<?php
/**
 * The file that creates our custom post type if activated in the settings.
 *
 * @link       http://betait.no/betaletsreg
 * @since      1.0.0
 *
 * @package    Betait_Letsreg
 * @subpackage Betait_Letsreg/includes
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

class Betait_Letsreg_CPT {

    /**
     * Constructor.
     *
     * We won't do anything here except store maybe references.
     */
    public function __construct() {
        // No code needed unless you want e.g. storing references
    }

    /**
     * Hook into init
     */
    public function register_hooks() {
        add_action( 'init', array( $this, 'maybe_register_lrarr_cpt_and_tax' ) );
    }

    /**
     * Conditionally register 'lr-arr' CPT & taxonomies if user’s storage choice is 'lr-arr'.
     */
    public function maybe_register_lrarr_cpt_and_tax() {

        // Retrieve user’s chosen storage method
        $choice = get_option( 'betait_letsreg_local_storage', 'lr-arr' );

        // Only register if 'lr-arr' is chosen
        if ( 'lr-arr' !== $choice ) {
            return;
        }

        // === 1) Register CPT: lr-arr =================================
        $labels = array(
            'name'               => __( 'LR Arrangementer', 'betait-letsreg' ),
            'singular_name'      => __( 'LR Arrangement', 'betait-letsreg' ),
            'add_new'            => __( 'Legg til arrangement', 'betait-letsreg' ),
            'add_new_item'       => __( 'Legg til nytt arrangement', 'betait-letsreg' ),
            'edit_item'          => __( 'Rediger arrangement', 'betait-letsreg' ),
            'new_item'           => __( 'Nytt arrangement', 'betait-letsreg' ),
            'view_item'          => __( 'Vis arrangement', 'betait-letsreg' ),
            'search_items'       => __( 'Søk arrangementer', 'betait-letsreg' ),
            'not_found'          => __( 'Ingen arrangementer funnet', 'betait-letsreg' ),
            'not_found_in_trash' => __( 'Ingen arrangementer i papirkurv', 'betait-letsreg' ),
        );

        $args = array(
            'label'               => __( 'LR Arrangementer', 'betait-letsreg' ),
            'labels'              => $labels,
            'public'              => true,  // or set false if you want them hidden
            'has_archive'         => true,
            'hierarchical'        => false,
            'supports'            => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt' ),
            'rewrite'             => array( 'slug' => 'lr-arr' ),
            'capability_type'     => 'post',
            'menu_position'       => 6,
            'menu_icon'           => 'dashicons-calendar-alt',
        );
        register_post_type( 'lr-arr', $args );

        // === 2) Hierarchical Taxonomy: arr categories ("lrarr_cat") ==
        $cat_labels = array(
            'name'              => __( 'Kategorier', 'betait-letsreg' ),
            'singular_name'     => __( 'Kategori', 'betait-letsreg' ),
            'search_items'      => __( 'Søk kategorier', 'betait-letsreg' ),
            'all_items'         => __( 'Alle kategorier', 'betait-letsreg' ),
            'parent_item'       => __( 'Foreldre-kategori', 'betait-letsreg' ),
            'parent_item_colon' => __( 'Foreldre-kategori:', 'betait-letsreg' ),
            'edit_item'         => __( 'Rediger kategori', 'betait-letsreg' ),
            'update_item'       => __( 'Oppdater kategori', 'betait-letsreg' ),
            'add_new_item'      => __( 'Legg til ny kategori', 'betait-letsreg' ),
            'new_item_name'     => __( 'Nytt kategori-navn', 'betait-letsreg' ),
        );
        $cat_args = array(
            'hierarchical'      => true,
            'labels'            => $cat_labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'rewrite'           => array( 'slug' => 'lrarr-cat' ),
        );
        register_taxonomy( 'lrarr_cat', 'lr-arr', $cat_args );

        // === 3) Non-hierarchical Taxonomy: arr tags ("lrarr_tag") ===
        $tag_labels = array(
            'name'               => __( 'Tags', 'betait-letsreg' ),
            'singular_name'      => __( 'Tag', 'betait-letsreg' ),
            'search_items'       => __( 'Søk tags', 'betait-letsreg' ),
            'all_items'          => __( 'Alle tags', 'betait-letsreg' ),
            'edit_item'          => __( 'Rediger tag', 'betait-letsreg' ),
            'update_item'        => __( 'Oppdater tag', 'betait-letsreg' ),
            'add_new_item'       => __( 'Legg til ny tag', 'betait-letsreg' ),
            'new_item_name'      => __( 'Nytt tag-navn', 'betait-letsreg' ),
            'popular_items'      => __( 'Populære tags', 'betait-letsreg' ),
            'separate_items_with_commas' => __( 'Skill tags med komma', 'betait-letsreg' ),
        );
        $tag_args = array(
            'hierarchical'      => false,
            'labels'            => $tag_labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'rewrite'           => array( 'slug' => 'lrarr-tag' ),
        );
        register_taxonomy( 'lrarr_tag', 'lr-arr', $tag_args );

        // === 4) Optional: "venue" as non-hierarchical tax
        $venue_labels = array(
            'name'               => __( 'Venue', 'betait-letsreg' ),
            'singular_name'      => __( 'Venue', 'betait-letsreg' ),
            'search_items'       => __( 'Søk venue', 'betait-letsreg' ),
            'all_items'          => __( 'Alle venue', 'betait-letsreg' ),
            'edit_item'          => __( 'Rediger venue', 'betait-letsreg' ),
            'update_item'        => __( 'Oppdater venue', 'betait-letsreg' ),
            'add_new_item'       => __( 'Legg til ny venue', 'betait-letsreg' ),
            'new_item_name'      => __( 'Nytt venue-navn', 'betait-letsreg' ),
        );
        $venue_args = array(
            'hierarchical'      => false,
            'labels'            => $venue_labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'rewrite'           => array( 'slug' => 'lrarr-venue' ),
        );
        register_taxonomy( 'lrarr_venue', 'lr-arr', $venue_args );

        // === 5) Optional: "organizer" as non-hierarchical tax
        $org_labels = array(
            'name'               => __( 'Arrangører', 'betait-letsreg' ),
            'singular_name'      => __( 'Arrangør', 'betait-letsreg' ),
            'search_items'       => __( 'Søk Arrangør', 'betait-letsreg' ),
            'all_items'          => __( 'Alle Arrangører', 'betait-letsreg' ),
            'edit_item'          => __( 'Rediger Arrangør', 'betait-letsreg' ),
            'update_item'        => __( 'Oppdater Arrangør', 'betait-letsreg' ),
            'add_new_item'       => __( 'Legg til ny Arrangør', 'betait-letsreg' ),
            'new_item_name'      => __( 'Nytt Arrangør-navn', 'betait-letsreg' ),
        );
        $org_args = array(
            'hierarchical'      => false,
            'labels'            => $org_labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'rewrite'           => array( 'slug' => 'lrarr-arrangor' ),
        );
        register_taxonomy( 'lrarr_arrangor', 'lr-arr', $org_args );

        // Done. 'lr-arr' + taxonomies are now recognized
    }

}

