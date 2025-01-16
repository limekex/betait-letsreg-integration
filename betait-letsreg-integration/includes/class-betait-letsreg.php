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
new Betait_LetsReg();

/**
 * Registrere Custom Post Type 'event'
 *
add_action( 'init', 'betait_letsreg_register_event_cpt' );

function betait_letsreg_register_event_cpt() {
    $labels = array(
        'name'                  => _x( 'LetsReg Arrangementer', 'Post type general name', 'betait-letsreg' ),
        'singular_name'         => _x( 'LetsReg Arrangement', 'Post type singular name', 'betait-letsreg' ),
        'menu_name'             => _x( 'LetsReg Arrangementer', 'Admin Menu text', 'betait-letsreg' ),
        'name_admin_bar'        => _x( 'LetsReg Arrangement', 'Add New on Toolbar', 'betait-letsreg' ),
        'add_new'               => __( 'Legg til ny', 'betait-letsreg' ),
        'add_new_item'          => __( 'Legg til nytt arrangement', 'betait-letsreg' ),
        'new_item'              => __( 'Nytt arrangement', 'betait-letsreg' ),
        'edit_item'             => __( 'Rediger arrangement', 'betait-letsreg' ),
        'view_item'             => __( 'Se arrangement', 'betait-letsreg' ),
        'all_items'             => __( 'Alle arrangementer', 'betait-letsreg' ),
        'search_items'          => __( 'Søk arrangementer', 'betait-letsreg' ),
        'parent_item_colon'     => __( 'Foreldre arrangementer:', 'betait-letsreg' ),
        'not_found'             => __( 'Ingen arrangementer funnet.', 'betait-letsreg' ),
        'not_found_in_trash'    => __( 'Ingen arrangementer funnet i søppel.', 'betait-letsreg' ),
        'featured_image'        => _x( 'Utvalgt bilde', 'Overrides the “Featured Image” phrase for this post type. Added in 4.3', 'betait-letsreg' ),
        'set_featured_image'    => _x( 'Sett utvalgt bilde', 'Overrides the “Set featured image” phrase for this post type. Added in 4.3', 'betait-letsreg' ),
        'remove_featured_image' => _x( 'Fjern utvalgt bilde', 'Overrides the “Remove featured image” phrase for this post type. Added in 4.3', 'betait-letsreg' ),
        'use_featured_image'    => _x( 'Bruk som utvalgt bilde', 'Overrides the “Use as featured image” phrase for this post type. Added in 4.3', 'betait-letsreg' ),
        'archives'              => _x( 'Arrangement arkiver', 'The post type archive label used in nav menus. Default “Post Archives”. Added in 4.4', 'betait-letsreg' ),
        'insert_into_item'      => _x( 'Sett inn i arrangement', 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post). Added in 4.4', 'betait-letsreg' ),
        'uploaded_to_this_item' => _x( 'Lastet opp til dette arrangementet', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post). Added in 4.4', 'betait-letsreg' ),
        'filter_items_list'     => _x( 'Filtrer arrangement liste', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. Added in 4.4', 'betait-letsreg' ),
        'items_list_navigation' => _x( 'Arrangement liste navigasjon', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4', 'betait-letsreg' ),
        'items_list'            => _x( 'Arrangement liste', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. Added in 4.4', 'betait-letsreg' ),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'event' ),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 5,
        'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments' ),
    );

    register_post_type( 'event', $args );
} */

}
