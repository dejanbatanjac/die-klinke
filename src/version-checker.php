<?php

/**
  * Make sure we are inside the WordPress environment.
  *
  * @since             1.0.0
  * @link              https://programming-review.com
  * @package           WW
  */

if ( ! function_exists( 'add_filter' ) ) {
  header( 'Status: 403 Forbidden' );
  header( 'HTTP/1.1 403 Forbidden' );
  exit();
}

/**
 * WordPress and PHP botomlines because of the security and functions we use.
 */
global $wp_version;

if ( version_compare( $wp_version, '4.5', '<=' ) ) {
  if ( is_admin() ) {
    // Admins deserve to know the reason of the problem.
    require_once admin_url( '/includes/plugin.php' );
    deactivate_plugins( __FILE__ );
    wp_die( __( 'The plugin requires WordPress 4.5 or higher. The plugin has now disabled itself.', 'die-klinke' ) );
  } else {
    // Don't go any further.
    wp_die();
  }
}

if ( version_compare( phpversion(), '5.6.0', '<' ) ) {
  if ( is_admin() ) {
    wp_die( __( 'The plugin requires PHP version higher than 5.6.0. The plugin has now disabled itself.', 'die-klinke' ) );
  } else {
    wp_die();
  }
}
