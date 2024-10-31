<?php
/**
 * Execute the following code during plugin activation
 *
 * @since      2.1
 * @package    Secure File Manager
 * @author     Themexa
 */

if ( empty( get_option( 'sfm_auth_roles' ) ) ) {
	update_option ( 'sfm_auth_roles', explode( ',', 'administrator' ) );
}