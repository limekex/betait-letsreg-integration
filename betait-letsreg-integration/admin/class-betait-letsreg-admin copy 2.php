<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://betait.no/betaletsreg
 * @since      1.0.0
 *
 * @package    Betait_Letsreg
 * @subpackage Betait_Letsreg/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * @package    Betait_Letsreg
 * @subpackage Betait_Letsreg/admin
 */
class Betait_Letsreg_Admin {

    /**
     * The ID (slug) of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string
     */
    private $betait_letsreg;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param string $betait_letsreg The name/slug of this plugin.
     * @param string $version        The version of this plugin.
     */
    public function __construct( $betait_letsreg, $version ) {
        $this->betait_letsreg = $betait_letsreg;
        $this->version        = $version;

        // Hook into WordPress
        add_action('admin_menu', array($this, 'add_plugin_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_styles'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        wp_enqueue_style(
            $this->betait_letsreg,
            plugin_dir_url( __FILE__ ) . 'css/betait-letsreg-admin.css',
            array(),
            $this->version,
            'all'
        );
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        wp_enqueue_script(
            $this->betait_letsreg,
            plugin_dir_url( __FILE__ ) . 'js/betait-letsreg-admin.js',
            array( 'jquery' ),
            $this->version,
            true
        );
    }

    /**
     * Legg til et eget toppnivå-menyvalg med tre undermenyer i WordPress admin.
     *
     * @since 1.0.0
     * @return void
     */
    public function add_plugin_admin_menu() {
        // Toppnivå-meny
        add_menu_page(
            __( 'Betait LetsReg', 'betait-letsreg' ),   // Sidenavn
            __( 'Betait LetsReg', 'betait-letsreg' ),   // Menynavn
            'manage_options',                           // Kapabilitet
            'betait-letsreg-main',                      // Slug for hovedsiden
            array( $this, 'display_main_menu_page' ),   // Callback for innholdet
            'dashicons-admin-generic',                  // Ikon
            66                                          // Menyposisjon
        );

        // Underlenke 1: “Innstillinger”
        add_submenu_page(
            'betait-letsreg-main',                      // Slug til hovedmenyen
            __( 'Innstillinger', 'betait-letsreg' ),    // Sidetittel
            __( 'Innstillinger', 'betait-letsreg' ),    // Menytittel
            'manage_options',
            'betait-letsreg-settings',
            array( $this, 'display_settings_page' )
        );

        // Underlenke 2: “Arrangementer”
        add_submenu_page(
            'betait-letsreg-main',
            __( 'Arrangementer', 'betait-letsreg' ),
            __( 'Arrangementer', 'betait-letsreg' ),
            'manage_options',
            'betait-letsreg-events',
            array( $this, 'display_events_page' )
        );

        // Underlenke 3: “Organisator”
        add_submenu_page(
            'betait-letsreg-main',
            __( 'Organisator', 'betait-letsreg' ),
            __( 'Organisator', 'betait-letsreg' ),
            'manage_options',
            'betait-letsreg-organizer',
            array( $this, 'display_organizer_page' )
        );
    }

    /**
     * Vises når man klikker toppnivå-menyen “Betait LetsReg” (slug: betait-letsreg-main).
     *
     * @since 1.0.0
     * @return void
     */
    public function display_main_menu_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Velkommen til Betait LetsReg', 'betait-letsreg' ); ?></h1>
            <p><?php esc_html_e( 'Velg en av undermenyene for å administrere innstillinger eller arrangementer.', 'betait-letsreg' ); ?></p>
        </div>
        <?php
    }

    /**
     * Callback for undermeny “Innstillinger”.
     *
     * Håndterer POST-kall (lagring av settings, henting av token, henting av arrangører, reset til default).
     *
     * @since 1.0.0
     * @return void
     */
    public function display_settings_page() {

        // 1) Håndter reset til standardinnstillinger
        if ( isset($_POST['betait_letsreg_reset_defaults']) && check_admin_referer('betait_letsreg_reset_defaults_action', 'betait_letsreg_reset_defaults_nonce') ) {
            $this->reset_to_default();
        }

        // 2) Håndter lagring av generelle innstillinger
        if ( isset($_POST['betait_letsreg_save_settings']) && check_admin_referer('betait_letsreg_settings_save', 'betait_letsreg_nonce') ) {
            $this->save_settings();
        }

        // 3) Håndter “Hent Access Token”-knapp
        if ( isset($_POST['betait_letsreg_fetch_token']) && check_admin_referer('betait_letsreg_settings_save', 'betait_letsreg_nonce') ) {
            $this->fetch_access_token();
        }

        // 4) Håndter “Hent arrangører”-knapp
        if ( isset($_POST['betait_letsreg_fetch_organizers']) && check_admin_referer('betait_letsreg_settings_save', 'betait_letsreg_nonce') ) {
            $this->fetch_organizers();
        }

        // Hent dine opsjoner i admin-klassen
        $base_url_value = get_option('betait_letsreg_base_url', 'https://integrate.deltager.no');
        $endpoints      = get_option('betait_letsreg_endpoints', array());

        // Dersom endpoints er tomt, bruk default
        if ( empty($endpoints) ) {
            $endpoints = $this->get_default_endpoints();
        }

        // Inkluder partial-fil for innholdet (skjemaet)
        include_once plugin_dir_path( __FILE__ ) . 'partials/betait-letsreg-admin-display.php';
    }

    /**
     * Callback for undermeny “Organisator”.
     *
     * @since 1.0.0
     * @return void
     */
    public function display_organizer_page() {

        // 1) Håndter oppdatering av organisasjonsdata
        if ( isset($_POST['betait_letsreg_update_organizer']) && check_admin_referer('betait_letsreg_update_organizer_action', 'betait_letsreg_update_organizer_nonce') ) {
            $this->update_organizer();
        }

        // 2) Hent informasjon om den primære organisasjonen
        $organizer_data = $this->get_primary_organizer();

        // 3) Inkluder partial-fil for innholdet (skjemaet)
        include_once plugin_dir_path( __FILE__ ) . 'partials/betait-letsreg-organizer-display.php';
    }

    /**
     * Hent default-endpoints fra includes/class-betait-letsreg-endpoints.php
     *
     * @return array Default-endpoints eller tom array hvis filen ikke finnes.
     */
    /**
 * Hent default-endpoints fra includes/class-betait-letsreg-endpoints.php
 *
 * @return array Default-endpoints eller tom array hvis filen ikke finnes eller ikke definerer $default_endpoints korrekt.
 */
private function get_default_endpoints() {
    // Filen ligger under hoved-plugin-katalogen -> includes -> class-betait-letsreg-endpoints.php

    // 1) Finn pluginens rotkatalog (én opp fra /admin/)
    $plugin_root = dirname(__DIR__); // Eks: /home/.../wp-content/plugins/betait-letsreg-integration

    // 2) Bygg stien videre til includes/
    $path = $plugin_root . '/includes/class-betait-letsreg-endpoints.php';

    // 3) Sjekk om filen eksisterer
    if ( file_exists( $path ) ) {
        require_once $path;
        $this->debug_log("Inkluderte endpoints-fil: $path");

        // 4) Sjekk om $default_endpoints er definert og er en array
        if ( isset($default_endpoints) && is_array($default_endpoints) ) {
            $this->debug_log("Default endpoints lastet inn korrekt.");
            return $default_endpoints;
        } else {
            error_log('[Betait_Letsreg_Debug] $default_endpoints er ikke definert eller ikke en array i ' . $path);
            return array(); // Returner tom array for å unngå feil i foreach
        }
    } else {
        // Filen finnes ikke - returner en tom array eller en fallback-liste
        error_log('[Betait_Letsreg_Debug] Kunne ikke finne filen: ' . $path);
        return array();
    }
}

    /**
     * Håndter lagring av generelle innstillinger
     */
    private function save_settings() {

        // Lese inn alt fra $_POST som før
        $username   = sanitize_text_field( $_POST['betait_letsreg_username'] ?? '' );
        $password   = sanitize_text_field( $_POST['betait_letsreg_password'] ?? '' );
        $affid      = sanitize_text_field( $_POST['betait_letsreg_affid']    ?? '1' );
        $token      = sanitize_textarea_field( $_POST['betait_letsreg_access_token'] ?? '' );
        $debug      = isset($_POST['betait_letsreg_debug']) ? true : false;
        $primaryOrg = sanitize_text_field( $_POST['betait_letsreg_primary_org'] ?? '' );

        // Avanserte felt
        $base_url   = esc_url_raw( $_POST['betait_letsreg_base_url'] ?? '' );

        // Hent hele fallback-listen fra filen du har laget
        $default_endpoints = $this->get_default_endpoints();

        // Hent brukersendte endepunkter
        $posted_endpoints = $_POST['betait_letsreg_endpoints'] ?? array();

        // Kjør en rens/validering + fallback til default_endpoints
        $clean_endpoints = array();
        if ( empty($posted_endpoints) ) {
			$clean_endpoints = $default_endpoints;
		} else {
			foreach ($posted_endpoints as $ep) {	
                $slug   = sanitize_key($ep['slug'] ?? '');
                $method = in_array($ep['method'], array('GET','POST','PUT','DELETE'), true) 
                          ? $ep['method'] 
                          : 'GET';
                $url    = esc_url_raw($ep['url'] ?? '');

                if ( empty($slug) ) {
                    continue; // skipper hvis ingen slug
                }
                if ( empty($url) ) {
                    // Finn fallback-URL fra default-liste for denne slug
                    foreach ($default_endpoints as $def) {
                        if ($def['slug'] === $slug) {
                            $url = $def['url'];
                            break;
                        }
                    }
                }

                $clean_endpoints[] = array(
                    'slug'   => $slug,
                    'method' => $method,
                    'url'    => $url
                );
            }
        }

        // Om base_url er tomt, sett fallback = "https://integrate.deltager.no"
        if ( empty($base_url) ) {
            $base_url = 'https://integrate.deltager.no';
        }

        // Lagre de andre enkle felt
        update_option('betait_letsreg_username', $username);
        update_option('betait_letsreg_password', $password);
        update_option('betait_letsreg_affid',    $affid);
        update_option('betait_letsreg_access_token', $token);
        update_option('betait_letsreg_primary_org',  $primaryOrg);
        update_option('betait_letsreg_debug',    $debug);

        update_option('betait_letsreg_base_url', $base_url);

        // Lagre endepunktene
        update_option('betait_letsreg_endpoints', $clean_endpoints);

        // Slett andre spesifikke URL-opsjoner hvis du har dem
        // delete_option('betait_letsreg_token_url');
        // delete_option('betait_letsreg_events_url');

        // Vis suksessmelding i admin
        add_action('admin_notices', function() {
            ?>
            <div class="notice notice-success is-dismissible">
                <p><?php esc_html_e('Innstillingene er oppdatert.', 'betait-letsreg'); ?></p>
            </div>
            <?php
        });
    }

    /**
     * Tilbakestill alle avanserte innstillinger til standardverdier.
     */
    private function reset_to_default() {
        // Hent default-endpoints
        $default_endpoints = $this->get_default_endpoints();

        if ( ! empty($default_endpoints) ) {
            update_option('betait_letsreg_endpoints', $default_endpoints);

            // Hvis du ønsker å tilbakestille base_url også:
            update_option('betait_letsreg_base_url', 'https://integrate.deltager.no');

            // Du kan også tilbakestille andre avanserte opsjoner hvis nødvendig

            add_action('admin_notices', function() {
                ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php esc_html_e('Avanserte innstillinger er tilbakestilt til standard.', 'betait-letsreg'); ?></p>
                </div>
                <?php
            });
        } else {
            $this->show_error_notice( __('Kunne ikke finne standardendepunkter for tilbakestilling.', 'betait-letsreg') );
        }
    }

    /**
     * Hent informasjon om den primære organisasjonen.
     *
     * @return array|null Organisasjonsdata eller null hvis ikke funnet.
     */
    /*private function get_primary_organizer() {
        // Hent den primære organisasjonens ID fra innstillingene
        $primary_org_id = get_option('betait_letsreg_primary_org', '');

        if ( empty($primary_org_id ) ) {
            $this->show_error_notice( __('Ingen primær organisasjon valgt. Vennligst velg en organisasjon i innstillingene.', 'betait-letsreg') );
            return null;
        }

        // Finn endepunkt for slug="organizer_detail"
        $org_detail_ep = $this->find_endpoint_by_slug('organizer_detail');
        if ( !$org_detail_ep ) {
            $this->show_error_notice( __('Fant ikke endepunkt for "organizer_detail".', 'betait-letsreg') );
            return null;
        }

        $url = str_replace('{organizerId}', $primary_org_id, $org_detail_ep['url']);
        $method = strtoupper( $org_detail_ep['method'] );

        // Forventer GET
        if ( $method !== 'GET' ) {
            $this->debug_log("ADVARSEL: Endepunkt organizer_detail har method=$method. Bør være GET.");
        }

        // Hent access token for autentisering
        $access_token = get_option('betait_letsreg_access_token', '');
        if ( empty($access_token) ) {
            $this->show_error_notice( __('Ingen access_token tilgjengelig. Hent token først.', 'betait-letsreg') );
            return null;
        }

        // Gjør GET-kall til organizer_detail endepunktet
        $response = wp_remote_get($url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token
            )
        ));

        if ( is_wp_error($response) ) {
            $this->show_error_notice( __('Feil ved nettverkskall for å hente organisasjonsdetaljer: ', 'betait-letsreg') . $response->get_error_message() );
            return null;
        }

        $code = wp_remote_retrieve_response_code($response);
        $body_str = wp_remote_retrieve_body($response);

        if ( $code !== 200 ) {
            $this->show_error_notice( sprintf(__('Henting av organisasjonsdetaljer feilet. HTTP-kode: %d', 'betait-letsreg'), $code) );
            return null;
        }

        $data = json_decode($body_str, true); // Som assosiativ array

        if ( json_last_error() !== JSON_ERROR_NONE ) {
            $this->show_error_notice( __('Feil ved dekoding av JSON-responsen for organisasjonsdetaljer.', 'betait-letsreg') );
            return null;
        }

        return $data;
    }*/

    /**
     * Oppdater organisasjonsinformasjon via API.
     *
     * @return void
     */
	private function update_organizer() {
		// Hent de oppdaterte feltene fra $_POST
		$org_name = sanitize_text_field( $_POST['betait_letsreg_org_name'] ?? '' );
		$description = sanitize_textarea_field( $_POST['betait_letsreg_org_description'] ?? '' );
		$org_number = sanitize_text_field( $_POST['betait_letsreg_org_number'] ?? '' );
	
		// Kontaktperson
		$contact_person = array(
			'name' => sanitize_text_field( $_POST['betait_letsreg_contact_name'] ?? '' ),
			'telephone' => sanitize_text_field( $_POST['betait_letsreg_contact_phone'] ?? '' ),
			'mobile' => sanitize_text_field( $_POST['betait_letsreg_contact_mobile'] ?? '' ),
			'email' => sanitize_email( $_POST['betait_letsreg_contact_email'] ?? '' ),
		);
	
		// Adresse
		$address = array(
			'address1' => sanitize_text_field( $_POST['betait_letsreg_address1'] ?? '' ),
			'address2' => sanitize_text_field( $_POST['betait_letsreg_address2'] ?? '' ),
			'postCode' => sanitize_text_field( $_POST['betait_letsreg_postcode'] ?? '' ),
			'city' => sanitize_text_field( $_POST['betait_letsreg_city'] ?? '' ),
		);
	
		// Bankkontoer
		$bank_accounts = array();
		if ( isset($_POST['betait_letsreg_bank_accounts']) && is_array($_POST['betait_letsreg_bank_accounts']) ) {
			foreach ( $_POST['betait_letsreg_bank_accounts'] as $bank ) {
				$bank_accounts[] = array(
					'id' => absint( $bank['id'] ?? 0 ),
					'organizerId' => absint( $bank['organizerId'] ?? 0 ),
					'accountNumber' => sanitize_text_field( $bank['account_number'] ?? '' ),
					'sortCode' => sanitize_text_field( $bank['sort_code'] ?? '' ),
					'alias' => sanitize_text_field( $bank['alias'] ?? '' ),
					'isDefault' => isset($bank['is_default']) ? true : false,
				);
			}
		}
	
		// Hent Primary Org ID
		$primary_org_id = get_option('betait_letsreg_primary_org', '');
		$this->debug_log("Primary Org ID: " . $primary_org_id);
	
		if ( empty($primary_org_id ) ) {
			$this->show_error_notice( __('Ingen primær organisasjon valgt. Vennligst velg en organisasjon i innstillingene.', 'betait-letsreg') );
			return;
		}
	
		// Bygg opp dataarrayen som skal sendes til API-et
		$data = array(
			'id' => $primary_org_id, // Legg til ID her
			'name' => $org_name,
			'description' => $description,
			'organisationNumber' => $org_number,
			'contactPerson' => $contact_person,
			'address' => $address,
			'bankAccounts' => $bank_accounts,
		);
	
		// Debug: Logg data som skal sendes
		$this->debug_log("API Request Body: " . wp_json_encode($data));
	
		// Finn endepunkt for "update_organizer"
		$update_ep = $this->find_endpoint_by_slug('update_organizer');
		if ( !$update_ep ) {
			$this->show_error_notice( __('Fant ikke endepunkt for "update_organizer".', 'betait-letsreg') );
			return;
		}
	
		$url = $update_ep['url'];
		$method = strtoupper( $update_ep['method'] );
	
		if ( $method !== 'PUT' ) {
			$this->debug_log("ADVARSEL: Endepunkt update_organizer har method=$method. Bør være PUT.");
		}
	
		// Hent access token for autentisering
		$access_token = get_option('betait_letsreg_access_token', '');
		if ( empty($access_token) ) {
			$this->show_error_notice( __('Ingen access_token tilgjengelig. Hent token først.', 'betait-letsreg') );
			return;
		}
	
		// Gjør PUT-kall til update_organizer endepunktet
		$response = wp_remote_request( $url, array(
			'method' => 'PUT',
			'headers' => array(
				'Authorization' => 'Bearer ' . $access_token,
				'Content-Type'  => 'application/json',
			),
			'body' => wp_json_encode( $data ),
		) );
	
		if ( is_wp_error( $response ) ) {
			$this->show_error_notice( __('Feil ved nettverkskall for å oppdatere organisasjonsdetaljer: ', 'betait-letsreg') . $response->get_error_message() );
			$this->debug_log( 'WP Error: ' . $response->get_error_message() );
			return;
		}
	
		$code = wp_remote_retrieve_response_code( $response );
		$body_str = wp_remote_retrieve_body( $response );
	
		$this->debug_log("API Response Code: $code");
		$this->debug_log("API Response Body: " . $body_str);
	
		if ( $code !== 200 && $code !== 204 ) { // Avhengig av API-et kan suksesskode være 200 eller 204
			$this->show_error_notice( sprintf( __('Oppdatering av organisasjonsdetaljer feilet. HTTP-kode: %d', 'betait-letsreg'), $code ) );
			return;
		}
	
		// Anta at API-et returnerer oppdatert data
		$data_response = json_decode( $body_str, true );
	
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			$this->show_error_notice( __('Feil ved dekoding av JSON-responsen for oppdatering av organisasjonsdetaljer.', 'betait-letsreg') );
			return;
		}
	
		// Lagre eventuelle oppdaterte data i lokal lagring hvis nødvendig
		// For eksempel kan du oppdatere primær organisasjon hvis API-et returnerer endringer
	
		// Vis suksessmelding
		add_action('admin_notices', function() {
			?>
			<div class="notice notice-success is-dismissible">
				<p><?php esc_html_e('Organisatorinformasjonen er oppdatert.', 'betait-letsreg'); ?></p>
			</div>
			<?php
		});
	
		$this->debug_log( 'Organisatorinformasjon oppdatert via API.' );
	}
	
	
	

