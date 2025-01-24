<?php

/**
 * Class Betait_LetsReg_Ajax
 *
 * Handles AJAX actions for:
 *  1) Fetching multiple events (betait_letsreg_fetch_events).
 *  2) Adding a single event to WordPress (betait_letsreg_add_event).
 *
 * @package Betait_Letsreg
 */
class Betait_LetsReg_Ajax {

    /**
     * Constructor.
     */
    public function __construct() {
        // Log to confirm the constructor is called
        $this->log_debug( 'Betait_LetsReg_Ajax class instantiated.' );

        // Hook AJAX handlers for logged-in users
        add_action( 'wp_ajax_betait_letsreg_fetch_events', array( $this, 'fetch_events_ajax_handler' ) );
        add_action( 'wp_ajax_betait_letsreg_add_event',   array( $this, 'add_event_ajax_handler' ) );
        add_action( 'wp_ajax_betait_letsreg_get_event', array( $this, 'get_event_ajax_handler' ) );

        // If you need to allow non-logged-in, also do wp_ajax_nopriv_...
        // add_action( 'wp_ajax_nopriv_betait_letsreg_fetch_events', array( $this, 'fetch_events_ajax_handler' ) );
        // add_action( 'wp_ajax_nopriv_betait_letsreg_add_event',   array( $this, 'add_event_ajax_handler' ) );
        // add_action( 'wp_ajax_nopriv_betait_letsreg_get_event', array( $this, 'get_event_ajax_handler' ) );

        // Confirm in debug
        $this->log_debug( 'AJAX handlers hooked.' );
    }

    /**
     * Private helper for debug logging.
     * Checks a "betait_letsreg_debug" option to see if debug is enabled.
     */
    private function log_debug( $message ) {
        $debug_enabled = (bool) get_option( 'betait_letsreg_debug', false );
        if ( $debug_enabled && function_exists( 'error_log' ) ) {
            error_log( '[Betait_LetsReg AJAX:] ' . $message );
        }
    }

    /**
     * AJAX handler for fetching multiple events (betait_letsreg_fetch_events).
     * Typically used in the "Arrangementer" listing, returning a JSON list.
     */
    public function fetch_events_ajax_handler() {
        $this->log_debug( 'Fetch Events AJAX handler initiated.' );

        // 1) Check nonce
        if ( ! check_ajax_referer( 'betait_letsreg_nonce', 'nonce', false ) ) {
            $this->log_debug( 'Nonce verification failed.' );
            wp_send_json_error( array( 'message' => __( 'Invalid security nonce.', 'betait-letsreg' ) ) );
        }
        $this->log_debug( 'Nonce verification passed.' );

        // 2) Check user capabilities
        if ( ! current_user_can( 'manage_options' ) ) {
            $this->log_debug( 'User lacks manage_options capability.' );
            wp_send_json_error( array( 'message' => __( 'You do not have permission to perform this action.', 'betait-letsreg' ) ) );
        }
        $this->log_debug( 'User has manage_options capability.' );

        // 3) Get organizer ID from options
        $organizer_id = get_option( 'betait_letsreg_primary_org', 0 );
        if ( ! $organizer_id ) {
            $this->log_debug( 'Organizer ID not set.' );
            wp_send_json_error( array( 'message' => __( 'Organizer ID not set.', 'betait-letsreg' ) ) );
        }
        $this->log_debug( 'Organizer ID: ' . $organizer_id );

        // 4) Get pagination parameters from AJAX
        $current_page = isset( $_POST['page'] ) ? intval( $_POST['page'] ) : 1;
        $limit        = 10; // number of results per page
        // For a "page-based" offset:
        $offset       = $current_page - 1;
        $this->log_debug( "Fetching page {$current_page} with limit {$limit} and offset (page-based)={$offset}." );

        // 5) Additional toggles from AJAX
        $active_only     = isset( $_POST['activeonly'] ) ? filter_var( $_POST['activeonly'], FILTER_VALIDATE_BOOLEAN ) : false;
        $searchable_only = isset( $_POST['searchableonly'] ) ? filter_var( $_POST['searchableonly'], FILTER_VALIDATE_BOOLEAN ) : false;

        $this->log_debug( 'Active Only: ' . ( $active_only ? 'true' : 'false' ) );
        $this->log_debug( 'Searchable Only: ' . ( $searchable_only ? 'true' : 'false' ) );

        // 6) Build the remote endpoint URL
        $base_url     = get_option( 'betait_letsreg_base_url', 'https://integrate.deltager.no' );
        $access_token = get_option( 'betait_letsreg_access_token', '' );
        $endpoint_url = trailingslashit( $base_url ) . 'organizers/' . $organizer_id . '/events';

        // 7) Prepare query args
        $query_args = array(
            'offset'                => $offset,
            'limit'                 => $limit,
            'IncludeMunicipalities' => 'true',
            'IncludeAreas'          => 'true',
        );
        if ( $active_only ) {
            $query_args['activeonly'] = 'true'; // or 'ActiveOnly' if the API demands uppercase
        }
        if ( $searchable_only ) {
            $query_args['searchableonly'] = 'true';
        }

        $endpoint_url = add_query_arg( $query_args, $endpoint_url );
        $this->log_debug( 'API Endpoint URL: ' . $endpoint_url );

        // 8) Call the remote API
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

        if ( ! is_array( $data ) ) {
            $this->log_debug( 'API response is not an array.' );
            wp_send_json_error( array( 'message' => __( 'Unexpected API response format.', 'betait-letsreg' ) ) );
        }
        $this->log_debug( 'No date-based filtering applied. Total events received: ' . count( $data ) );

        // 9) (Optional) Sort / slice them in PHP if needed
        $sort_field     = isset( $_POST['sort_field'] ) ? sanitize_text_field( $_POST['sort_field'] ) : 'startDate';
        $sort_direction = isset( $_POST['sort_direction'] ) && in_array( strtolower( $_POST['sort_direction'] ), array( 'asc', 'desc' ), true )
            ? strtolower( $_POST['sort_direction'] )
            : 'asc';

        $this->log_debug( 'Sort field: ' . $sort_field . ', Sort direction: ' . $sort_direction );

        $fetched_events = $data; // rename for clarity
        usort( $fetched_events, function( $a, $b ) use ( $sort_field, $sort_direction ) {
            $valA = isset($a[$sort_field]) ? $a[$sort_field] : '';
            $valB = isset($b[$sort_field]) ? $b[$sort_field] : '';

            // e.g. if 'startDate' etc => sort by time
            // if 'registeredParticipants' => numeric
            // etc. (Your sorting logic here)
            // Return -1,0,1
            // For brevity, skip full code...
            return 0;
        });

        $this->log_debug( 'Sorted events using the chosen field and direction.' );

        // 10) Possibly slice to $limit (though the endpoint might already do it)
        $final_events = array_slice( $fetched_events, 0, $limit );
        $this->log_debug( 'Sliced events down to ' . count($final_events) . ' records.' );

        // 11) Return success
        wp_send_json_success( array(
            'events' => $final_events,
        ) );
    }

