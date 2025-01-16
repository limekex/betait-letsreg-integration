<?php
/**
 * Plugin Name: Let'sReg Integration Test
 * Description: Eksempel-plugin for å teste integrasjon med Deltager (LetsReg) via integrate.deltager.no. Viser arrangører, lar deg velge arrangør og lagre affid. Henter nå navn og kontaktperson.
 * Version: 0.5
 * Author: Ditt Navn
 * Text Domain: letsreg-integration-test
 */

/**
 * ==============================
 *  GLOBALT: KONSTANTER OG FUNKSJONER
 * ==============================
 */

// Her kan du skru debug av/på
define('LETSREG_DEBUG', true);

/**
 * Enkel debug-funksjon. Skriver til debug.log dersom LETSREG_DEBUG er true.
 *
 * Husk at WP_DEBUG og WP_DEBUG_LOG må være aktivert i wp-config.php
 */
function letsreg_integration_debug_log($message) {
    if (LETSREG_DEBUG) {
        if (is_array($message) || is_object($message)) {
            error_log(print_r($message, true));
        } else {
            error_log($message);
        }
    }
}

/**
 * ==============================
 *  AKTIVERING / DEAKTIVERING
 * ==============================
 */

/**
 * Kalles ved aktivering av plugin (oppretter default-opsjoner m.m.)
 */
function letsreg_integration_activate() {
    // Opprett standard-opsjoner i databasen om de ikke finnes
    add_option('letsreg_username', '');
    add_option('letsreg_password', '');
    add_option('letsreg_affid', '1'); // Default affiliate ID
    add_option('letsreg_access_token', '');
    // "main_affid" for å kunne gå tilbake hvis vi bytter
    add_option('letsreg_main_affid', '1');
    // Vi oppretter en standard-liste for organisører (tom)
    add_option('letsreg_organizers_list', array());
}
register_activation_hook(__FILE__, 'letsreg_integration_activate');

/**
 * Kalles ved deaktivering av plugin (valgfritt - rydding, sletting, etc.)
 */
function letsreg_integration_deactivate() {
    // Valgfritt: Slette midlertidige transients, tømme cache, etc.
}
register_deactivation_hook(__FILE__, 'letsreg_integration_deactivate');

/**
 * ==============================
 *  ADMINMENY & SIDE
 * ==============================
 */

/**
 * Legger til en undermeny under "Settings" (Innstillinger) i WordPress admin.
 */
function letsreg_integration_add_admin_menu() {
    add_options_page(
        __('LetsReg Settings', 'letsreg-integration-test'),  // side-tittel
        __('LetsReg Settings', 'letsreg-integration-test'),  // meny-navn
        'manage_options',                                    // kapabilitet
        'letsreg-integration',                               // slug
        'letsreg_integration_settings_page'                  // callback-funksjon
    );
}
add_action('admin_menu', 'letsreg_integration_add_admin_menu');

/**
 * ==============================
 *  HOVED-FUNKSJON: ADMIN-SIDE
 * ==============================
 */