class Betait_Letsreg_Tax_Meta {

    public function __construct() {
        // Hook for "lrarr_venue"
        add_action( 'lrarr_venue_add_form_fields', array($this, 'add_venue_term_fields') );
        add_action( 'lrarr_venue_edit_form_fields', array($this, 'edit_venue_term_fields'), 10, 2 );
        add_action( 'created_lrarr_venue', array($this, 'save_venue_term_fields'), 10, 2 );
        add_action( 'edited_lrarr_venue', array($this, 'save_venue_term_fields'), 10, 2 );

        // Similarly for "lrarr_arrangor" (Organizer)
        add_action( 'lrarr_arrangor_add_form_fields', array($this, 'add_organizer_term_fields') );
        add_action( 'lrarr_arrangor_edit_form_fields', array($this, 'edit_organizer_term_fields'), 10, 2 );
        add_action( 'created_lrarr_arrangor', array($this, 'save_organizer_term_fields'), 10, 2 );
        add_action( 'edited_lrarr_arrangor', array($this, 'save_organizer_term_fields'), 10, 2 );

        // We also need the media uploader scripts in term edit page:
        add_action( 'admin_enqueue_scripts', array($this, 'enqueue_media_uploader') );
    }

    /**
     * Enqueue the WordPress media scripts so we can use wp.media
     */
    public function enqueue_media_uploader( $hook ) {
        // Only load on taxonomy pages if you'd like:
        // e.g. if "edit-tags.php" or "term.php" is in the $hook for your taxonomy
        // We'll keep it simple and load on all admin pages for now.
        if ( is_admin() ) {
            wp_enqueue_media();
            // optionally enqueue your own JS for 'upload logo' button
        }
    }

