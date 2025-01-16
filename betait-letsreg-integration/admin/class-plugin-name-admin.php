<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    betait_letsreg
 * @subpackage betait_letsreg/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    betait_letsreg
 * @subpackage betait_letsreg/admin
 * @author     Your Name <email@example.com>
 */
class betait_letsreg_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $betait_letsreg    The ID of this plugin.
	 */
	private $betait_letsreg;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $betait_letsreg       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $betait_letsreg, $version ) {

		$this->betait_letsreg = $betait_letsreg;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in betait_letsreg_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The betait_letsreg_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->betait_letsreg, plugin_dir_url( __FILE__ ) . 'css/betait-letsreg-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in betait_letsreg_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The betait_letsreg_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->betait_letsreg, plugin_dir_url( __FILE__ ) . 'js/betait-letsreg-admin.js', array( 'jquery' ), $this->version, false );

	}

}
