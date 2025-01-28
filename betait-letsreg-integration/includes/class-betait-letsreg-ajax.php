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

    private function import_featured_image($image_url, $post_id) {
        // Log the image URL
        $this->log_debug('Attempting to import image from URL: ' . $image_url);
    
        // Temporary filename for download
        $temp_file = download_url($image_url);
    
        if (is_wp_error($temp_file)) {
            $this->log_debug('Error downloading image: ' . $temp_file->get_error_message());
            return $temp_file;
        }
    
        // Force the file extension and MIME type for the image
        $file_name = basename($image_url);
        if (!preg_match('/\.(jpg|jpeg|png|gif|bmp|webp)$/i', $file_name)) {
            $this->log_debug('File extension missing or invalid; forcing ".jpg".');
            $file_name .= '.jpg';
        }
    
        // Set up file array
        $file = [
            'name'     => $file_name, // File name
            'type'     => 'image/jpeg', // Force MIME type (adjust as necessary)
            'tmp_name' => $temp_file, // Temporary file location
            'error'    => 0, // No errors
            'size'     => filesize($temp_file), // File size
        ];
    
        // Check file type explicitly
        $check_file = wp_check_filetype_and_ext($temp_file, $file_name);
        if (!$check_file['ext'] || !$check_file['type']) {
            $this->log_debug('Forced file type: image/jpeg');
            $check_file['ext'] = 'jpg';
            $check_file['type'] = 'image/jpeg';
        }
    
        // Upload the file to the media library
        $overrides = [
            'test_form'   => false, // Skip form file validation
            'test_upload' => true,  // Perform MIME type checks
        ];
    
        $attachment_id = media_handle_sideload($file, $post_id, null, $overrides);
    
        // Clean up temporary file
        @unlink($temp_file);
    
        if (is_wp_error($attachment_id)) {
            $this->log_debug('Error adding image to media library: ' . $attachment_id->get_error_message());
            return $attachment_id;
        }
    
        $this->log_debug('Image successfully imported with ID: ' . $attachment_id);
        return $attachment_id;
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

 /*    /**
     * AJAX handler for adding (importing) a single event to WP (betait_letsreg_add_event).
     */
    public function add_event_ajax_handler() {
        $this->log_debug('Add Event AJAX handler initiated.');
    
        // 1. Security checks
        if (!check_ajax_referer('betait_letsreg_nonce', 'nonce', false)) {
            $this->log_debug('Nonce verification failed.');
            wp_send_json_error(['message' => __('Ugyldig sikkerhetskode.', 'betait-letsreg')]);
        }
    
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Du har ikke tilgang til denne handlingen.', 'betait-letsreg')]);
        }
    
        // 2. Get the event_id from POST
        $event_id = isset($_POST['event_id']) ? intval($_POST['event_id']) : 0;
        if (!$event_id) {
            $this->log_debug('Invalid event ID provided.');
            wp_send_json_error(['message' => __('Ugyldig arrangement ID.', 'betait-letsreg')]);
        }
        $this->log_debug('Event ID: ' . $event_id);
    
        // 3. Retrieve storage choice and field mappings
        $storage_choice = get_option('betait_letsreg_local_storage', 'lr-arr');
        $this->log_debug('Storage choice: ' . $storage_choice);
        $field_mapping = include plugin_dir_path(__FILE__) . 'class-betait-letsreg-fieldmapping.php';
        $mapping = $field_mapping[$storage_choice] ?? [];
        $this->log_debug('Field mapping loaded.');
    
        // 4. Fetch event data from API
        $access_token = get_option('betait_letsreg_access_token', '');
        $base_url = get_option('betait_letsreg_base_url', 'https://integrate.deltager.no');
        $endpoint_url = trailingslashit($base_url) . 'events/' . $event_id;
        $this->log_debug('Fetching event data from: ' . $endpoint_url);
    
        $response = wp_remote_get($endpoint_url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $access_token,
                'Accept' => 'application/json',
            ],
        ]);
    
        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            $this->log_debug('API request error: ' . $error_message);
            wp_send_json_error(['message' => $error_message]);
        }
    
        $data = json_decode(wp_remote_retrieve_body($response), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->log_debug('JSON parse error: ' . json_last_error_msg());
            wp_send_json_error(['message' => __('Kunne ikke parse JSON-responsen.', 'betait-letsreg')]);
        }
        $this->log_debug('Event data retrieved: ' . print_r($data, true));
    
        // 5. Prevent duplicate imports
        $existing = get_posts([
            'post_type' => $storage_choice,
            'meta_key' => 'external_event_id',
            'meta_value' => $event_id,
            'numberposts' => 1,
        ]);
        if ($existing) {
            $this->log_debug('Event already exists in post type: ' . $storage_choice);
            wp_send_json_error(['message' => __('Arrangementet er allerede lagret.', 'betait-letsreg')]);
        }
    
        // 6. Map API data to WP fields
        $post_args = [
            'post_title' => sanitize_text_field($data[$mapping['post_title']] ?? 'Untitled'),
            'post_content' => wp_kses_post($data[$mapping['post_content']] ?? ''),
            'post_status' => $mapping['post_status'] ?? 'publish',
            'post_type' => $storage_choice,
        ];
        $this->log_debug('Post arguments: ' . print_r($post_args, true));
    
        $post_id = wp_insert_post($post_args);
        if (is_wp_error($post_id)) {
            $this->log_debug('Post creation error: ' . $post_id->get_error_message());
            wp_send_json_error(['message' => $post_id->get_error_message()]);
        }
        $this->log_debug('Post created with ID: ' . $post_id);
    
        // 7. Save meta fields with ISO date conversion
        foreach ($mapping['meta'] as $meta_key => $api_key) {
            if (!empty($data[$api_key])) {
                $value = $data[$api_key];
    
                // Convert ISO dates to proper format
                if (strpos($meta_key, '_EventStartDate') !== false || strpos($meta_key, '_EventEndDate') !== false) {
                    $value = date('Y-m-d H:i:s', strtotime($value));
                    $this->log_debug('Converted ISO date for ' . $meta_key . ': ' . $value);
                }
    
                update_post_meta($post_id, $meta_key, sanitize_text_field($value));
            }
        }
    
        // 8. Handle specific cases for The Events Calendar
        if ($storage_choice === 'tribe_events') {
            if (!empty($data['location'])) {
                $venue_args = [
                    'Venue' => sanitize_text_field($data['location']['name'] ?? ''),
                    'Address' => sanitize_text_field($data['location']['address1'] ?? ''),
                    'City' => sanitize_text_field($data['location']['city'] ?? ''),
                    'Country' => sanitize_text_field($data['location']['county'] ?? ''),
                ];
                $venue_id = Tribe__Events__API::createVenue($venue_args);
                if (!is_wp_error($venue_id)) {
                    update_post_meta($post_id, '_EventVenueID', $venue_id);
                    $this->log_debug('Venue created with ID: ' . $venue_id);
                }
            }
    
            if (!empty($data['organizer'])) {
                $organizer_args = [
                    'Organizer' => sanitize_text_field($data['organizer']['name'] ?? ''),
                    'Phone' => sanitize_text_field($data['organizer']['phone'] ?? ''),
                    'Email' => sanitize_email($data['organizer']['email'] ?? ''),
                ];
                $organizer_id = Tribe__Events__API::createOrganizer($organizer_args);
                if (!is_wp_error($organizer_id)) {
                    update_post_meta($post_id, '_EventOrganizerID', $organizer_id);
                    $this->log_debug('Organizer created with ID: ' . $organizer_id);
                }
            }
        }
    
        // 9. Set featured image
        if (!empty($data['imageUrl'])) {
            $image_id = $this->import_featured_image($data['imageUrl'], $post_id);
            if (!is_wp_error($image_id)) {
                set_post_thumbnail($post_id, $image_id);
                $this->log_debug('Featured image set with ID: ' . $image_id);
            } else {
                $this->log_debug('Error importing featured image: ' . $image_id->get_error_message());
            }
        }
    
        // 10. Flush rewrite rules for The Events Calendar
        if ($storage_choice === 'tribe_events') {
            flush_rewrite_rules();
            $this->log_debug('Rewrite rules flushed for tribe_events.');
        }
    
        // 11. Respond with success
        $this->log_debug('Event import completed successfully.');
        wp_send_json_success(['message' => __('Arrangementet ble lagt til.', 'betait-letsreg'), 'post_id' => $post_id]);
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
        $this->log_debug( 'API Endpoint URL for verification of event: ' . $endpoint_url );
    
        $response = wp_remote_get( $endpoint_url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
                'Accept'        => 'application/json',
            ),
        ));

// Construct cURL command for debug (optional)
        /*
        $curl_command = sprintf(
            "curl -X GET \"%s\" \\\n"
            . "     -H \"Authorization: Bearer %s\" \\\n"
            . "     -H \"Accept: application/json\"",
            $endpoint_url,
            $access_token
        );
        $curl_command .= sprintf(" \\\n     # Organizer ID: %s", $organizer_id);
    
        $this->log_debug('Constructed cURL command for debug:' . "\n" . $curl_command);
        error_log("[Betait_Letsreg_Debug_Curl] " . $curl_command);
        */
        

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