    /* ---------------------------
       1) VENUE FIELDS
    --------------------------- */

    // Show fields on "Add New Venue" form
    public function add_venue_term_fields() {
        ?>
        <div class="form-field">
            <label for="venue_phone"><?php esc_html_e('Telefonnummer', 'betait-letsreg'); ?></label>
            <input type="text" name="venue_phone" id="venue_phone" value="">
            <p class="description"><?php esc_html_e('Kontakttelefon for denne Venue.', 'betait-letsreg'); ?></p>
        </div>

        <div class="form-field">
            <label for="venue_website"><?php esc_html_e('Nettside', 'betait-letsreg'); ?></label>
            <input type="url" name="venue_website" id="venue_website" value="">
            <p class="description"><?php esc_html_e('Nettside URL.', 'betait-letsreg'); ?></p>
        </div>

        <!-- Address splitted fields -->
        <div class="form-field">
            <label for="venue_street"><?php esc_html_e('Gateadresse', 'betait-letsreg'); ?></label>
            <input type="text" name="venue_street" id="venue_street" value="">
        </div>
        <div class="form-field">
            <label for="venue_postcode"><?php esc_html_e('Postkode', 'betait-letsreg'); ?></label>
            <input type="text" name="venue_postcode" id="venue_postcode" value="">
        </div>
        <div class="form-field">
            <label for="venue_city"><?php esc_html_e('By', 'betait-letsreg'); ?></label>
            <input type="text" name="venue_city" id="venue_city" value="">
        </div>
        <div class="form-field">
            <label for="venue_country"><?php esc_html_e('Land', 'betait-letsreg'); ?></label>
            <input type="text" name="venue_country" id="venue_country" value="">
        </div>

        <!-- Media uploader for logo -->
        <div class="form-field">
            <label for="venue_logo"><?php esc_html_e('Logo', 'betait-letsreg'); ?></label>
            <input type="hidden" name="venue_logo" id="venue_logo" value="">
            <button type="button" class="button" id="venue_logo_button">
                <?php esc_html_e('Last opp logo', 'betait-letsreg'); ?>
            </button>
            <p class="description"><?php esc_html_e('Last opp eller velg logo for dette venue.', 'betait-letsreg'); ?></p>
            <div id="venue_logo_preview"></div>
        </div>

        <script>
        jQuery(document).ready(function($){
            var file_frame;
            $('#venue_logo_button').on('click', function(e){
                e.preventDefault();
                // If the media frame already exists, reopen it.
                if ( file_frame ) {
                    file_frame.open();
                    return;
                }
                // Create the media frame.
                file_frame = wp.media({
                    title: '<?php echo esc_js(__('Velg en Venue Logo', 'betait-letsreg')); ?>',
                    button: {
                        text: '<?php echo esc_js(__('Bruk dette bildet', 'betait-letsreg')); ?>'
                    },
                    multiple: false
                });
                // When an image is selected, run a callback.
                file_frame.on('select', function(){
                    var attachment = file_frame.state().get('selection').first().toJSON();
                    $('#venue_logo').val(attachment.id);
                    // Preview
                    $('#venue_logo_preview').html(
                        '<img src="' + attachment.url + '" style="max-width:150px;height:auto;" />'
                    );
                });
                // Finally, open the modal
                file_frame.open();
            });
        });
        </script>
        <?php
    }

