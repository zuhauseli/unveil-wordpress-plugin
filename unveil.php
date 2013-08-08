<?php
/*
Plugin Name: Unveil
Description: "Lazy Load" Ladetechnik für Bilder in WordPress auf Basis des leichtgewichtigen jQuery-Plugins <a href="https://github.com/luis-almeida/unveil">Unveil.js</a>. Smarte Lösung für bessere Blog-Performance.
Author: Sergej M&uuml;ller
Author URI: http://wpcoder.de
Plugin URI: https://github.com/sergejmueller/unveil-wordpress-plugin
Version: 0.0.1
*/


/* Quit */
defined('ABSPATH') OR exit;


/* Fire */
add_action(
	'plugins_loaded',
	array(
		'Unveil',
		'instance'
	)
);


/* Unveil class */
final class Unveil {


	/**
	* Class instance
	*
	* @since   0.0.1
	* @change  0.0.1
	*/

	public static function instance()
	{
		new self();
	}


	/**
	* Class constructor
	*
	* @since   0.0.1
	* @change  0.0.1
	*/

	public function __construct()
  	{
  		/* Go home */
		if ( is_admin() OR (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) OR (defined('DOING_CRON') && DOING_CRON) OR (defined('DOING_AJAX') && DOING_AJAX) OR (defined('XMLRPC_REQUEST') && XMLRPC_REQUEST) ) {
			return;
		}

		/* Hooks */
		add_filter(
			'the_content',
			array(
				__CLASS__,
				'prepare_images'
			)
		);
		add_action(
			'wp_enqueue_scripts',
			array(
				__CLASS__,
				'print_scripts'
			)
		);
	}


	/**
	* Prepare content images for unveil usage
	*
	* @since   0.0.1
	* @change  0.0.1
	*
	* @param   string  $content  Original post content
	* @param   string  $content  Modified post content
	*/

	public static function prepare_images($content) {
		/* CSS class LIKE %wp-image-% */
		if ( strpos($content, 'wp-image-') === false ) {
			return $content;
		}

		/* Empty gif */
		$null = 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==';

		/* Replace images */
		return preg_replace(
			array(
				'#<img(.+?)(wp-image-.+?)src=["\'](.+?)["\'](.*?)/>#',
				'#<img(.+?)src=["\'](.+?)["\'](.+?)(wp-image-.+?)/>#'
			),
			array(
				'<img$1$2src="' .$null. '" data-src="$3"$4/>',
				'<img$1src="' .$null. '" data-src="$2"$3$4/>'
			),
			$content
		);
	}


	/**
	* Print unveil scripts in footer
	*
	* @since   0.0.1
	* @change  0.0.1
	*/

	public static function print_scripts() {
		/* Globals */
		global $wp_scripts;

		/* Register script */
		wp_enqueue_script(
			'unveil',
			plugins_url(
				'/js/jquery.unveil.min.js',
				__FILE__
			),
			array('jquery'),
			'',
			true
		);

		/* Touch script */
		$wp_scripts->add_data(
			'unveil',
			'data',
			'jQuery(document).ready(function(){ jQuery("img[class*=wp-image-]").unveil(); });'
		);
	}
}