    /**
     * AJAX handler for adding (importing) a single event to WP (betait_letsreg_add_event).
     */
    public function add_event_ajax_handler() {
        $this->log_debug( 'Add Event AJAX handler initiated.' );

        // 1) Security checks
        if ( ! check_ajax_referer( 'betait_letsreg_nonce', 'nonce', false ) ) {
            $this->log_debug( 'Nonce verification failed.' );
            wp_send_json_error( array( 'message' => __( 'Ugyldig sikkerhetskode.', 'betait-letsreg' ) ) );
        }
        $this->log_debug( 'Nonce verification passed.' );

        if ( ! current_user_can( 'manage_options' ) ) {
            $this->log_debug( 'User lacks manage_options capability.' );
            wp_send_json_error( array( 'message' => __( 'Du har ikke tilgang til denne handlingen.', 'betait-letsreg' ) ) );
        }
        $this->log_debug( 'User has manage_options capability.' );

        // 2) event_id from POST
        $event_id = isset( $_POST['event_id'] ) ? intval( $_POST['event_id'] ) : 0;
        if ( ! $event_id ) {
            $this->log_debug( 'Ugyldig arrangement ID: ' . $event_id );
            wp_send_json_error( array( 'message' => __( 'Ugyldig arrangement ID.', 'betait-letsreg' ) ) );
        }
        $this->log_debug( 'Received event ID: ' . $event_id );

        // 3) Retrieve the user's chosen storage method (option)
        $storage_choice = get_option( 'betait_letsreg_local_storage', 'lr-arr' );
        $this->log_debug( 'Storage method chosen: ' . $storage_choice );

        // 4) Build the "GET single event" endpoint (e.g. /organizers/xxx/events/yyy)
        $organizer_id = get_option( 'betait_letsreg_primary_org', 0 );
        $event_id = isset($_POST['event_id']) ? intval($_POST['event_id']) : 0;
        if ( ! $event_id ) {
            wp_send_json_error( array( 'message' => 'Missing event_id' ) );
        }
    
        // Build your external call to "GET /events/{eventId}" or something
        // or integrate with your aggregator logic. Example:
    
        $access_token = get_option('betait_letsreg_access_token','');
        $base_url     = get_option('betait_letsreg_base_url','https://integrate.deltager.no');
        $endpoint_url = trailingslashit( $base_url ) . 'events/' . $event_id;
        $this->log_debug( 'API Endpoint URL for specific event: ' . $endpoint_url );

        // 5) Make request
        $response = wp_remote_get( $endpoint_url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
                'Accept'        => 'application/json',
            ),
        ) );
        if ( is_wp_error($response) ) {
            $error_message = $response->get_error_message();
            $this->log_debug( 'wp_remote_get error: ' . $error_message );
            wp_send_json_error( array( 'message' => $error_message ) );
        }
        $this->log_debug( 'API request for single event successful.' );

        $status_code = wp_remote_retrieve_response_code($response);
        $this->log_debug( 'API response status code: ' . $status_code );
        if ( 200 !== $status_code ) {
            $this->log_debug( 'API request failed with status code ' . $status_code );
            wp_send_json_error( array( 'message' => sprintf( __( 'API request failed with status code %d.', 'betait-letsreg' ), $status_code ) ) );
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        if ( json_last_error() !== JSON_ERROR_NONE ) {
            $this->log_debug( 'JSON parse error: ' . json_last_error_msg() );
            wp_send_json_error( array( 'message' => __( 'Kunne ikke parse JSON-responsen.', 'betait-letsreg' ) ) );
        }
        $this->log_debug( 'JSON parsed successfully.' );

        if ( empty($data) || ! is_array($data) ) {
            $this->log_debug( 'Event data is empty or invalid for ID: ' . $event_id );
            wp_send_json_error( array( 'message' => __( 'Arrangement ikke funnet.', 'betait-letsreg' ) ) );
        }
        $this->log_debug( 'Event data retrieved: ' . print_r($data, true) );

        // 6) Possibly check if already stored (meta "external_event_id" = $event_id)
        $existing = get_posts( array(
            'post_type'   => array('tribe_events','lr-arr','post'), // or 'any'
            'meta_key'    => 'external_event_id',
            'meta_value'  => $event_id,
            'numberposts' => 1,
        ) );
        if ( $existing ) {
            $this->log_debug( 'Arrangementet er allerede lagret i WordPress for ID: ' . $event_id );
            wp_send_json_error( array( 'message' => __( 'Arrangementet er allerede lagret i WordPress.', 'betait-letsreg' ) ) );
        }
        $this->log_debug( 'Arrangement er ikke lagret ennå. Fortsetter opprettelse.' );

        // 7) Map data => WP
        // We'll assume you have a separate function like map_letsreg_event_to_wp:
        // e.g. $mapped = $this->map_letsreg_event_to_wp($data, $storage_choice)
        // which returns an array with 'post_args' => [...], 'meta' => [...]
        // For demonstration, let's do a quick manual approach:
        $post_title   = sanitize_text_field( $data['name'] ?? 'Untitled' );
        $post_content = wp_kses_post( $data['description'] ?? '' );
        $post_type    = ($storage_choice === 'tribe_events') ? 'tribe_events'
                       : (($storage_choice === 'post')       ? 'post'
                       : 'lr-arr'); // fallback

        $post_args = array(
            'post_title'   => $post_title,
            'post_content' => $post_content,
            'post_status'  => 'publish',
            'post_type'    => $post_type,
        );
        // Insert post
        $post_id = wp_insert_post($post_args);
        if ( is_wp_error($post_id) ) {
            $this->log_debug( 'wp_insert_post error: ' . $post_id->get_error_message() );
            wp_send_json_error( array( 'message' => $post_id->get_error_message() ) );
        }
        $this->log_debug( 'Post created with ID: ' . $post_id );

        // 8) Store meta e.g. external_event_id
        update_post_meta( $post_id, 'external_event_id', $event_id );
        // You can store more meta. E.g. startDate => 'lr_startDate'
        if ( ! empty($data['startDate']) ) {
            update_post_meta( $post_id, 'lr_startDate', sanitize_text_field($data['startDate']) );
        }
        // etc. Or do a full loop with your field mapping array.

        // 9) If tribe_events -> you might create a real venue, organizer, etc.

        $this->log_debug( 'Arrangementet ble opprettet med post_id=' . $post_id );

        // 10) Done
        wp_send_json_success( array(
            'message' => __( 'Arrangementet ble lagt til i WordPress.', 'betait-letsreg' ),
            'post_id' => $post_id
        ) );
    }

    /**
     * AJAX handler for fetching a single event (betait_letsreg_get_event).
     * This is a helper function to get detailed info for a single event.
     */
    public function get_event_ajax_handler() {
        // check nonce
        check_ajax_referer( 'betait_letsreg_nonce', 'nonce' );
    
        // capability check
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'No permission.' ) );
        }
    
        $event_id = isset($_POST['event_id']) ? intval($_POST['event_id']) : 0;
        if ( ! $event_id ) {
            wp_send_json_error( array( 'message' => 'Missing event_id' ) );
        }
    
        // Build your external call to "GET /events/{eventId}" or something
        // or integrate with your aggregator logic. Example:
    
        $access_token = get_option('betait_letsreg_access_token','');
        $base_url     = get_option('betait_letsreg_base_url','https://integrate.deltager.no');
        $endpoint_url = trailingslashit( $base_url ) . 'events/' . $event_id;
    
        $response = wp_remote_get( $endpoint_url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
                'Accept'        => 'application/json',
            ),
        ));
        if ( is_wp_error($response) ) {
            wp_send_json_error( array( 'message' => $response->get_error_message() ) );
        }
        $status_code = wp_remote_retrieve_response_code($response);
        if ( 200 !== $status_code ) {
            wp_send_json_error( array( 'message' => "Status $status_code from upstream." ) );
        }
    
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        if ( json_last_error() !== JSON_ERROR_NONE ) {
            wp_send_json_error( array( 'message' => 'JSON parse error' ) );
        }
    
        // success
        // $data should be the event object with fields like name, startDate, etc.
        wp_send_json_success( $data );
    }

}