    // Show fields on "Edit Venue" form
    public function edit_venue_term_fields( $term ) {
        $phone     = get_term_meta( $term->term_id, 'venue_phone', true );
        $website   = get_term_meta( $term->term_id, 'venue_website', true );
        $street    = get_term_meta( $term->term_id, 'venue_street', true );
        $postcode  = get_term_meta( $term->term_id, 'venue_postcode', true );
        $city      = get_term_meta( $term->term_id, 'venue_city', true );
        $country   = get_term_meta( $term->term_id, 'venue_country', true );
        $logo_id   = get_term_meta( $term->term_id, 'venue_logo', true );
        $logo_url  = $logo_id ? wp_get_attachment_image_url( $logo_id, 'thumbnail' ) : '';

        ?>
        <tr class="form-field">
            <th scope="row"><label for="venue_phone"><?php esc_html_e('Phone', 'betait-letsreg'); ?></label></th>
            <td>
                <input type="text" name="venue_phone" id="venue_phone" value="<?php echo esc_attr($phone); ?>">
                <p class="description"><?php esc_html_e('Kontakttelefon for denne Venue.', 'betait-letsreg'); ?></p>
            </td>
        </tr>

        <tr class="form-field">
            <th scope="row"><label for="venue_website"><?php esc_html_e('Nettside', 'betait-letsreg'); ?></label></th>
            <td>
                <input type="url" name="venue_website" id="venue_website" value="<?php echo esc_attr($website); ?>">
                <p class="description"><?php esc_html_e('Nettsteds URL.', 'betait-letsreg'); ?></p>
            </td>
        </tr>

        <!-- Address splitted fields -->
        <tr class="form-field">
            <th scope="row"><label for="venue_street"><?php esc_html_e('Gateadresse', 'betait-letsreg'); ?></label></th>
            <td>
                <input type="text" name="venue_street" id="venue_street" value="<?php echo esc_attr($street); ?>">
            </td>
        </tr>
        <tr class="form-field">
            <th scope="row"><label for="venue_postcode"><?php esc_html_e('Postnummer', 'betait-letsreg'); ?></label></th>
            <td>
                <input type="text" name="venue_postcode" id="venue_postcode" value="<?php echo esc_attr($postcode); ?>">
            </td>
        </tr>
        <tr class="form-field">
            <th scope="row"><label for="venue_city"><?php esc_html_e('By', 'betait-letsreg'); ?></label></th>
            <td>
                <input type="text" name="venue_city" id="venue_city" value="<?php echo esc_attr($city); ?>">
            </td>
        </tr>
        <tr class="form-field">
            <th scope="row"><label for="venue_country"><?php esc_html_e('Land', 'betait-letsreg'); ?></label></th>
            <td>
                <input type="text" name="venue_country" id="venue_country" value="<?php echo esc_attr($country); ?>">
            </td>
        </tr>

        <tr class="form-field">
            <th scope="row"><label for="venue_logo"><?php esc_html_e('Venue Logo', 'betait-letsreg'); ?></label></th>
            <td>
                <input type="hidden" name="venue_logo" id="venue_logo" value="<?php echo esc_attr($logo_id); ?>">
                <button type="button" class="button" id="venue_logo_button_edit">
                    <?php esc_html_e('Upload / Select Logo', 'betait-letsreg'); ?>
                </button>
                <p class="description"><?php esc_html_e('Last opp eller velg en logo for denne Venue.', 'betait-letsreg'); ?></p>
                <div id="venue_logo_preview">
                  <?php 
                  if ( $logo_url ) {
                      echo '<img src="' . esc_url($logo_url) . '" style="max-width:150px;height:auto;" />';
                  }
                  ?>
                </div>
            </td>
        </tr>

        <script>
        jQuery(document).ready(function($){
            var file_frame;
            $('#venue_logo_button_edit').on('click', function(e){
                e.preventDefault();
                if ( file_frame ) {
                    file_frame.open();
                    return;
                }
                file_frame = wp.media({
                    title: '<?php echo esc_js(__('Velg Venue Logo', 'betait-letsreg')); ?>',
                    button: {
                        text: '<?php echo esc_js(__('Bruk dette bildet', 'betait-letsreg')); ?>'
                    },
                    multiple: false
                });
                file_frame.on('select', function(){
                    var attachment = file_frame.state().get('selection').first().toJSON();
                    $('#venue_logo').val(attachment.id);
                    $('#venue_logo_preview').html(
                        '<img src="' + attachment.url + '" style="max-width:150px;height:auto;" />'
                    );
                });
                file_frame.open();
            });
        });
        </script>
        <?php
    }

