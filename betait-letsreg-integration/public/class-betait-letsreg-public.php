<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://betait.no/betaletsreg
 * @since      1.0.0
 *
 * @package    Betait_Letsreg
 * @subpackage Betait_Letsreg/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Betait_Letsreg
 * @subpackage Betait_Letsreg/public
 * @author     Bjørn-Tore Almås <bt@betait.no>
 */
class Betait_Letsreg_Public {

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
	 * @param      string    $betait_letsreg       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $betait_letsreg, $version ) {

		$this->betait_letsreg = $betait_letsreg;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Betait_Letsreg_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Betait_Letsreg_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->betait_letsreg, plugin_dir_url( __FILE__ ) . 'css/betait-letsreg-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Betait_Letsreg_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Betait_Letsreg_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->betait_letsreg, plugin_dir_url( __FILE__ ) . 'js/betait-letsreg-public.js', array( 'jquery' ), $this->version, false );

	}

}