    /**
     * Finn et endepunkt basert på slug.
     *
     * @param string $slug Slug for endepunktet.
     * @return array|null Endepunktet som en array eller null hvis ikke funnet.
     */
    private function find_endpoint_by_slug($slug) {
        $endpoints = get_option('betait_letsreg_endpoints', array());

        // Hvis tom, bruk fallback-liste
        if ( empty($endpoints) ) {
            $endpoints = $this->get_default_endpoints();
        }

        foreach ( $endpoints as $ep ) {
            if ( isset($ep['slug']) && $ep['slug'] === $slug ) {
                return $ep; // Returnerer [ 'slug'=>'token', 'method'=>'POST', 'url'=>'...' ]
            }
        }
        return null;
    }

    /**
     * Hent Access Token basert på brukernavn, passord, affid, token_url
     */
    private function fetch_access_token() {
        $this->debug_log('Starter fetch_access_token...');

        $username = get_option('betait_letsreg_username', '');
        $password = get_option('betait_letsreg_password', '');
        $affid    = get_option('betait_letsreg_affid', '1');

        $this->debug_log("Brukernavn=$username, Passord=*****, AffID=$affid");

        if ( empty($username) || empty($password) ) {
            $this->debug_log('Mangler brukernavn/passord for tokenkall -> avbryter.');
            $this->show_error_notice( __('Mangler brukernavn eller passord for å hente token.', 'betait-letsreg') );
            return;
        }

        // Finn endepunkt for slug="token"
        $token_ep = $this->find_endpoint_by_slug('token');
        if (!$token_ep) {
            // fallback
            $this->debug_log('Fant ikke slug=token i endpoints, fallback=legacyapi');
            $url = 'https://legacyapi.deltager.no/token';
            $method = 'POST';
        } else {
            $url    = $token_ep['url'];
            $method = strtoupper( $token_ep['method'] );
        }

        // Forventer at method=POST
        if ( $method !== 'POST' ) {
            $this->debug_log("ADVARSEL: Endepunkt token har method=$method. Bør være POST.");
        }

        $this->debug_log("Fetcher token via URL=$url, method=$method");

        $body = array(
            'grant_type' => 'password',
            'username'   => $username,
            'password'   => $password,
            'affid'      => $affid,
        );
        $this->debug_log("POST-data: " . print_r($body, true));

        $response = wp_remote_post($url, array(
            'headers' => array('Content-Type'=>'application/x-www-form-urlencoded'),
            'body'    => $body
        ));

        if ( is_wp_error($response) ) {
            $this->debug_log('WP Error i token-kall: ' . $response->get_error_message());
            $this->show_error_notice( __('Feil ved nettverkskall for token: ', 'betait-letsreg') . $response->get_error_message() );
            return;
        }

        $code = wp_remote_retrieve_response_code($response);
        $body_str = wp_remote_retrieve_body($response);
        $this->debug_log("HTTP-kode=$code, Body=$body_str");

        if ( $code !== 200 ) {
            $this->show_error_notice( sprintf(__('Henting av token feilet. HTTP-kode: %d', 'betait-letsreg'), $code) );
            return;
        }

        $data = json_decode($body_str);

        if ( isset($data->access_token) ) {
            update_option('betait_letsreg_access_token', $data->access_token);

            add_action('admin_notices', function() {
                ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php esc_html_e('Access Token hentet og lagret.', 'betait-letsreg'); ?></p>
                </div>
                <?php
            });

            $this->debug_log('Token lagret OK: ' . $data->access_token);

        } else {
            $this->debug_log('Responsen manglet access_token: ' . $body_str);
            $this->show_error_notice( __('Responsen manglet access_token.', 'betait-letsreg') );
        }
    }