    // Save the metadata when creating or editing the venue
    public function save_venue_term_fields( $term_id ) {
        // phone, website
        if ( isset($_POST['venue_phone']) ) {
            update_term_meta( $term_id, 'venue_phone', sanitize_text_field($_POST['venue_phone']) );
        }
        if ( isset($_POST['venue_website']) ) {
            update_term_meta( $term_id, 'venue_website', esc_url_raw($_POST['venue_website']) );
        }
        // address splitted
        if ( isset($_POST['venue_street']) ) {
            update_term_meta( $term_id, 'venue_street', sanitize_text_field($_POST['venue_street']) );
        }
        if ( isset($_POST['venue_postcode']) ) {
            update_term_meta( $term_id, 'venue_postcode', sanitize_text_field($_POST['venue_postcode']) );
        }
        if ( isset($_POST['venue_city']) ) {
            update_term_meta( $term_id, 'venue_city', sanitize_text_field($_POST['venue_city']) );
        }
        if ( isset($_POST['venue_country']) ) {
            update_term_meta( $term_id, 'venue_country', sanitize_text_field($_POST['venue_country']) );
        }
        // logo as attachment ID
        if ( isset($_POST['venue_logo']) ) {
            $logo_id = intval($_POST['venue_logo']);
            update_term_meta( $term_id, 'venue_logo', $logo_id );
        }
    }

    /* ------------------------------------------------------------------
       2) ORGANIZER FIELDS (lrarr_arrangor)
    ------------------------------------------------------------------ */