function letsreg_integration_settings_page() {
    // Sjekk brukertilganger
    if (!current_user_can('manage_options')) {
        wp_die(__('Du har ikke tilgang til denne siden.', 'letsreg-integration-test'));
    }

    // --- 1) Håndter forskjellige POST-aksjoner ---

    // 1A) Lagrer brukernavn, passord, affid og forsøker å hente token
    if (isset($_POST['letsreg_save_settings'])) {
        check_admin_referer('letsreg_settings_save', 'letsreg_settings_nonce');

        $username = sanitize_text_field($_POST['letsreg_username']);
        $password = sanitize_text_field($_POST['letsreg_password']);
        $affid    = sanitize_text_field($_POST['letsreg_affid']);

        // Oppdater opsjoner
        update_option('letsreg_username', $username);
        update_option('letsreg_password', $password);
        update_option('letsreg_affid', $affid);

        // Oppdater "main_affid" også
        update_option('letsreg_main_affid', $affid);

        // Forsøk å hente token
        $access_token = letsreg_integration_get_token($username, $password, $affid);
        if (!empty($access_token)) {
            update_option('letsreg_access_token', $access_token);
            $message = __('Settings saved and token retrieved successfully!', 'letsreg-integration-test');
        } else {
            $message = __('Settings saved, but failed to retrieve token.', 'letsreg-integration-test');
        }
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($message) . '</p></div>';
    }

    // 1B) Hent organisasjons-liste (arrangører)
    if (isset($_POST['letsreg_fetch_organizers'])) {
        check_admin_referer('letsreg_settings_save', 'letsreg_settings_nonce');

        // Har vi en token?
        $token = get_option('letsreg_access_token', '');
        if (empty($token)) {
            $token = letsreg_integration_refresh_token();
        }

        // Hent arrangører
        $organizers = letsreg_integration_get_organizers($token);
        if (is_array($organizers)) {
            // Lagre listen i en WP-option
            update_option('letsreg_organizers_list', $organizers);
            $message = __('Fetched organizers list successfully.', 'letsreg-integration-test');
        } else {
            $message = __('Failed to fetch organizers list. Check logs.', 'letsreg-integration-test');
        }
        echo '<div class="notice notice-info is-dismissible"><p>' . esc_html($message) . '</p></div>';
    }

    // 1C) Velg en arrangør -> Oppdatere AFFID (dersom du faktisk ønsker å endre AFFID)
    if (isset($_POST['letsreg_select_organizer'])) {
        check_admin_referer('letsreg_settings_save', 'letsreg_settings_nonce');
        $selected_affid = sanitize_text_field($_POST['letsreg_selected_affid']);
        update_option('letsreg_affid', $selected_affid);

        echo '<div class="notice notice-success is-dismissible"><p>'
            . __('AffID updated to: ', 'letsreg-integration-test') . esc_html($selected_affid) . '</p></div>';
    }

    // 1D) Gå tilbake til hoved-arrangør (den “opprinnelige”)
    if (isset($_POST['letsreg_revert_main_organizer'])) {
        check_admin_referer('letsreg_settings_save', 'letsreg_settings_nonce');
        $main_affid = get_option('letsreg_main_affid', '1');
        update_option('letsreg_affid', $main_affid);

        echo '<div class="notice notice-success is-dismissible"><p>'
            . __('AffID reverted to main organizer: ', 'letsreg-integration-test') . esc_html($main_affid) . '</p></div>';
    }

    // --- 2) Hent eksisterende opsjoner for å vise i skjema ---
    $username_stored = get_option('letsreg_username', '');
    $password_stored = get_option('letsreg_password', '');
    $affid_stored    = get_option('letsreg_affid', '1');
    $token_stored    = get_option('letsreg_access_token', '');

    // Arrangør-liste (om hentet)
    $organizers_list = get_option('letsreg_organizers_list', array());
    ?>

    <div class="wrap">
        <h1><?php echo esc_html(__('LetsReg Integration Settings', 'letsreg-integration-test')); ?></h1>

        <!-- Skjema for brukernavn/passord/affid -->
        <form method="post">
            <?php wp_nonce_field('letsreg_settings_save', 'letsreg_settings_nonce'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php _e('Username', 'letsreg-integration-test'); ?></th>
                    <td><input type="text" name="letsreg_username" value="<?php echo esc_attr($username_stored); ?>" size="40" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Password', 'letsreg-integration-test'); ?></th>
                    <td><input type="password" name="letsreg_password" value="<?php echo esc_attr($password_stored); ?>" size="40" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('AffID (affiliateID)', 'letsreg-integration-test'); ?></th>
                    <td><input type="text" name="letsreg_affid" value="<?php echo esc_attr($affid_stored); ?>" size="5" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Last Access Token', 'letsreg-integration-test'); ?></th>
                    <td>
                        <textarea readonly rows="3" cols="60"><?php echo esc_textarea($token_stored); ?></textarea>
                    </td>
                </tr>
            </table>

            <?php submit_button(__('Save and Retrieve Token', 'letsreg-integration-test'), 'primary', 'letsreg_save_settings'); ?>
        </form>

        <hr />

        <!-- Del 2: Hent og vis arrangørliste -->
        <h2><?php echo esc_html(__('Organizers', 'letsreg-integration-test')); ?></h2>

        <form method="post">
            <?php wp_nonce_field('letsreg_settings_save', 'letsreg_settings_nonce'); ?>
            <?php submit_button(__('Fetch Organizers', 'letsreg-integration-test'), 'secondary', 'letsreg_fetch_organizers'); ?>
        </form>

        <?php 
        /**
         * Nå viser vi en dropdown med formatet:
         * (id) - name (contactPersonName)
         */
        if (!empty($organizers_list) && is_array($organizers_list)) : ?>

            <p><?php _e('Select one of the organizers to update AffID:', 'letsreg-integration-test'); ?></p>
            <form method="post">
                <?php wp_nonce_field('letsreg_settings_save', 'letsreg_settings_nonce'); ?>

                <select name="letsreg_selected_affid">
                    <?php foreach ($organizers_list as $org) : 
                        /**
                         * Forenklet antagelse at 'id' er AFFID.
                         * Vi bruker 'name' og 'contactPersonName' i labelen
                         */
                        $label = sprintf(
                            '(%d) - %s (%s)',
                            $org['id'],
                            $org['name'],
                            $org['contactPersonName']
                        );
                    ?>
                        <option value="<?php echo esc_attr($org['id']); ?>">
                            <?php echo esc_html($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <?php submit_button(__('Select Organizer', 'letsreg-integration-test'), 'primary', 'letsreg_select_organizer'); ?>
            </form>

            <form method="post" style="margin-top: 10px;">
                <?php wp_nonce_field('letsreg_settings_save', 'letsreg_settings_nonce'); ?>
                <?php submit_button(__('Revert to Main Organizer', 'letsreg-integration-test'), 'secondary', 'letsreg_revert_main_organizer'); ?>
            </form>

        <?php else : ?>
            <p><?php _e('No organizers stored yet or the list is empty.', 'letsreg-integration-test'); ?></p>
        <?php endif; ?>
    </div>

    <?php
}

/**
 * ==============================
 *  API-KALL FUNKSJONER
 * ==============================
 */

/**
 * Gjør et kall mot integrate.deltager.no for å hente access_token
 *  - Uten "/api" i stien.
 *
 * @param string $username
 * @param string $password
 * @param int    $affid
 * @return string|null  Mottatt access_token eller null ved feil
 */
function letsreg_integration_get_token($username, $password, $affid) {
    // POST-data
    $body = array(
        'grant_type' => 'password',
        'username'   => $username,
        'password'   => $password,
        'affid'      => $affid,
    );

    $response = wp_remote_post(
        'https://integrate.deltager.no/token',  // <-- Uten /api
        array(
            'headers' => array(
                'Content-Type' => 'application/x-www-form-urlencoded',
            ),
            'body' => $body,
        )
    );

    // Nettverksfeil?
    if (is_wp_error($response)) {
        letsreg_integration_debug_log('Feil ved token-henting: ' . $response->get_error_message());
        return null;
    }

    // HTTP-status
    $code = wp_remote_retrieve_response_code($response);
    if ($code !== 200) {
        letsreg_integration_debug_log('Feil i HTTP-kall (token). Statuskode: ' . $code);
        return null;
    }

    $body_raw = wp_remote_retrieve_body($response);
    $data = json_decode($body_raw);

    if (isset($data->access_token)) {
        return $data->access_token;
    } else {
        letsreg_integration_debug_log('Ingen access_token i responsen: ' . print_r($data, true));
        return null;
    }
}

/**
 * Henter token på nytt basert på lagrede verdier
 */
function letsreg_integration_refresh_token() {
    $username = get_option('letsreg_username', '');
    $password = get_option('letsreg_password', '');
    $affid    = get_option('letsreg_affid', '1');

    $token = letsreg_integration_get_token($username, $password, $affid);
    if (!empty($token)) {
        update_option('letsreg_access_token', $token);
    }
    return $token;
}

/**
 * Henter en liste av arrangører (organizers) fra integrate.deltager.no (uten /api)
 * og trekker ut "id", "name" og "contactPerson->name".
 *
 * @param string $access_token
 * @return array|false  Returnerer en forenklet liste (array) av arrangører eller false ved feil
 */
function letsreg_integration_get_organizers($access_token) {
    $url = 'https://integrate.deltager.no/organizers'; // <-- Uten /api

    // Gjør GET-kall med Bearer-token
    $response = wp_remote_get(
        $url,
        array(
            'headers' => array(
                'Authorization' => 'bearer ' . $access_token,
            ),
        )
    );

    // Nettverksfeil?
    if (is_wp_error($response)) {
        letsreg_integration_debug_log('Feil ved henting av arrangører: ' . $response->get_error_message());
        return false;
    }

    // HTTP-status
    $code = wp_remote_retrieve_response_code($response);
    if ($code !== 200) {
        letsreg_integration_debug_log('Feil i HTTP-kall for /organizers. Statuskode: ' . $code);
        return false;
    }

    // Les responsen som JSON
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body);

    if (!is_array($data)) {
        // Responsen er ikke et array – logg og avbryt
        letsreg_integration_debug_log('Uventet respons for /organizers: ' . print_r($data, true));
        return false;
    }

    // Bygg en "forenklet" liste med feltene vi er interessert i.
    $organized_list = array();
    foreach ($data as $org_obj) {
        $org_id   = isset($org_obj->id)   ? $org_obj->id   : null;
        $org_name = isset($org_obj->name) ? $org_obj->name : '';

        // Kontaktpersonens navn
        $contact_person_name = '';
        if (isset($org_obj->contactPerson) && isset($org_obj->contactPerson->name)) {
            $contact_person_name = $org_obj->contactPerson->name;
        }

        // Legg til i vår "forenklede" liste
        $organized_list[] = array(
            'id'                 => $org_id,
            'name'               => $org_name,
            'contactPersonName'  => $contact_person_name,
        );
    }

    return $organized_list;
}

