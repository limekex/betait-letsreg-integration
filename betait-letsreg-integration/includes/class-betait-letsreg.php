<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://betait.no/betaletsreg
 * @since      1.0.0
 *
 * @package    Betait_Letsreg
 * @subpackage Betait_Letsreg/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Betait_Letsreg
 * @subpackage Betait_Letsreg/includes
 * @author     Bjørn-Tore Almås <bt@betait.no>
 */


 if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class Betait_Letsreg {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Betait_Letsreg_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader; 

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $betait_letsreg    The string used to uniquely identify this plugin.
	 */
	protected $betait_letsreg;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;
	
	/**
     * AJAX Handler Instance.
     *
     * @var Betait_LetsReg_Ajax
     */
    protected $ajax;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'BETAIT_LETSREG_VERSION' ) ) {
			$this->version = BETAIT_LETSREG_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->betait_letsreg = 'betait-letsreg';
		$this->init_ajax();
		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Betait_Letsreg_Loader. Orchestrates the hooks of the plugin.
	 * - Betait_Letsreg_i18n. Defines internationalization functionality.
	 * - Betait_Letsreg_Admin. Defines all hooks for the admin area.
	 * - Betait_Letsreg_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-betait-letsreg-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-betait-letsreg-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-betait-letsreg-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-betait-letsreg-public.php';

		// This class is responsible for creating the custom post type:
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-betait-letsreg-cpt.php';
		$this->cpt_manager = new Betait_Letsreg_CPT();
    	$this->cpt_manager->register_hooks();
		$this->tax_meta = new Betait_Letsreg_Tax_Meta();

			// e.g. in class-betait-letsreg.php or similar:
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-betait-letsreg-metabox.php';
		$this->metabox = new Betait_Letsreg_Metabox();

		$this->loader = new Betait_Letsreg_Loader();

	}

 /**
     * Init AJAX handler.
     */
    private function init_ajax() {
        // Inkluder AJAX-klassen
        require_once plugin_dir_path( __FILE__ ) . 'class-betait-letsreg-ajax.php';

        // Instansier AJAX-klassen
        $this->ajax = new Betait_LetsReg_Ajax();
    }

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Betait_Letsreg_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Betait_Letsreg_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Betait_Letsreg_Admin( $this->get_betait_letsreg(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_plugin_admin_menu' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Betait_Letsreg_Public( $this->get_betait_letsreg(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_betait_letsreg() {
		return $this->betait_letsreg;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Betait_Letsreg_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}


	/* Instansier klassen
new Betait_LetsReg();*/

}