    /**
     * Hent arrangører-liste basert på gjeldende access_token
     */
    private function fetch_organizers() {

        $access_token = get_option('betait_letsreg_access_token', '');

        if ( empty($access_token) ) {
            $this->show_error_notice( __('Ingen access_token tilgjengelig. Hent token først.', 'betait-letsreg') );
            return;
        }

        // Finn endepunkt for slug="organizers_list"
        $org_ep = $this->find_endpoint_by_slug('organizers_list');
        if (!$org_ep) {
            $this->debug_log('Fant ikke slug=organizers_list i endpoints, fallback=integrate');
            $url = 'https://integrate.deltager.no/organizers';
            $method = 'GET';
        } else {
            $url = $org_ep['url'];
            $method = strtoupper( $org_ep['method'] );
        }
        $this->debug_log("Henter arrangører fra $url (method=$method)");

        // Forventer GET
        if ( $method !== 'GET' ) {
            $this->debug_log("ADVARSEL: Endepunkt organizers_list har method=$method. Bør være GET.");
        }

        $response = wp_remote_get($url, array(
            'headers' => array('Authorization' => 'Bearer ' . $access_token),
        ));

        if ( is_wp_error($response) ) {
            $this->show_error_notice( __('Feil ved nettverkskall for arrangører: ', 'betait-letsreg') . $response->get_error_message() );
            return;
        }

        $code = wp_remote_retrieve_response_code($response);
        if ( $code !== 200 ) {
            $this->show_error_notice( sprintf(__('Henting av arrangører feilet. HTTP-kode: %d', 'betait-letsreg'), $code) );
            return;
        }

        $body_str = wp_remote_retrieve_body($response);
        $data = json_decode($body_str);

        if ( is_array($data) ) {
            // For enkelhet, lagrer hele responsen
            update_option('betait_letsreg_organizers_list', $data);

            add_action('admin_notices', function() use ($data) {
                $count = count($data);
                ?>
                <div class="notice notice-success is-dismissible">
                    <p>
                        <?php printf(
                            esc_html__('Fant %d arrangører. Listen er lagret.', 'betait-letsreg'),
                            $count
                        ); ?>
                    </p>
                </div>
                <?php
            });
        } else {
            $this->show_error_notice( __('Responsen for arrangører var ikke en liste/array.', 'betait-letsreg') );
        }
    }

