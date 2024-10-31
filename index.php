<?php
/*
Plugin Name: QRCodes
Description: Use media query to add qrcodes to pages and posts.
Author: Pierre Peronnet
Domain Path: /languages
Text Domain: qrcodes
Version: 2.1
*/

defined( 'ABSPATH' ) or die( 'WordPress is not loaded' );

/**
 * Include additional shortcodes.
 */
include_once plugin_dir_path( __FILE__ ) . '/shortcodes.php';

/**
 * Include options page.
 */
include_once plugin_dir_path( __FILE__ ) . '/settings.php';

/**
 * Get genral options of the plugin.
 *
 * @return array
 */
function qrcodes_get_general_options() {
	$values = get_option( 'qrcodes_data', array(
		'text'             => '[current-url]',
		'width'            => 128,
		'media'            => 'print',
		'primary-color'    => '#000000',
		'secondary-color'  => '#FFFFFF',
		'correction-level' => 'QRCode.CorrectLevel.Q',
	) );

	if ( current_theme_supports( 'qrcodes' ) ) {
		$theme_supports = get_theme_support( 'qrcodes' );

		$theme_supports = array_intersect_key( $theme_supports, array(
			'width',
			'primary-color',
			'secondary-color',
			'correction-level',
		) );

		return wp_parse_args( $theme_supports, $values );
	}

	return $values;
}

/**
 * Registers scripts and styles.
 *
 * Register all scripts and styles libraries required by QRCodes plugin.
 */
function qrcodes_register_scripts_libraries() {
    if ( ! wp_script_is( 'jquery', 'registered' ) ) {
        wp_register_script( 'jquery', plugin_dir_url( __FILE__ ) . 'scripts/qrcodejs/jquery.min.js', array(), '1.8.3', true );
    }
    if ( ! wp_script_is( 'qrcodejs', 'registered' ) ) {
        wp_register_script( 'qrcodejs', plugin_dir_url( __FILE__ ) . 'scripts/qrcodejs/qrcode.min.js', array( 'jquery' ), '1.0', true );
    }
}

add_action( 'wp_enqueue_scripts', 'qrcodes_register_scripts_libraries', 8 );

/**
 * Enqueue main script and style
 *
 * Enqueue main script of the QRCodes plugin.
 */
function qrcodes_enqueue_scripts() {
    $directory_url = plugin_dir_url( __FILE__ );

    wp_enqueue_script( 'qrcodes', $directory_url . 'scripts/qrcodes.js', array( 'qrcodejs', 'jquery' ), '2.0', true );
    if ( ! current_theme_supports( 'qrcodes' ) ) {
        $options = qrcodes_get_general_options();

        /**
         * Filter media of the QRCode.
         *
         * Overwrite the media query where the QRCode will be displayed.
         *
         * @param string $var media query.
         */
        $media = apply_filters( 'qrcodes_media', $options['media'] );

        wp_enqueue_style( 'qrcodes', $directory_url . 'styles/qrcodes.css', array(), '2.0', $media );

	    $style = '#qrcodes-container{';
        $style .= 'display:none;';
        $style .= 'top:0;';
        $style .= 'right:0;';
        $style .= '}';
        wp_add_inline_style( 'qrcodes', $style );
    }
}

add_action( 'wp_enqueue_scripts', 'qrcodes_enqueue_scripts' );

/**
 * Add script data.
 *
 * Add data required for main script.
 */
function qrcodes_script_data() {
	$options = qrcodes_get_general_options();

    /**
     * Filter data of the QRCode.
     *
     * Overwrite the data encoded in the QRCode.
     *
     * @param array $var {
     *     Array for a new QRCode() from qrcodejs.
     *
     *     @type string $text         Data to encode.
     *     @type int    $width        Width of the QRCode.
     *     @type int    $height       Height of the QRCode.
     *     @type string $colorDark    Color of pixels. Usually black.
     *     @type string $colorLight   Color of background. Usually white.
     *     @type string $correctLevel Level correction. (QRCode.CorrectLevel.H | QRCode.CorrectLevel.Q | QRCode.CorrectLevel.M | QRCode.CorrectLevel.L)
     * }
     * @see wp_localize_script
     * @see https://github.com/davidshimjs/qrcodejs
     */
    $data = apply_filters( 'qrcodes_data', array(
        'text'         => do_shortcode( $options['text'] ),
        'width'        => $options['width'],
        'height'       => $options['width'],
        'colorDark'    => $options['primary-color'],
        'colorLight'   => $options['secondary-color'],
        'correctLevel' => $options['correction-level'],
    ) );

    wp_localize_script( 'qrcodes', 'qrcodes_params', $data );
}

add_action( 'wp_enqueue_scripts', 'qrcodes_script_data' );

/**
 * Get the current url.
 *
 * @return string
 */
function qrcodes_get_current_url() {
    return path_join(
        'http' . ( isset( $_SERVER['HTTPS'] ) ? 's' : '' ) . '://' . $_SERVER['HTTP_HOST'],
        ltrim( $_SERVER['REQUEST_URI'], '/' )
    );
}

/**
 * Add text domain.
 *
 * Add text domain directory used by QRCodes plugin.
 */
function qrcodes_load_domain() {
    load_plugin_textdomain( 'qrcodes', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

add_action( 'plugins_loaded', 'qrcodes_load_domain' );