<?php
/**
 * The file that defines the Metabox for the custom CPT "lr-arr",
 * displaying imported metadata from LetsReg (fields, dates, contact, etc.).
 *
 * @link       http://betait.no/betaletsreg
 * @since      1.0.0
 *
 * @package    Betait_Letsreg
 * @subpackage Betait_Letsreg/includes
 */

class Betait_Letsreg_Metabox {

    /**
     * Hook into WordPress to add the metabox.
     */
    public function __construct() {
        add_action('add_meta_boxes', array($this, 'register_metabox'));
        add_action('add_meta_boxes', array($this, 'register_debug_metabox'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_datepicker_scripts'));
    }

    /**
     * Register the main metabox for our CPT "lr-arr".
     */
    public function register_metabox() {
        add_meta_box(
            'lr_arr_metabox', // ID
            __('Arrangement Details', 'betait-letsreg'), // Title
            array($this, 'render_lr_arr_metabox'), // Callback
            'lr-arr', // CPT slug
            'normal', // Context
            'high' // Priority
        );
    }

    /**
     * Register the debug metabox for all relevant post types if debug mode is enabled.
     */
    public function register_debug_metabox() {
        // Check if debug mode is enabled
        if (!get_option('betait_letsreg_debug', false)) {
            return;
        }

        // Dynamically fetch post types or use fallback
        $post_types = get_option('betait_letsreg_post_types', ['tribe_events', 'lr-arr', 'post']);
        
        foreach ($post_types as $post_type) {
            add_meta_box(
                'betait_letsreg_debug_metabox',
                __('LetsReg Debug Info', 'betait-letsreg'),
                array($this, 'render_debug_metabox'),
                $post_type,
                'side',
                'high'
            );
        }
    }

    /**
     * Render the debug metabox, showing all metadata for the current post.
     */
    public function render_debug_metabox($post) {
        // Get all post meta for the current post
        $meta = get_post_meta($post->ID);

        if (empty($meta)) {
            echo '<p>' . __('No metadata found for this post.', 'betait-letsreg') . '</p>';
            return;
        }

        // Display metadata in a readable format
        echo '<div style="max-height: 300px; overflow-y: auto; font-size: 12px; background: #f9f9f9; padding: 10px; border: 1px solid #ddd;">';
        echo '<ul>';
        foreach ($meta as $key => $value) {
            echo '<li><strong>' . esc_html($key) . '</strong>: ' . esc_html(json_encode($value)) . '</li>';
        }
        echo '</ul>';
        echo '</div>';
    }

    /**
     * Enqueue datepicker scripts if needed.
     */
    public function enqueue_datepicker_scripts($hook) {
        global $post;
        if (!in_array($hook, ['post.php', 'post-new.php']) || empty($post) || $post->post_type !== 'lr-arr') {
            return;
        }

        wp_enqueue_style('jquery-ui-datepicker', '//code.jquery.com/ui/1.13.2/themes/smoothness/jquery-ui.css');
        wp_enqueue_script('jquery-ui-datepicker');
    }

    /**
     * Render the main metabox HTML for "lr-arr".
     */
    public function render_lr_arr_metabox( $post ) {
        // We'll read from meta, e.g.:
        $lr_id        = get_post_meta( $post->ID, 'lr_id', true );
        $lr_active    = get_post_meta( $post->ID, 'lr_active', true );
        $lr_published = get_post_meta( $post->ID, 'lr_published', true );
        $lr_searchable= get_post_meta( $post->ID, 'lr_searchable', true );
        $lr_isPaid    = get_post_meta( $post->ID, 'lr_isPaidEvent', true );
        $lr_hasWait   = get_post_meta( $post->ID, 'lr_hasWaitinglist', true );

        // Date/time
        $start_date   = get_post_meta( $post->ID, 'lr_startDate', true );
        $end_date     = get_post_meta( $post->ID, 'lr_endDate', true );
        $reg_start    = get_post_meta( $post->ID, 'lr_registrationStartDate', true );
        $reg_end      = get_post_meta( $post->ID, 'lr_registrationEndDate', true );

        // Additional booleans for visibility
        $start_vis    = get_post_meta( $post->ID, 'lr_startDateVisible', true );
        $end_vis      = get_post_meta( $post->ID, 'lr_endDateVisible', true );
        $regend_vis   = get_post_meta( $post->ID, 'lr_registrationEndDateVisible', true );

        // Contact Person
        $contact_name  = get_post_meta( $post->ID, 'lr_contact_name', true );
        $contact_email = get_post_meta( $post->ID, 'lr_contact_email', true );
        $contact_phone = get_post_meta( $post->ID, 'lr_contact_phone', true );
        $contact_mob   = get_post_meta( $post->ID, 'lr_contact_mobile', true );

        // Possibly parse JSON for prices
        $prices_json = get_post_meta( $post->ID, 'lr_prices', true );
        $prices = [];
        if ( $prices_json ) {
            $maybe_array = json_decode( $prices_json, true );
            if ( is_array($maybe_array) ) {
                $prices = $maybe_array;
            }
        }

        // Start output
        echo '<div class="lrarr-metabox-wrapper">';

        // Basic info group
        echo '<div class="lrarr-metabox-field-group">';
        echo '<h3>' . esc_html__('Grunnleggende info', 'betait-letsreg') . '</h3>';

        // ID
        echo '<div class="lrarr-metabox-field">';
        echo '<label>' . esc_html__('LetsReg Arrangement ID', 'betait-letsreg') . '</label>';
        printf('<input type="text" readonly value="%s" />', esc_attr($lr_id));
        echo '</div>';

        // Basic Booleans as checkboxes (read-only? we might just show "Yes"/"No")
        echo '<div class="lrarr-metabox-field">';
        echo '<label>' . esc_html__('Arrangementflagg', 'betait-letsreg') . '</label>';
        echo '<div class="lrarr-boolean-group lrarr-boolean-group-flags">';
        $this->renderReadonlyCheckbox( esc_html__('Aktiv','betait-letsreg'), $lr_active );
        $this->renderReadonlyCheckbox( esc_html__('Publisert','betait-letsreg'), $lr_published );
        $this->renderReadonlyCheckbox( esc_html__('Søkbar','betait-letsreg'), $lr_searchable );
        $this->renderReadonlyCheckbox( esc_html__('Betalt arrangement','betait-letsreg'), $lr_isPaid );
        $this->renderReadonlyCheckbox( esc_html__('Venteliste','betait-letsreg'), $lr_hasWait );
        echo '</div>';
        echo '</div>';

        echo '</div>'; // end Basic Info group

        // Dates group
        echo '<div class="lrarr-metabox-field-group">';
        echo '<h3>' . esc_html__('Datoer og synlighet', 'betait-letsreg') . '</h3>';

        // Start date
        echo '<div class="lrarr-metabox-field">';
        echo '<label>' . esc_html__('Oppstartsdato og tid', 'betait-letsreg') . '</label>';
        $date_val = $start_date ? date_i18n( 'Y-m-d H:i', strtotime($start_date) ) : '';
        printf('<input type="text" class="lrarr-datepicker" readonly value="%s" />', esc_attr($date_val));
        echo '</div>';

        // Start date visible
        echo '<div class="lrarr-metabox-field">';
        echo '<label>' . esc_html__('Vis oppstartsdato offentlig?', 'betait-letsreg') . '</label>';
        $this->renderReadonlyCheckbox('', $start_vis);
        echo '</div>';

        // End date
        echo '<div class="lrarr-metabox-field">';
        echo '<label>' . esc_html__('Sluttdato og tidspunkt', 'betait-letsreg') . '</label>';
        $date_val = $end_date ? date_i18n( 'Y-m-d H:i', strtotime($end_date) ) : '';
        printf('<input type="text" class="lrarr-datepicker" readonly value="%s" />', esc_attr($date_val));
        echo '</div>';

        echo '<div class="lrarr-metabox-field">';
        echo '<label>' . esc_html__('Vis sluttdato offentlig?', 'betait-letsreg') . '</label>';
        $this->renderReadonlyCheckbox('', $end_vis);
        echo '</div>';

        // Registration start
        echo '<div class="lrarr-metabox-field">';
        echo '<label>' . esc_html__('Påmeldingstart og tid', 'betait-letsreg') . '</label>';
        $date_val = $reg_start ? date_i18n( 'Y-m-d H:i', strtotime($reg_start) ) : '';
        printf('<input type="text" class="lrarr-datepicker" readonly value="%s" />', esc_attr($date_val));
        echo '</div>';

        // Registration end
        echo '<div class="lrarr-metabox-field">';
        echo '<label>' . esc_html__('Påmeldingslutt og tid', 'betait-letsreg') . '</label>';
        $date_val = $reg_end ? date_i18n( 'Y-m-d H:i', strtotime($reg_end) ) : '';
        printf('<input type="text" class="lrarr-datepicker" readonly value="%s" />', esc_attr($date_val));
        echo '</div>';

        // Registration end date visible
        echo '<div class="lrarr-metabox-field">';
        echo '<label>' . esc_html__('Vis påmeldingsinfo offentlig?', 'betait-letsreg') . '</label>';
        $this->renderReadonlyCheckbox('', $regend_vis);
        echo '</div>';

        echo '</div>'; // end Dates group

        // Contact group
        echo '<div class="lrarr-metabox-field-group">';
        echo '<h3>' . esc_html__('Kontaktperson for arrangementet', 'betait-letsreg') . '</h3>';

        echo '<div class="lrarr-metabox-field">';
        echo '<label>' . esc_html__('Navn', 'betait-letsreg') . '</label>';
        printf('<input type="text" readonly value="%s" />', esc_attr($contact_name));
        echo '</div>';

        echo '<div class="lrarr-metabox-field">';
        echo '<label>' . esc_html__('E-post', 'betait-letsreg') . '</label>';
        printf('<input type="text" readonly value="%s" />', esc_attr($contact_email));
        echo '</div>';

        echo '<div class="lrarr-metabox-field">';
        echo '<label>' . esc_html__('Telefon', 'betait-letsreg') . '</label>';
        printf('<input type="text" readonly value="%s" />', esc_attr($contact_phone));
        echo '</div>';

        echo '<div class="lrarr-metabox-field">';
        echo '<label>' . esc_html__('Mobil', 'betait-letsreg') . '</label>';
        printf('<input type="text" readonly value="%s" />', esc_attr($contact_mob));
        echo '</div>';

        echo '</div>'; // end Contact group

        // Prices group (parsed from JSON)
        echo '<div class="lrarr-metabox-field-group">';
        echo '<h3>' . esc_html__('Priser', 'betait-letsreg') . '</h3>';

        if ( empty($prices) ) {
            echo '<p>' . esc_html__('Ingen priser funnet.', 'betait-letsreg') . '</p>';
        } else {
            echo '<table class="lrarr-prices-table">';
            echo '<thead><tr>';
            echo '<th>' . esc_html__('Navn','betait-letsreg') . '</th>';
            echo '<th>' . esc_html__('Aktiv','betait-letsreg') . '</th>';
            echo '<th>' . esc_html__('Pris','betait-letsreg') . '</th>';
            echo '<th>' . esc_html__('Registert','betait-letsreg') . '</th>';
            echo '<th>' . esc_html__('Tilgjengelig','betait-letsreg') . '</th>';
            echo '</tr></thead>';
            echo '<tbody>';
            foreach ( $prices as $p ) {
                // $p might contain: "name", "active", "price", "registered" etc.
                $activeFlag  = !empty($p['active']) ? __('Ja','betait-letsreg') : __('Nei','betait-letsreg');
                $pName       = isset($p['name']) ? $p['name'] : '';
                $pPrice      = isset($p['price']) ? $p['price'] : '';
                $pRegistered = isset($p['registered']) ? $p['registered'] : '';
                $pAvailable  = isset($p['available']) ? $p['available'] : '';
                echo '<tr>';
                echo '<td>' . esc_html($pName) . '</td>';
                echo '<td>' . esc_html($activeFlag) . '</td>';
                echo '<td>' . esc_html($pPrice) . '</td>';
                echo '<td>' . esc_html($pRegistered) . '</td>';
                echo '<td>' . esc_html($pAvailable) . '</td>';
                echo '</tr>';
            }
            echo '</tbody></table>';
        }
        echo '</div>'; // end Prices group

        echo '</div>'; // end wrapper
    }

    /**
     * Helper function to render read-only checkboxes.
     */
    private function renderReadonlyCheckbox($label, $value) {
        $isChecked = in_array(strtolower($value), ['1', 'yes', 'true', 'on']);
        echo '<label><input type="checkbox" disabled ' . checked($isChecked, true, false) . ' /> ' . esc_html($label) . '</label>';
    }
}