    /**
     * Hent informasjon om den primære organisasjonen.
     *
     * @return array|null Organisasjonsdata eller null hvis ikke funnet.
     */
    private function get_primary_organizer() {
		// Hent den primære organisasjonens ID fra innstillingene
		$primary_org_id = get_option('betait_letsreg_primary_org', '');
		$this->debug_log("Primary Org ID: " . $primary_org_id);
	
		if ( empty($primary_org_id ) ) {
			$this->show_error_notice( __('Ingen primær organisasjon valgt. Vennligst velg en organisasjon i innstillingene.', 'betait-letsreg') );
			return null;
		}
	
		// Finn endepunkt for slug="organizer_detail"
		$org_detail_ep = $this->find_endpoint_by_slug('organizer_detail');
		if ( !$org_detail_ep ) {
			$this->show_error_notice( __('Fant ikke endepunkt for "organizer_detail".', 'betait-letsreg') );
			return null;
		}
	
		$url = str_replace('{organizerId}', $primary_org_id, $org_detail_ep['url']);
		$method = strtoupper( $org_detail_ep['method'] );
	
		$this->debug_log("API URL: $url, Method: $method");
	
		// Forventer GET
		if ( $method !== 'GET' ) {
			$this->debug_log("ADVARSEL: Endepunkt organizer_detail har method=$method. Bør være GET.");
		}
	
		// Hent access token for autentisering
		$access_token = get_option('betait_letsreg_access_token', '');
		$this->debug_log("Access Token: " . substr($access_token, 0, 10) . '...'); // Vis kun første 10 tegn
	
		if ( empty($access_token) ) {
			$this->show_error_notice( __('Ingen access_token tilgjengelig. Hent token først.', 'betait-letsreg') );
			return null;
		}
	
		// Gjør GET-kall til organizer_detail endepunktet
		$response = wp_remote_get($url, array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $access_token
			)
		));
	
		if ( is_wp_error($response) ) {
			$this->debug_log('WP Error: ' . $response->get_error_message());
			$this->show_error_notice( __('Feil ved nettverkskall for å hente organisasjonsdetaljer: ', 'betait-letsreg') . $response->get_error_message() );
			return null;
		}
	
		$code = wp_remote_retrieve_response_code($response);
		$body_str = wp_remote_retrieve_body($response);
		$this->debug_log("API Response Code: $code");
		$this->debug_log("API Response Body: " . $body_str);
	
		if ( $code !== 200 ) {
			$this->show_error_notice( sprintf(__('Henting av organisasjonsdetaljer feilet. HTTP-kode: %d', 'betait-letsreg'), $code) );
			return null;
		}
	
		$data = json_decode($body_str, true); // Som assosiativ array
		$this->debug_log("Decoded API Data: " . print_r($data, true));
	
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			$this->show_error_notice( __('Feil ved dekoding av JSON-responsen for organisasjonsdetaljer.', 'betait-letsreg') );
			return null;
		}
	
		return $data;
	}
	

    /**
     * Oppdater organisasjonsinformasjon via API.
     *
     * @return void
     */
    /*private function update_organizer() {
        // Hent de oppdaterte feltene fra $_POST
        $org_name = sanitize_text_field( $_POST['betait_letsreg_org_name'] ?? '' );
        $description = sanitize_textarea_field( $_POST['betait_letsreg_org_description'] ?? '' );
        $org_number = sanitize_text_field( $_POST['betait_letsreg_org_number'] ?? '' );

        // Kontaktperson
        $contact_person = array(
            'name' => sanitize_text_field( $_POST['betait_letsreg_contact_name'] ?? '' ),
            'telephone' => sanitize_text_field( $_POST['betait_letsreg_contact_phone'] ?? '' ),
            'mobile' => sanitize_text_field( $_POST['betait_letsreg_contact_mobile'] ?? '' ),
            'email' => sanitize_email( $_POST['betait_letsreg_contact_email'] ?? '' ),
        );

        // Adresse
        $address = array(
            'address1' => sanitize_text_field( $_POST['betait_letsreg_address1'] ?? '' ),
            'address2' => sanitize_text_field( $_POST['betait_letsreg_address2'] ?? '' ),
            'postCode' => sanitize_text_field( $_POST['betait_letsreg_postcode'] ?? '' ),
            'city' => sanitize_text_field( $_POST['betait_letsreg_city'] ?? '' ),
        );

        // Bankkontoer
        $bank_accounts = array();
        if ( isset($_POST['betait_letsreg_bank_accounts']) && is_array($_POST['betait_letsreg_bank_accounts']) ) {
            foreach ( $_POST['betait_letsreg_bank_accounts'] as $bank ) {
                $bank_accounts[] = array(
                    'id' => absint( $bank['id'] ?? 0 ),
                    'organizerId' => absint( $bank['organizerId'] ?? 0 ),
                    'accountNumber' => sanitize_text_field( $bank['account_number'] ?? '' ),
                    'sortCode' => sanitize_text_field( $bank['sort_code'] ?? '' ),
                    'alias' => sanitize_text_field( $bank['alias'] ?? '' ),
                    'isDefault' => isset($bank['is_default']) ? true : false,
                );
            }
        }

        // Bygg opp dataarrayen som skal sendes til API-et
        $data = array(
            'name' => $org_name,
            'description' => $description,
            'organisationNumber' => $org_number,
            'contactPerson' => $contact_person,
            'address' => $address,
            'bankAccounts' => $bank_accounts,
        );

        // Finn endepunkt for "update_organizer"
        $update_ep = $this->find_endpoint_by_slug('update_organizer');
        if ( !$update_ep ) {
            $this->show_error_notice( __('Fant ikke endepunkt for "update_organizer".', 'betait-letsreg') );
            return;
        }

        $url = $update_ep['url'];
        $method = strtoupper( $update_ep['method'] );

        if ( $method !== 'PUT' ) {
            $this->debug_log("ADVARSEL: Endepunkt update_organizer har method=$method. Bør være PUT.");
        }

        // Hent access token for autentisering
        $access_token = get_option('betait_letsreg_access_token', '');
        if ( empty($access_token) ) {
            $this->show_error_notice( __('Ingen access_token tilgjengelig. Hent token først.', 'betait-letsreg') );
            return;
        }

        // Gjør PUT-kall til update_organizer endepunktet
        $response = wp_remote_request( $url, array(
            'method' => 'PUT',
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type'  => 'application/json',
            ),
            'body' => wp_json_encode( $data ),
        ) );

        if ( is_wp_error( $response ) ) {
            $this->show_error_notice( __('Feil ved nettverkskall for å oppdatere organisasjonsdetaljer: ', 'betait-letsreg') . $response->get_error_message() );
            return;
        }

        $code = wp_remote_retrieve_response_code( $response );
        $body_str = wp_remote_retrieve_body( $response );

        if ( $code !== 200 && $code !== 204 ) { // Avhengig av API-et kan suksesskode være 200 eller 204
            $this->show_error_notice( sprintf( __('Oppdatering av organisasjonsdetaljer feilet. HTTP-kode: %d', 'betait-letsreg'), $code ) );
            return;
        }

        // Anta at API-et returnerer oppdatert data
        $data_response = json_decode( $body_str, true );

        if ( json_last_error() !== JSON_ERROR_NONE ) {
            $this->show_error_notice( __('Feil ved dekoding av JSON-responsen for oppdatering av organisasjonsdetaljer.', 'betait-letsreg') );
            return;
        }

        // Lagre eventuelle oppdaterte data i lokal lagring hvis nødvendig
        // For eksempel kan du oppdatere primær organisasjon hvis API-et returnerer endringer

        // Vis suksessmelding
        add_action('admin_notices', function() {
            ?>
            <div class="notice notice-success is-dismissible">
                <p><?php esc_html_e('Organisatorinformasjonen er oppdatert.', 'betait-letsreg'); ?></p>
            </div>
            <?php
        });

        $this->debug_log( 'Organisatorinformasjon oppdatert via API.' );
    }*/

    /**
     * Finn et endepunkt basert på slug.
     *
     * @param string $slug Slug for endepunktet.
     * @return array|null Endepunktet som en array eller null hvis ikke funnet.
     */
    /*private function find_endpoint_by_slug($slug) {
        $endpoints = get_option('betait_letsreg_endpoints', array());

        // Hvis tom, bruk fallback-liste
        if ( empty($endpoints) ) {
            $endpoints = $this->get_default_endpoints();
        }

        foreach ( $endpoints as $ep ) {
            if ( isset($ep['slug']) && $ep['slug'] === $slug ) {
                return $ep; // Returnerer [ 'slug'=>'token', 'method'=>'POST', 'url'=>'...' ]
            }
        }
        return null;
    }*/

    /**
     * Hent Access Token basert på brukernavn, passord, affid, token_url
     */
    
	
	
