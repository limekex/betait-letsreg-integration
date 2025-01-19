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
    
        // Check nonce for security
        if ( ! check_ajax_referer( 'betait_letsreg_nonce', 'nonce', false ) ) {
            $this->log_debug( 'Nonce verification failed.' );
            wp_send_json_error( array( 'message' => __( 'Invalid security nonce.', 'betait-letsreg' ) ) );
        }
        $this->log_debug( 'Nonce verification passed.' );
    
        // Check user capabilities
        if ( ! current_user_can( 'manage_options' ) ) {
            $this->log_debug( 'User lacks manage_options capability.' );
            wp_send_json_error( array( 'message' => __( 'You do not have permission to perform this action.', 'betait-letsreg' ) ) );
        }
        $this->log_debug( 'User has manage_options capability.' );
    
        // Get organizer ID from options
        $organizer_id = get_option( 'betait_letsreg_primary_org', 0 );
        if ( ! $organizer_id ) {
            $this->log_debug( 'Organizer ID not set.' );
            wp_send_json_error( array( 'message' => __( 'Organizer ID not set.', 'betait-letsreg' ) ) );
        }
        $this->log_debug( 'Organizer ID: ' . $organizer_id );
    
        // Get pagination parameters from AJAX request
        $current_page = isset( $_POST['page'] ) ? intval( $_POST['page'] ) : 1;
        $limit        = 10; // Number of results per page
        $offset       = ( $current_page - 1 ) * $limit;
    
        $this->log_debug( 'Fetching page ' . $current_page . ' with limit ' . $limit . ' and offset ' . $offset . '.' );
    
        // Get optional filter parameters (toggles) from AJAX request
        $active_only     = isset( $_POST['activeonly'] ) ? filter_var( $_POST['activeonly'], FILTER_VALIDATE_BOOLEAN ) : false;
        $searchable_only = isset( $_POST['searchableonly'] ) ? filter_var( $_POST['searchableonly'], FILTER_VALIDATE_BOOLEAN ) : false;
    
        $this->log_debug( 'Active Only: ' . ( $active_only ? 'true' : 'false' ) );
        $this->log_debug( 'Searchable Only: ' . ( $searchable_only ? 'true' : 'false' ) );
    
        // Build the endpoint URL with offset, limit, and optional filters
        $base_url     = get_option( 'betait_letsreg_base_url', 'https://integrate.deltager.no' );
        $access_token = get_option( 'betait_letsreg_access_token', '' );
        $endpoint_url = trailingslashit( $base_url ) . 'organizers/' . $organizer_id . '/events';
    
        // Prepare query arguments
        $query_args = array(
            'offset'                => $offset,
            'limit'                 => $limit,
            'IncludeMunicipalities' => 'true',
            'IncludeAreas'          => 'true',
        );
    
        // Conditionally add 'activeonly' and 'searchableonly' if set to true
        if ( $active_only ) {
            // Must match the API’s expected parameter name & casing
            $query_args['activeonly'] = 'true';
        }
        if ( $searchable_only ) {
            $query_args['searchableonly'] = 'true';
        }
    
        // Build the full endpoint URL with query parameters
        $endpoint_url = add_query_arg( $query_args, $endpoint_url );
        $this->log_debug( 'API Endpoint URL: ' . $endpoint_url );
    
        // Make the API request
        $response = wp_remote_get( $endpoint_url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
                'Accept'        => 'application/json',
            ),
        ) );
    
        $curl_command = sprintf(
            "curl -X GET \"%s\" \\\n"
            . "     -H \"Authorization: Bearer %s\" \\\n"
            . "     -H \"Accept: application/json\"",
            $endpoint_url,
            $access_token
        );
        
        // Comment out this part if you don't want to log the cURL command ---
        // If you want to also show $organizer_id, you could add it to the command or to the log:
        $curl_command .= sprintf(" \\\n     # Organizer ID: %s", $organizer_id);
        
        // Now log the entire cURL command to debug.log
        // If you're using a custom debug method:
        $this->log_debug('Constructed cURL command for debug:' . "\n" . $curl_command);
        
        // Or if you prefer direct error_log:
        error_log("[Betait_Letsreg_Debug_Curl] " . $curl_command);


        

        if ( is_wp_error( $response ) ) {
            $error_message = $response->get_error_message();
            $this->log_debug( 'wp_remote_get error: ' . $error_message );
            wp_send_json_error( array( 'message' => $error_message ) );
        }
        $this->log_debug( 'API request successful.' );
    
        $status_code = wp_remote_retrieve_response_code( $response );
        $this->log_debug( 'API response status code: ' . $status_code );
    
        if ( 200 !== $status_code ) {
            $this->log_debug( 'API request failed with status code ' . $status_code . '.' );
            wp_send_json_error( array( 'message' => sprintf( __( 'API request failed with status code %d.', 'betait-letsreg' ), $status_code ) ) );
        }
    
        $body = wp_remote_retrieve_body( $response );
        $this->log_debug( 'API response body: ' . $body );
    
        $data = json_decode( $body, true );
        if ( json_last_error() !== JSON_ERROR_NONE ) {
            $this->log_debug( 'JSON parse error: ' . json_last_error_msg() );
            wp_send_json_error( array( 'message' => __( 'Could not parse JSON response.', 'betait-letsreg' ) ) );
        }
        $this->log_debug( 'JSON parsed successfully.' );
    
        // Log entire JSON data for debugging
        $this->log_debug( 'Decoded JSON data: ' . print_r( $data, true ) );
    
        // Ensure the response is an array
        if ( ! is_array( $data ) ) {
            $this->log_debug( 'API response is not an array.' );
            wp_send_json_error( array( 'message' => __( 'Unexpected API response format.', 'betait-letsreg' ) ) );
        }
    
        // Include all events (no date-based filtering)
        $fetched_events = $data;
        $this->log_debug( 'No date-based filtering applied. Total events received: ' . count( $fetched_events ) );
    
        // Sort events if requested
        $sort_field = isset( $_POST['sort_field'] ) ? sanitize_text_field( $_POST['sort_field'] ) : 'startDate';
        $sort_direction = isset( $_POST['sort_direction'] ) && in_array( strtolower( $_POST['sort_direction'] ), array( 'asc', 'desc' ) )
            ? strtolower( $_POST['sort_direction'] )
            : 'asc';
    
        $this->log_debug( 'Sort field: ' . $sort_field . ', Sort direction: ' . $sort_direction );
    
        usort( $fetched_events, function( $a, $b ) use ( $sort_field, $sort_direction ) {
            $valueA = isset( $a[ $sort_field ] ) ? $a[ $sort_field ] : '';
            $valueB = isset( $b[ $sort_field ] ) ? $b[ $sort_field ] : '';
    
            // Handle different data types
            if ( in_array( $sort_field, array( 'startDate', 'endDate', 'registrationStartDate' ) ) ) {
                $timeA = strtotime( $valueA );
                $timeB = strtotime( $valueB );
                if ( $timeA == $timeB ) return 0;
                return ( $sort_direction === 'asc' ) ? ( $timeA < $timeB ? -1 : 1 ) : ( $timeA > $timeB ? -1 : 1 );
            }
    
            if ( in_array( $sort_field, array( 'registeredParticipants', 'maxAllowedRegistrations' ) ) ) {
                $numA = intval( $valueA );
                $numB = intval( $valueB );
                if ( $numA == $numB ) return 0;
                return ( $sort_direction === 'asc' ) ? ( $numA < $numB ? -1 : 1 ) : ( $numA > $numB ? -1 : 1 );
            }
    
            if ( $sort_field === 'hasWaitinglist' ) {
                $valA = strtolower( $valueA ) === 'ja' ? 1 : 0;
                $valB = strtolower( $valueB ) === 'ja' ? 1 : 0;
                if ( $valA == $valB ) return 0;
                return ( $sort_direction === 'asc' ) ? ( $valA < $valB ? -1 : 1 ) : ( $valA > $valB ? -1 : 1 );
            }
    
            // For string fields
            $valA = strtolower( $valueA );
            $valB = strtolower( $valueB );
            if ( $valA == $valB ) return 0;
            return ( $sort_direction === 'asc' ) ? ( $valA < $valB ? -1 : 1 ) : ( $valA > $valB ? -1 : 1 );
        });
    
        $this->log_debug( 'Sorted events using the chosen field and direction.' );
    
        // Limit to the specified limit (even if the API already does so)
        $final_events = array_slice( $fetched_events, 0, $limit );
        $this->log_debug( 'Sliced events down to ' . count( $final_events ) . ' records.' );
    
        // Send data back to the front-end
        wp_send_json_success( array(
            'events' => $final_events,
            // 'pagination' => array(), // API doesn't return pagination; adjust if needed
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
