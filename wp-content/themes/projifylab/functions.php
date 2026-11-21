<?php
/**
 * Theme Projifylab.
 */

defined( 'ABSPATH' ) || exit;

define( 'PROJIFYLAB_THEME_VERSION', '1.2.0' );

add_action(
	'after_setup_theme',
	static function () {
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
		add_theme_support(
			'custom-logo',
			array(
				'height'      => 96,
				'width'       => 96,
				'flex-height' => true,
				'flex-width'  => true,
			)
		);
	}
);

add_action(
	'wp_enqueue_scripts',
	static function () {
		$uri = get_template_directory_uri();
		wp_enqueue_style( 'projifylab-font', 'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap', array(), null );
		wp_enqueue_style( 'projifylab-launcher', $uri . '/assets/css/launcher.css', array(), PROJIFYLAB_THEME_VERSION );
		wp_enqueue_script( 'projifylab-launcher', $uri . '/assets/js/launcher.js', array(), PROJIFYLAB_THEME_VERSION, array( 'in_footer' => true, 'strategy' => 'defer' ) );
	}
);

add_filter(
	'wp_resource_hints',
	static function ( $urls, $relation ) {
		if ( 'preconnect' === $relation ) {
			$urls[] = 'https://fonts.googleapis.com';
			$urls[] = array(
				'href'        => 'https://fonts.gstatic.com',
				'crossorigin' => 'anonymous',
			);
		}
		return $urls;
	},
	10,
	2
);

/**
 * Nhận biết trang đang mở trong app Projify (app gắn "ProjifyApp" vào User-Agent).
 */
function projifylab_in_app() {
	$ua = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
	return false !== stripos( $ua, 'ProjifyApp' );
}

add_filter(
	'body_class',
	static function ( $classes ) {
		if ( projifylab_in_app() ) {
			$classes[] = 'in-projify-app';
		}
		if ( is_front_page() && projifylab_get_banners() ) {
			$classes[] = 'has-banner';
		}
		return $classes;
	}
);

/**
 * Màu theme cho thanh trình duyệt trên điện thoại.
 */
add_action(
	'wp_head',
	static function () {
		echo '<meta name="theme-color" content="#EEF2FF">' . "\n";
	},
	1
);

/**
 * Danh sách mini app (từ plugin Projify Apps).
 *
 * @return array[]|null null nếu plugin chưa bật.
 */
function projifylab_get_apps() {
	if ( ! function_exists( 'projify_get_apps' ) ) {
		return null;
	}
	return projify_get_apps();
}

/**
 * Banner quảng cáo đang bật (từ plugin Projify Apps). Kết quả được nhớ trong một lần tải trang.
 *
 * @return array[]
 */
function projifylab_get_banners() {
	static $cache = null;
	if ( null === $cache ) {
		$cache = function_exists( 'projify_get_banners' ) ? projify_get_banners() : array();
	}
	return $cache;
}

/**
 * Logo: dùng Custom Logo nếu đã chọn trong Giao diện > Tùy biến, ngược lại dùng logo đi kèm theme.
 */
function projifylab_logo_url() {
	$id = get_theme_mod( 'custom_logo' );
	if ( $id ) {
		$src = wp_get_attachment_image_url( $id, 'medium' );
		if ( $src ) {
			return $src;
		}
	}
	return get_template_directory_uri() . '/assets/img/projify-mark.png';
}

/**
 * In một card mini app.
 *
 * @param array $app  Dữ liệu từ projify_get_apps().
 * @param bool  $hero Card nổi bật (siêu to).
 */
function projifylab_render_card( $app, $hero = false ) {
	get_template_part(
		'template-parts/app-card',
		null,
		array(
			'app'  => $app,
			'hero' => $hero,
		)
	);
}

/**
 * Icon ổ khóa (SVG nội tuyến).
 */
function projifylab_lock_svg() {
	return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><rect x="4.5" y="10.5" width="15" height="10" rx="3"/><path d="M8 10.5V7.5a4 4 0 0 1 8 0v3"/><path d="M12 14.5v2.5"/></svg>';
}