    /**
     * Render fields in the "Add New Organizer" form
     */
    public function add_organizer_term_fields() {
        ?>
        <div class="form-field">
            <label for="org_email"><?php esc_html_e('E-post', 'betait-letsreg'); ?></label>
            <input type="text" name="org_email" id="org_email" value="">
            <p class="description"><?php esc_html_e('E-postadresse for denne arrangøren.', 'betait-letsreg'); ?></p>
        </div>
        <div class="form-field">
            <label for="org_phone"><?php esc_html_e('Telefon', 'betait-letsreg'); ?></label>
            <input type="text" name="org_phone" id="org_phone" value="">
            <p class="description"><?php esc_html_e('Kontakttelefon for denne arrangøren.', 'betait-letsreg'); ?></p>
        </div>

        <div class="form-field">
            <label for="org_website"><?php esc_html_e('Nettsted', 'betait-letsreg'); ?></label>
            <input type="url" name="org_website" id="org_website" value="">
            <p class="description"><?php esc_html_e('Arrangørens nettsted.', 'betait-letsreg'); ?></p>
        </div>

        <!-- splitted address -->
        <div class="form-field">
            <label for="org_street"><?php esc_html_e('Gateadresse', 'betait-letsreg'); ?></label>
            <input type="text" name="org_street" id="org_street" value="">
        </div>
        <div class="form-field">
            <label for="org_postcode"><?php esc_html_e('Postnummer', 'betait-letsreg'); ?></label>
            <input type="text" name="org_postcode" id="org_postcode" value="">
        </div>
        <div class="form-field">
            <label for="org_city"><?php esc_html_e('By', 'betait-letsreg'); ?></label>
            <input type="text" name="org_city" id="org_city" value="">
        </div>
        <div class="form-field">
            <label for="org_country"><?php esc_html_e('Land', 'betait-letsreg'); ?></label>
            <input type="text" name="org_country" id="org_country" value="">
        </div>

        <!-- Media uploader for logo -->
        <div class="form-field">
            <label for="org_logo"><?php esc_html_e('Arrangørlogo', 'betait-letsreg'); ?></label>
            <input type="hidden" name="org_logo" id="org_logo" value="">
            <button type="button" class="button" id="org_logo_button">
                <?php esc_html_e('Last opp logo', 'betait-letsreg'); ?>
            </button>
            <div id="org_logo_preview" style="margin-top:10px;"></div>
        </div>
        <script>
        jQuery(document).ready(function($){
            var file_frame;
            $('#org_logo_button').on('click', function(e){
                e.preventDefault();
                if ( file_frame ) {
                    file_frame.open();
                    return;
                }
                file_frame = wp.media({
                    title: '<?php echo esc_js(__('Velg arrangørlogo', 'betait-letsreg')); ?>',
                    button: { text: '<?php echo esc_js(__('Bruk dette bildet', 'betait-letsreg')); ?>' },
                    multiple: false
                });
                file_frame.on('select', function(){
                    var attachment = file_frame.state().get('selection').first().toJSON();
                    $('#org_logo').val(attachment.id);
                    $('#org_logo_preview').html(
                        '<img src="' + attachment.url + '" style="max-width:150px;height:auto;" />'
                    );
                });
                file_frame.open();
            });
        });
        </script>
        <?php
    }

