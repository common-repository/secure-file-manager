<?php

/**
 * @package Secure File Manager
 * @version 2.9.3
 */

/*
Plugin Name: Secure File Manager
Plugin URI: https://themexa.com/secure-file-manager-pro
Description: Most Beautiful and Secure WordPress File Manager
Author: Themexa
Version: 2.9.3
Author URI: https://www.themexa.com
License: GPL2
Text Domain: secure-file-manager
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
  die;
}

/**
 * Current plugin version.
 */
define( 'secure-file-manager', '2.9.3' );

/**
 * Secure File Manager Plugin File
 */
define( 'SECURE_FILE_MANAGER_PLUGIN_FILE', __FILE__ );

/**
 * Code that runs on plugin activation
 */
register_activation_hook( __FILE__, 'sfm_activate' );

function sfm_activate() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/activation.php';
}

//Calling the core file
require_once plugin_dir_path( __FILE__ ) . 'includes/core.php';
