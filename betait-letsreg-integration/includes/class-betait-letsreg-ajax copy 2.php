<?php

/**
 * Class Betait_LetsReg_Ajax
 *
 * @package Betait_Letsreg
 */
class Betait_LetsReg_Ajax {

    /**
     * Constructor.
     */
    public function __construct() {
        // Log for å bekrefte at konstruktøren blir kalt
        $this->log_debug( 'Betait_LetsReg_Ajax class instantiated.' );

        // Hook AJAX handlers for logged-in users
        add_action( 'wp_ajax_betait_letsreg_fetch_events', array( $this, 'fetch_events_ajax_handler' ) );
        add_action( 'wp_ajax_betait_letsreg_add_event', array( $this, 'add_event_ajax_handler' ) );

        // Log at hooks er lagt til
        $this->log_debug( 'AJAX handlers hooked.' );
    }

    /**
     * Helper method for logging debug messages.
     *
     * @param string $message Meldingen som skal logges.
     */
    private function log_debug( $message ) {
        $debug_enabled = (bool) get_option( 'betait_letsreg_debug', false );
        if ( $debug_enabled ) {
            if ( function_exists( 'error_log' ) ) {
                error_log( '[Betait_LetsReg AJAX:] ' . $message );
            }
        }
    }

    /**
     * AJAX handler for fetching events.
     */
    public function fetch_events_ajax_handler() {
        $this->log_debug( 'Fetch Events AJAX handler initiated.' );
    
        // Sjekk nonce for sikkerhet
        if ( ! check_ajax_referer( 'betait_letsreg_nonce', 'nonce', false ) ) {
            $this->log_debug( 'Nonce verification failed.' );
            wp_send_json_error( array( 'message' => __( 'Ugyldig sikkerhetskode.', 'betait-letsreg' ) ) );
        }
        $this->log_debug( 'Nonce verification passed.' );
    
        // Sjekk brukerrettigheter
        if ( ! current_user_can( 'manage_options' ) ) {
            $this->log_debug( 'User lacks manage_options capability.' );
            wp_send_json_error( array( 'message' => __( 'Du har ikke tilgang til denne handlingen.', 'betait-letsreg' ) ) );
        }
        $this->log_debug( 'User has manage_options capability.' );
    
        // Hent organizerId fra options
        $organizer_id = get_option( 'betait_letsreg_primary_org', 0 );
        if ( ! $organizer_id ) {
            $this->log_debug( 'Organizer ID ikke satt.' );
            wp_send_json_error( array( 'message' => __( 'Organizer ID ikke satt.', 'betait-letsreg' ) ) );
        }
        $this->log_debug( 'Organizer ID: ' . $organizer_id );
    
        // Hent offset og limit fra AJAX request
        $current_page = isset( $_POST['page'] ) ? intval( $_POST['page'] ) : 1;
        $limit        = 10; // Antall resultater per side
        $offset       = ($current_page - 1) * $limit;
    
        $this->log_debug( 'Fetching page ' . $current_page . ' with limit ' . $limit . ' and offset ' . $offset . '.' );
    
        // Bygg endpoint-URL med offset og limit
        $base_url     = get_option( 'betait_letsreg_base_url', 'https://integrate.deltager.no' );
        $access_token = get_option( 'betait_letsreg_access_token', '' );
        $endpoint_url = trailingslashit( $base_url ) . 'organizers/' . $organizer_id . '/events';
        $endpoint_url = add_query_arg( array(
           /'IncludeFields' => 'true',
            'ActiveOnly' => 'false',
            'SearchableOnly'=> 'false',
            'IncludeMunicipalities'=> 'true',
            'IncludeAreas'=> 'true',
            'offset' => $offset,
            'limit'  => $limit,
        ), $endpoint_url );
    
        $this->log_debug( 'API Endpoint URL: ' . $endpoint_url );
    
        // Gjør API-forespørsel
        $response = wp_remote_get( $endpoint_url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
                'Accept'        => 'application/json',
            ),
        ) );
    
        if ( is_wp_error( $response ) ) {
            $error_message = $response->get_error_message();
            $this->log_debug( 'wp_remote_get error: ' . $error_message );
            wp_send_json_error( array( 'message' => $error_message ) );
        }
        $this->log_debug( 'API request successful.' );
    
        $status_code = wp_remote_retrieve_response_code( $response );
        $this->log_debug( 'API response status code: ' . $status_code );
    
        if ( 200 !== $status_code ) {
            $this->log_debug( 'API-forespørsel feilet med statuskode ' . $status_code . '.' );
            wp_send_json_error( array( 'message' => sprintf( __( 'API-forespørsel feilet med statuskode %d.', 'betait-letsreg' ), $status_code ) ) );
        }
    
        $body = wp_remote_retrieve_body( $response );
        $this->log_debug( 'API response body: ' . $body );
    
        $data = json_decode( $body, true );
    
        if ( json_last_error() !== JSON_ERROR_NONE ) {
            $this->log_debug( 'JSON parse error: ' . json_last_error_msg() );
            wp_send_json_error( array( 'message' => __( 'Kunne ikke parse JSON-responsen.', 'betait-letsreg' ) ) );
        }
        $this->log_debug( 'JSON parsed successfully.' );
    
        // Log hele JSON-strømmen for feilsøking
        $this->log_debug( 'Decoded JSON data: ' . print_r( $data, true ) );
    
        // Sjekk om $data er en array
        if ( ! is_array( $data ) ) {
            $this->log_debug( 'API-responsen er ikke en array.' );
            wp_send_json_error( array( 'message' => __( 'Uventet API-responsformat.', 'betait-letsreg' ) ) );
        }
    
        // Filtrer aktive arrangementer
        $current_time = current_time( 'timestamp', true ); // UTC time
        $active_events = array_filter( $data, function( $event ) use ( $current_time ) {
            // Sjekk om arrangementet er aktivt
            if ( ! isset( $event['active'] ) || ! $event['active'] ) {
                $this->log_debug( 'Event ID ' . $event['id'] . ' er ikke aktivt.' );
                return false;
            }
    
            // Sjekk om startDate er etter nåværende tid
            if ( ! isset( $event['startDate'] ) ) {
                $this->log_debug( 'Event ID ' . $event['id'] . ' har ingen startDate.' );
                return false;
            }
            try {
                $start_date = new DateTime( $event['startDate'] );
                if ( $start_date->getTimestamp() <= $current_time ) {
                    $this->log_debug( 'Event ID ' . $event['id'] . ' startDate (' . $event['startDate'] . ') er ikke etter current_time.' );
                    return false;
                }
            } catch ( Exception $e ) {
                $this->log_debug( 'Event ID ' . $event['id'] . ' har ugyldig startDate: ' . $event['startDate'] );
                return false;
            }
    
            // Sjekk om registrering er åpen
            if ( isset( $event['registrationStartDate'] ) ) {
                try {
                    $registration_start = new DateTime( $event['registrationStartDate'] );
                    if ( $registration_start->getTimestamp() > $current_time ) {
                        $this->log_debug( 'Event ID ' . $event['id'] . ' registrationStartDate (' . $event['registrationStartDate'] . ') er i fremtiden.' );
                        return false; // Registreringen har ikke startet ennå
                    }
                } catch ( Exception $e ) {
                    $this->log_debug( 'Event ID ' . $event['id'] . ' har ugyldig registrationStartDate: ' . $event['registrationStartDate'] );
                    return false;
                }
            } else {
                $this->log_debug( 'Event ID ' . $event['id'] . ' har ingen registrationStartDate.' );
                return false; // Ingen registreringsstartdato angitt
            }
    
            if ( isset( $event['registrationEndDate'] ) && ! empty( $event['registrationEndDate'] ) ) {
                try {
                    $registration_end = new DateTime( $event['registrationEndDate'] );
                    if ( $registration_end->getTimestamp() < $current_time ) {
                        $this->log_debug( 'Event ID ' . $event['id'] . ' registrationEndDate (' . $event['registrationEndDate'] . ') er før current_time.' );
                        return false; // Registreringen er stengt
                    }
                } catch ( Exception $e ) {
                    $this->log_debug( 'Event ID ' . $event['id'] . ' har ugyldig registrationEndDate: ' . $event['registrationEndDate'] );
                    return false;
                }
            }
    
            $this->log_debug( 'Event ID ' . $event['id'] . ' passerte alle filtreringer.' );
            return true;
        });
        $this->log_debug( 'Filtered ' . count( $active_events ) . ' active events.' );
    
        // Sorter arrangementer etter start_time
        usort( $active_events, function( $a, $b ) {
            return strtotime( $a['startDate'] ) - strtotime( $b['startDate'] );
        });
        $this->log_debug( 'Sorted active events by start_time.' );
    
        // Begrens til 10 per side (selv om API allerede håndterer dette)
        $active_events = array_slice( $active_events, 0, 10 );
        $this->log_debug( 'Sliced active events to ' . count( $active_events ) . ' events.' );
    
        // Send data tilbake til front-end
        wp_send_json_success( array(
            'events' => $active_events,
            // 'pagination' => array(), // API-et returnerer ikke pagination, så dette kan fjernes eller tilpasses
        ) );
    }
    

    /**
     * AJAX handler for adding an event to WordPress.
     */
    public function add_event_ajax_handler() {
        $this->log_debug( 'Add Event AJAX handler initiated.' );

        // Sjekk nonce for sikkerhet
        if ( ! check_ajax_referer( 'betait_letsreg_nonce', 'nonce', false ) ) {
            $this->log_debug( 'Nonce verification failed.' );
            wp_send_json_error( array( 'message' => __( 'Ugyldig sikkerhetskode.', 'betait-letsreg' ) ) );
        }
        $this->log_debug( 'Nonce verification passed.' );

        // Sjekk brukerrettigheter
        if ( ! current_user_can( 'manage_options' ) ) {
            $this->log_debug( 'User lacks manage_options capability.' );
            wp_send_json_error( array( 'message' => __( 'Du har ikke tilgang til denne handlingen.', 'betait-letsreg' ) ) );
        }
        $this->log_debug( 'User has manage_options capability.' );

        // Hent event ID fra AJAX request
        $event_id = isset( $_POST['event_id'] ) ? intval( $_POST['event_id'] ) : 0;
        if ( ! $event_id ) {
            $this->log_debug( 'Ugyldig arrangement ID: ' . $event_id );
            wp_send_json_error( array( 'message' => __( 'Ugyldig arrangement ID.', 'betait-letsreg' ) ) );
        }
        $this->log_debug( 'Received event ID: ' . $event_id );

        // Hent organizerId fra options
        $organizer_id = get_option( 'betait_letsreg_primary_org', 0 );
        if ( ! $organizer_id ) {
            $this->log_debug( 'Organizer ID ikke satt.' );
            wp_send_json_error( array( 'message' => __( 'Organizer ID ikke satt.', 'betait-letsreg' ) ) );
        }
        $this->log_debug( 'Organizer ID: ' . $organizer_id );

        // Bygg API-endpoint for spesifikt arrangement
        $base_url     = get_option( 'betait_letsreg_base_url', 'https://integrate.deltager.no' );
        $access_token = get_option( 'betait_letsreg_access_token', '' );
        $endpoint_url = trailingslashit( $base_url ) . 'organizers/' . $organizer_id . '/events/' . $event_id;
        $this->log_debug( 'API Endpoint URL for specific event: ' . $endpoint_url );

        // Gjør API-forespørsel for å hente arrangementdetaljer
        $response = wp_remote_get( $endpoint_url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
                'Accept'        => 'application/json',
            ),
        ) );

        if ( is_wp_error( $response ) ) {
            $error_message = $response->get_error_message();
            $this->log_debug( 'wp_remote_get error: ' . $error_message );
            wp_send_json_error( array( 'message' => $error_message ) );
        }
        $this->log_debug( 'API request successful.' );

        $status_code = wp_remote_retrieve_response_code( $response );
        $this->log_debug( 'API response status code: ' . $status_code );

        if ( 200 !== $status_code ) {
            $this->log_debug( 'API-forespørsel feilet med statuskode ' . $status_code . '.' );
            wp_send_json_error( array( 'message' => sprintf( __( 'API-forespørsel feilet med statuskode %d.', 'betait-letsreg' ), $status_code ) ) );
        }

        $body = wp_remote_retrieve_body( $response );
        $this->log_debug( 'API response body: ' . $body );

        $data = json_decode( $body, true );

        if ( json_last_error() !== JSON_ERROR_NONE ) {
            $this->log_debug( 'JSON parse error: ' . json_last_error_msg() );
            wp_send_json_error( array( 'message' => __( 'Kunne ikke parse JSON-responsen.', 'betait-letsreg' ) ) );
        }
        $this->log_debug( 'JSON parsed successfully.' );
        $this->log_debug( 'Decoded JSON data: ' . print_r( $data, true ) );

        $event = $data ?? null; // Siden API-et returnerer en enkelt event som array
        if ( ! $event ) {
            $this->log_debug( 'Arrangement ikke funnet for ID: ' . $event_id );
            wp_send_json_error( array( 'message' => __( 'Arrangement ikke funnet.', 'betait-letsreg' ) ) );
        }
        $this->log_debug( 'Event data retrieved: ' . print_r( $event, true ) );

        // Sjekk om arrangementet allerede er lagret (unik identifikator, f.eks. ekstern ID)
        $existing_event = get_posts( array(
            'post_type'   => 'event',
            'meta_key'    => 'external_event_id',
            'meta_value'  => $event_id,
            'numberposts' => 1,
        ) );

        if ( ! empty( $existing_event ) ) {
            $this->log_debug( 'Arrangementet er allerede lagret i WordPress for ID: ' . $event_id );
            wp_send_json_error( array( 'message' => __( 'Arrangementet er allerede lagret i WordPress.', 'betait-letsreg' ) ) );
        }
        $this->log_debug( 'Arrangementet er ikke lagret. Fortsetter med opprettelse.' );

        // Opprett et nytt arrangement som Custom Post Type 'event'
        $post_id = wp_insert_post( array(
            'post_title'   => sanitize_text_field( $event['name'] ),
            'post_content' => sanitize_textarea_field( $event['description'] ?? '' ),
            'post_status'  => 'publish',
            'post_type'    => 'event', // Sørg for at denne CPT er registrert
        ) );

        if ( is_wp_error( $post_id ) ) {
            $this->log_debug( 'wp_insert_post error: ' . $post_id->get_error_message() );
            wp_send_json_error( array( 'message' => $post_id->get_error_message() ) );
        }
        $this->log_debug( 'Post created with ID: ' . $post_id );

        // Legg til metadata
        update_post_meta( $post_id, 'external_event_id', $event_id );
        update_post_meta( $post_id, 'venue', sanitize_text_field( $event['location']['name'] ?? '' ) );
        update_post_meta( $post_id, 'registered', intval( $event['registeredParticipants'] ) );
        update_post_meta( $post_id, 'max_attendees', intval( $event['maxAllowedRegistrations'] ) );
        update_post_meta( $post_id, 'waitlist', intval( $event['hasWaitinglist'] ) );
        update_post_meta( $post_id, 'start_time', sanitize_text_field( $event['startDate'] ) );
        update_post_meta( $post_id, 'end_time', sanitize_text_field( $event['endDate'] ) );
        update_post_meta( $post_id, 'registration_deadline', sanitize_text_field( $event['registrationStartDate'] ) );
        update_post_meta( $post_id, 'external_url', esc_url_raw( $event['eventUrl'] ) );

        $this->log_debug( 'Metadata added to post ID: ' . $post_id );

        wp_send_json_success( array( 'message' => __( 'Arrangementet ble lagt til i WordPress.', 'betait-letsreg' ) ) );
    }
}