/*	 private function fetch_access_token() {
        $this->debug_log('Starter fetch_access_token...');

        $username = get_option('betait_letsreg_username', '');
        $password = get_option('betait_letsreg_password', '');
        $affid    = get_option('betait_letsreg_affid', '1');

        $this->debug_log("Brukernavn=$username, Passord=*****, AffID=$affid");

        if ( empty($username) || empty($password) ) {
            $this->debug_log('Mangler brukernavn/passord for tokenkall -> avbryter.');
            $this->show_error_notice( __('Mangler brukernavn eller passord for å hente token.', 'betait-letsreg') );
            return;
        }

        // Finn endepunkt for slug="token"
        $token_ep = $this->find_endpoint_by_slug('token');
        if (!$token_ep) {
            // fallback
            $this->debug_log('Fant ikke slug=token i endpoints, fallback=legacyapi');
            $url = 'https://legacyapi.deltager.no/token';
            $method = 'POST';
        } else {
            $url    = $token_ep['url'];
            $method = strtoupper( $token_ep['method'] );
        }

        // Forventer at method=POST
        if ( $method !== 'POST' ) {
            $this->debug_log("ADVARSEL: Endepunkt token har method=$method. Bør være POST.");
        }

        $this->debug_log("Fetcher token via URL=$url, method=$method");

        $body = array(
            'grant_type' => 'password',
            'username'   => $username,
            'password'   => $password,
            'affid'      => $affid,
        );
        $this->debug_log("POST-data: " . print_r($body, true));

        $response = wp_remote_post($url, array(
            'headers' => array('Content-Type'=>'application/x-www-form-urlencoded'),
            'body'    => $body
        ));

        if ( is_wp_error($response) ) {
            $this->debug_log('WP Error i token-kall: ' . $response->get_error_message());
            $this->show_error_notice( __('Feil ved nettverkskall for token: ', 'betait-letsreg') . $response->get_error_message() );
            return;
        }

        $code = wp_remote_retrieve_response_code($response);
        $body_str = wp_remote_retrieve_body($response);
        $this->debug_log("HTTP-kode=$code, Body=$body_str");

        if ( $code !== 200 ) {
            $this->show_error_notice( sprintf(__('Henting av token feilet. HTTP-kode: %d', 'betait-letsreg'), $code) );
            return;
        }

        $data = json_decode($body_str);

        if ( isset($data->access_token) ) {
            update_option('betait_letsreg_access_token', $data->access_token);

            add_action('admin_notices', function() {
                ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php esc_html_e('Access Token hentet og lagret.', 'betait-letsreg'); ?></p>
                </div>
                <?php
            });

            $this->debug_log('Token lagret OK: ' . $data->access_token);

        } else {
            $this->debug_log('Responsen manglet access_token: ' . $body_str);
            $this->show_error_notice( __('Responsen manglet access_token.', 'betait-letsreg') );
        }
    } */

    /**
     * Hent arrangører-liste basert på gjeldende access_token
     */
   /* private function fetch_organizers() {

        $access_token = get_option('betait_letsreg_access_token', '');

        if ( empty($access_token) ) {
            $this->show_error_notice( __('Ingen access_token tilgjengelig. Hent token først.', 'betait-letsreg') );
            return;
        }

        // Finn endepunkt for slug="organizers_list"
        $org_ep = $this->find_endpoint_by_slug('organizers_list');
        if (!$org_ep) {
            $this->debug_log('Fant ikke slug=organizers_list i endpoints, fallback=integrate');
            $url = 'https://integrate.deltager.no/organizers';
            $method = 'GET';
        } else {
            $url = $org_ep['url'];
            $method = strtoupper( $org_ep['method'] );
        }
        $this->debug_log("Henter arrangører fra $url (method=$method)");

        // Forventer GET
        if ( $method !== 'GET' ) {
            $this->debug_log("ADVARSEL: Endepunkt organizers_list har method=$method. Bør være GET.");
        }

        $response = wp_remote_get($url, array(
            'headers' => array('Authorization' => 'Bearer ' . $access_token),
        ));

        if ( is_wp_error($response) ) {
            $this->show_error_notice( __('Feil ved nettverkskall for arrangører: ', 'betait-letsreg') . $response->get_error_message() );
            return;
        }

        $code = wp_remote_retrieve_response_code($response);
        if ( $code !== 200 ) {
            $this->show_error_notice( sprintf(__('Henting av arrangører feilet. HTTP-kode: %d', 'betait-letsreg'), $code) );
            return;
        }

        $body_str = wp_remote_retrieve_body($response);
        $data = json_decode($body_str);

        if ( is_array($data) ) {
            // For enkelhet, lagrer hele responsen
            update_option('betait_letsreg_organizers_list', $data);

            add_action('admin_notices', function() use ($data) {
                $count = count($data);
                ?>
                <div class="notice notice-success is-dismissible">
                    <p>
                        <?php printf(
                            esc_html__('Fant %d arrangører. Listen er lagret.', 'betait-letsreg'),
                            $count
                        ); ?>
                    </p>
                </div>
                <?php
            });
        } else {
            $this->show_error_notice( __('Responsen for arrangører var ikke en liste/array.', 'betait-letsreg') );
        }
    } */

    /**
     * Oppdater organisasjonsinformasjon via API.
     *
     * @return void
     */
    /* private function update_organizer() {
        // Hent de oppdaterte feltene fra $_POST
        $org_name = sanitize_text_field( $_POST['betait_letsreg_org_name'] ?? '' );
        $description = sanitize_textarea_field( $_POST['betait_letsreg_org_description'] ?? '' );
        $org_number = sanitize_text_field( $_POST['betait_letsreg_org_number'] ?? '' );

        // Kontaktperson
        $contact_person = array(
            'name' => sanitize_text_field( $_POST['betait_letsreg_contact_name'] ?? '' ),
            'telephone' => sanitize_text_field( $_POST['betait_letsreg_contact_phone'] ?? '' ),
            'mobile' => sanitize_text_field( $_POST['betait_letsreg_contact_mobile'] ?? '' ),
            'email' => sanitize_email( $_POST['betait_letsreg_contact_email'] ?? '' ),
        );

        // Adresse
        $address = array(
            'address1' => sanitize_text_field( $_POST['betait_letsreg_address1'] ?? '' ),
            'address2' => sanitize_text_field( $_POST['betait_letsreg_address2'] ?? '' ),
            'postCode' => sanitize_text_field( $_POST['betait_letsreg_postcode'] ?? '' ),
            'city' => sanitize_text_field( $_POST['betait_letsreg_city'] ?? '' ),
        );

        // Bankkontoer
        $bank_accounts = array();
        if ( isset($_POST['betait_letsreg_bank_accounts']) && is_array($_POST['betait_letsreg_bank_accounts']) ) {
            foreach ( $_POST['betait_letsreg_bank_accounts'] as $bank ) {
                $bank_accounts[] = array(
                    'id' => absint( $bank['id'] ?? 0 ),
                    'organizerId' => absint( $bank['organizerId'] ?? 0 ),
                    'accountNumber' => sanitize_text_field( $bank['account_number'] ?? '' ),
                    'sortCode' => sanitize_text_field( $bank['sort_code'] ?? '' ),
                    'alias' => sanitize_text_field( $bank['alias'] ?? '' ),
                    'isDefault' => isset($bank['is_default']) ? true : false,
                );
            }
        }

        // Bygg opp dataarrayen som skal sendes til API-et
        $data = array(
            'name' => $org_name,
            'description' => $description,
            'organisationNumber' => $org_number,
            'contactPerson' => $contact_person,
            'address' => $address,
            'bankAccounts' => $bank_accounts,
        );

        // Finn endepunkt for "update_organizer"
        $update_ep = $this->find_endpoint_by_slug('update_organizer');
        if ( !$update_ep ) {
            $this->show_error_notice( __('Fant ikke endepunkt for "update_organizer".', 'betait-letsreg') );
            return;
        }

        $url = $update_ep['url'];
        $method = strtoupper( $update_ep['method'] );

        if ( $method !== 'PUT' ) {
            $this->debug_log("ADVARSEL: Endepunkt update_organizer har method=$method. Bør være PUT.");
        }

        // Hent access token for autentisering
        $access_token = get_option('betait_letsreg_access_token', '');
        if ( empty($access_token) ) {
            $this->show_error_notice( __('Ingen access_token tilgjengelig. Hent token først.', 'betait-letsreg') );
            return;
        }

        // Gjør PUT-kall til update_organizer endepunktet
        $response = wp_remote_request( $url, array(
            'method' => 'PUT',
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type'  => 'application/json',
            ),
            'body' => wp_json_encode( $data ),
        ) );

        if ( is_wp_error( $response ) ) {
            $this->show_error_notice( __('Feil ved nettverkskall for å oppdatere organisasjonsdetaljer: ', 'betait-letsreg') . $response->get_error_message() );
            return;
        }

        $code = wp_remote_retrieve_response_code( $response );
        $body_str = wp_remote_retrieve_body( $response );

        if ( $code !== 200 && $code !== 204 ) { // Avhengig av API-et kan suksesskode være 200 eller 204
            $this->show_error_notice( sprintf( __('Oppdatering av organisasjonsdetaljer feilet. HTTP-kode: %d', 'betait-letsreg'), $code ) );
            return;
        }

        // Anta at API-et returnerer oppdatert data
        $data_response = json_decode( $body_str, true );

        if ( json_last_error() !== JSON_ERROR_NONE ) {
            $this->show_error_notice( __('Feil ved dekoding av JSON-responsen for oppdatering av organisasjonsdetaljer.', 'betait-letsreg') );
            return;
        }

        // Lagre eventuelle oppdaterte data i lokal lagring hvis nødvendig
        // For eksempel kan du oppdatere primær organisasjon hvis API-et returnerer endringer

        // Vis suksessmelding
        add_action('admin_notices', function() {
            ?>
            <div class="notice notice-success is-dismissible">
                <p><?php esc_html_e('Organisatorinformasjonen er oppdatert.', 'betait-letsreg'); ?></p>
            </div>
            <?php
        });

        $this->debug_log( 'Organisatorinformasjon oppdatert via API.' );
    }*/

    /**
     * Viser en enkel feilmelding (rød boks) i admin
     */
    private function show_error_notice($msg) {
        add_action('admin_notices', function() use ($msg) {
            ?>
            <div class="notice notice-error is-dismissible">
                <p><?php echo esc_html($msg); ?></p>
            </div>
            <?php
        });
    }

    /**
     * Callback for undermeny “Arrangementer”.
     *
     * @since 1.0.0
     * @return void
     */
    public function display_events_page() {
        // Inkluder en egen partial for arrangement-visning
        // (Ikke definert i dette eksempelet)
        include_once plugin_dir_path( __FILE__ ) . 'partials/betait-letsreg-events-display.php';
    }

    /**
     * Skriver meldinger til debug.log hvis debug er aktivert
     */
    private function debug_log( $message ) {
        // Sjekk om debug er skrudd på
        $debug_enabled = (bool) get_option('betait_letsreg_debug', false);

        if ( ! $debug_enabled ) {
            return; // Debug er av
        }

        // WP_DEBUG_LOG må være true i wp-config.php for at logger havner i wp-content/debug.log
        if ( is_array($message) || is_object($message) ) {
            error_log( print_r($message, true) );
        } else {
            error_log( '[Betait_Letsreg_Debug] ' . $message );
        }
    }
}
