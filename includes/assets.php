<?php
/**
 * Enqueue and handle assets
 *
 * @since      1.1
 * @package    Secure File Manager
 * @author     Themexa
 */

// Admin Assets
function sfm_enqueue_admin_assets( $hook ) {

	global $sfm_file_manager;
    global $sfm_access_control;
    global $sfm_settings;

	if( $hook != $sfm_file_manager && $hook != $sfm_access_control && $hook != $sfm_settings) {
		return;
	}

	$currentUserRoles = wp_get_current_user()->roles;
	
	if ( in_array( get_current_user_id(), get_option( 'sfm_auth_user' ) ) || !empty( array_intersect( $currentUserRoles, get_option( 'sfm_auth_roles' ) ) ) ) {
        $sfmp_nonce_key = wp_create_nonce( 'secure-file-manager-pro' );
    }

    wp_enqueue_style( 'sfm-admin-normalize',  plugin_dir_url( dirname( __FILE__ ) ) . 'assets/admin/css/normalize.css' );
    wp_enqueue_style( 'sfm-admin-cosmostrap',  plugin_dir_url( dirname( __FILE__ ) ) . 'assets/admin/css/cosmostrap.css' );
    wp_enqueue_style( 'sfm-admin-elfinder', plugin_dir_url( dirname( __FILE__ ) ) . 'vendor/elfinder/css/elfinder.full.css', false, '1.0.0' );
    wp_enqueue_style( 'sfm-admin-theme', plugin_dir_url( dirname( __FILE__ ) ) . 'vendor/elfinder/themes/windows-10/css/theme.css', false, '1.0.0' );
    wp_enqueue_style( 'sfm-admin-style', plugin_dir_url( dirname( __FILE__ ) ) . 'assets/admin/css/plugin-admin-style.css' );
    wp_enqueue_script( 'jquery-ui-draggable' );
    wp_enqueue_script( 'jquery-ui-droppable' );
    wp_enqueue_script( 'jquery-ui-resizable' );
    wp_enqueue_script( 'jquery-ui-selectable' );
    wp_enqueue_script( 'jquery-ui-button' );
    wp_enqueue_script( 'jquery-ui-slider' );
    wp_enqueue_script( 'sfm-admin-popper',  plugin_dir_url( dirname( __FILE__ ) ) . 'assets/admin/js/popper.min.js' );
    wp_enqueue_script( 'sfm-admin-bootstrap',  plugin_dir_url( dirname( __FILE__ ) ) . 'assets/admin/js/bootstrap.min.js' );
    wp_enqueue_script( 'sfm-admin-elfinder-min', plugin_dir_url( dirname( __FILE__ ) ) . 'vendor/elfinder/js/elfinder.min.js' );
    wp_enqueue_script( 'sfm-admin-editor-default', plugin_dir_url( dirname( __FILE__ ) ) . 'vendor/elfinder/js/extras/editors.default.js' );
    wp_enqueue_script( 'sfm-admin-vendor-script', plugin_dir_url( dirname( __FILE__ ) ) . 'vendor/elfinder/js/script.js' );
    wp_localize_script('sfm-admin-vendor-script', 'elfScript', array(
        'pluginsDirUrl' => plugin_dir_url( dirname( __FILE__ ) ),
        'sfmpNonceKey' => $sfmp_nonce_key
    ));
    wp_enqueue_script( 'sfm-admin-script',  plugin_dir_url( dirname( __FILE__ ) ) . 'assets/admin/js/plugin-admin-script.js' );

}

add_action( 'admin_enqueue_scripts', 'sfm_enqueue_admin_assets' );