    /**
     * Render fields in the "Edit Organizer" form
     */
    public function edit_organizer_term_fields( $term ) {
        $email    = get_term_meta( $term->term_id, 'org_email', true );
        $phone    = get_term_meta( $term->term_id, 'org_phone', true );
        $website  = get_term_meta( $term->term_id, 'org_website', true );
        $street   = get_term_meta( $term->term_id, 'org_street', true );
        $postcode = get_term_meta( $term->term_id, 'org_postcode', true );
        $city     = get_term_meta( $term->term_id, 'org_city', true );
        $country  = get_term_meta( $term->term_id, 'org_country', true );
        $logo_id  = get_term_meta( $term->term_id, 'org_logo', true );
        $logo_url = $logo_id ? wp_get_attachment_image_url( $logo_id, 'thumbnail' ) : '';
        ?>
        <tr class="form-field">
            <th scope="row"><label for="email"><?php esc_html_e('E-post', 'betait-letsreg'); ?></label></th>
            <td>
                <input type="text" name="org_email" id="org_email" value="<?php echo esc_attr($email); ?>">
                <p class="description"><?php esc_html_e('E-postadresse for denne arrangøren.', 'betait-letsreg'); ?></p>
            </td>
        </tr>
        <tr class="form-field">
            <th scope="row"><label for="org_phone"><?php esc_html_e('Telefon', 'betait-letsreg'); ?></label></th>
            <td>
                <input type="text" name="org_phone" id="org_phone" value="<?php echo esc_attr($phone); ?>">
                <p class="description"><?php esc_html_e('Kontakttelefon for denne arrangøren.', 'betait-letsreg'); ?></p>
            </td>
        </tr>
        <tr class="form-field">
            <th scope="row"><label for="org_website"><?php esc_html_e('Nettsted', 'betait-letsreg'); ?></label></th>
            <td>
                <input type="url" name="org_website" id="org_website" value="<?php echo esc_attr($website); ?>">
                <p class="description"><?php esc_html_e('Arrangørens nettsted.', 'betait-letsreg'); ?></p>
            </td>
        </tr>
        <tr class="form-field">
            <th scope="row"><label for="org_street"><?php esc_html_e('Gateadresse', 'betait-letsreg'); ?></label></th>
            <td>
                <input type="text" name="org_street" id="org_street" value="<?php echo esc_attr($street); ?>">
            </td>
        </tr>
        <tr class="form-field">
            <th scope="row"><label for="org_postcode"><?php esc_html_e('Postnummer', 'betait-letsreg'); ?></label></th>
            <td>
                <input type="text" name="org_postcode" id="org_postcode" value="<?php echo esc_attr($postcode); ?>">
            </td>
        </tr>
        <tr class="form-field">
            <th scope="row"><label for="org_city"><?php esc_html_e('By', 'betait-letsreg'); ?></label></th>
            <td>
                <input type="text" name="org_city" id="org_city" value="<?php echo esc_attr($city); ?>">
            </td>
        </tr>
        <tr class="form-field">
            <th scope="row"><label for="org_country"><?php esc_html_e('Land', 'betait-letsreg'); ?></label></th>
            <td>
                <input type="text" name="org_country" id="org_country" value="<?php echo esc_attr($country); ?>">
            </td>
        </tr>

        <tr class="form-field">
            <th scope="row"><label for="org_logo"><?php esc_html_e('Arrangørlogo', 'betait-letsreg'); ?></label></th>
            <td>
                <input type="hidden" name="org_logo" id="org_logo" value="<?php echo esc_attr($logo_id); ?>">
                <button type="button" class="button" id="org_logo_button_edit">
                    <?php esc_html_e('Last opp / velg logo', 'betait-letsreg'); ?>
                </button>
                <div id="org_logo_preview" style="margin-top:10px;">
                  <?php if ( $logo_url ) {
                      echo '<img src="' . esc_url($logo_url) . '" style="max-width:150px;height:auto;" />';
                  } ?>
                </div>
            </td>
        </tr>

        <script>
        jQuery(document).ready(function($){
            var file_frame;
            $('#org_logo_button_edit').on('click', function(e){
                e.preventDefault();
                if ( file_frame ) {
                    file_frame.open();
                    return;
                }
                file_frame = wp.media({
                    title: '<?php echo esc_js(__('Velg arrangørlogo', 'betait-letsreg')); ?>',
                    button: { text: '<?php echo esc_js(__('Bruk dette bildet', 'betait-letsreg')); ?>' },
                    multiple: false
                });
                file_frame.on('select', function(){
                    var attachment = file_frame.state().get('selection').first().toJSON();
                    $('#org_logo').val(attachment.id);
                    $('#org_logo_preview').html(
                        '<img src="' + attachment.url + '" style="max-width:150px;height:auto;" />'
                    );
                });
                file_frame.open();
            });
        });
        </script>
        <?php
    }

    /**
     * Save the Organizer term meta
     */
    public function save_organizer_term_fields( $term_id ) {
        if ( isset($_POST['org_email']) ) {
            update_term_meta( $term_id, 'org_email', sanitize_text_field($_POST['org_email']) );
        }
        if ( isset($_POST['org_phone']) ) {
            update_term_meta( $term_id, 'org_phone', sanitize_text_field($_POST['org_phone']) );
        }
        if ( isset($_POST['org_website']) ) {
            update_term_meta( $term_id, 'org_website', esc_url_raw($_POST['org_website']) );
        }

        if ( isset($_POST['org_street']) ) {
            update_term_meta( $term_id, 'org_street', sanitize_text_field($_POST['org_street']) );
        }
        if ( isset($_POST['org_postcode']) ) {
            update_term_meta( $term_id, 'org_postcode', sanitize_text_field($_POST['org_postcode']) );
        }
        if ( isset($_POST['org_city']) ) {
            update_term_meta( $term_id, 'org_city', sanitize_text_field($_POST['org_city']) );
        }
        if ( isset($_POST['org_country']) ) {
            update_term_meta( $term_id, 'org_country', sanitize_text_field($_POST['org_country']) );
        }

        if ( isset($_POST['org_logo']) ) {
            $logo_id = intval($_POST['org_logo']);
            update_term_meta( $term_id, 'org_logo', $logo_id );
        }
    }

}

