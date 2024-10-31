<?php
defined( 'ABSPATH' ) or die( 'WordPress is not loaded' );

/**
 * Do blog-id shortcode.
 *
 * Get the current blog id.
 *
 * @see get_current_blog_id
 *
 * @param array $atts {
 *     Currently no attributes supported.
 * }
 *
 * @return int
 */
function qrcodes_shortcode_blogid( $atts ) {
	return get_current_blog_id();
}

add_shortcode( 'blog-id', 'qrcodes_shortcode_blogid' );

/**
 * Do user-id shortcode.
 *
 * Get the current user id.
 *
 * @see get_current_user_id
 *
 * @param array $atts {
 *     Currently no attributes supported.
 * }
 *
 * @return int
 */
function qrcodes_shortcode_userid( $atts ) {
	return get_current_user_id();
}

add_shortcode( 'user-id', 'qrcodes_shortcode_userid' );

/**
 * Do current-url shortcode.
 *
 * Get the current url.
 *
 * @see get_current_user_id
 *
 * @param array $atts {
 *     Any shortcode attributes.
 *
 *     @type string $encode Set to true to get the encoded url.
 * }
 *
 * @return string
 */
function qrcodes_shortcode_current_url( $atts ) {
	$atts = shortcode_atts(
		array( 'encode' => 'false' ),
		$atts,
		'current-url'
	);
	$url  = qrcodes_get_current_url();

	if ( wp_validate_boolean( $atts['encode'] ) ) {
		$url = urlencode( $url );
	}

	return $url;
}

add_shortcode( 'current-url', 'qrcodes_shortcode_current_url' );