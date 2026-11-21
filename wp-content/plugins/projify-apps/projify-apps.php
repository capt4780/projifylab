<?php
/**
 * Plugin Name:       Projify Apps
 * Description:       Quản lý các mini web app hiển thị trên trang chủ Projify Lab và trong app Projify (Android/iOS).
 * Version:           1.1.0
 * Author:            Projify Lab
 * Text Domain:       projify-apps
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * License:           GPL-2.0-or-later
 */

defined( 'ABSPATH' ) || exit;

define( 'PROJIFY_APPS_VERSION', '1.1.0' );
define( 'PROJIFY_APPS_FILE', __FILE__ );
define( 'PROJIFY_APPS_URL', plugin_dir_url( __FILE__ ) );

require_once __DIR__ . '/includes/class-projify-apps.php';
require_once __DIR__ . '/includes/class-projify-apps-admin.php';
require_once __DIR__ . '/includes/class-projify-banners.php';
require_once __DIR__ . '/includes/class-projify-apps-rest.php';

register_activation_hook(
	__FILE__,
	static function () {
		Projify_Apps::activate();
		Projify_Banners::register_post_type();
		Projify_Banners::seed();
		update_option( 'projify_apps_version', PROJIFY_APPS_VERSION, false );
	}
);

/**
 * Nâng cấp khi chép đè phiên bản mới (không qua kích hoạt lại).
 */
add_action(
	'init',
	static function () {
		if ( get_option( 'projify_apps_version' ) === PROJIFY_APPS_VERSION ) {
			return;
		}
		Projify_Banners::seed();
		update_option( 'projify_apps_version', PROJIFY_APPS_VERSION, false );
	},
	20
);

Projify_Apps::init();
Projify_Apps_Admin::init();
Projify_Banners::init();
Projify_Apps_Rest::init();

/**
 * Hàm tiện ích cho theme: trả về danh sách mini app đã chuẩn hoá.
 *
 * @return array[]
 */
function projify_get_apps() {
	return Projify_Apps::get_apps();
}

/**
 * Hàm tiện ích cho theme: danh sách banner đang bật.
 *
 * @return array[]
 */
function projify_get_banners() {
	return Projify_Banners::get_banners();
}
