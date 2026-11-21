<?php
/**
 * REST API cho app Projify (Android/iOS): GET /wp-json/projify/v1/apps
 */

defined( 'ABSPATH' ) || exit;

class Projify_Apps_Rest {

	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'routes' ) );
	}

	public static function routes() {
		register_rest_route(
			'projify/v1',
			'/apps',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'list_apps' ),
				'permission_callback' => '__return_true',
			)
		);
		register_rest_route(
			'projify/v1',
			'/banners',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'list_banners' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	private static function banners_payload() {
		return array_map(
			static function ( $b ) {
				return array(
					'id'          => $b['id'],
					'title'       => html_entity_decode( $b['title'], ENT_QUOTES, 'UTF-8' ),
					'description' => $b['description'],
					'link'        => $b['link'],
					'new_tab'     => $b['new_tab'],
					'show_text'   => $b['show_text'],
					'cta'         => $b['cta'],
					'image'       => $b['image'],
					'order'       => $b['order'],
				);
			},
			Projify_Banners::get_banners()
		);
	}

	public static function list_banners() {
		$response = rest_ensure_response(
			array(
				'version' => PROJIFY_APPS_VERSION,
				'banners' => self::banners_payload(),
			)
		);
		$response->header( 'Cache-Control', 'public, max-age=60' );
		return $response;
	}

	public static function list_apps() {
		$apps = array_map(
			static function ( $a ) {
				return array(
					'id'          => $a['id'],
					'title'       => html_entity_decode( $a['title'], ENT_QUOTES, 'UTF-8' ),
					'description' => $a['description'],
					'url'         => $a['url'],
					'status'      => $a['status'],
					'featured'    => $a['featured'],
					'glyph'       => $a['glyph'],
					'gradient'    => $a['gradient'],
					'image'       => $a['image'],
					'order'       => $a['order'],
				);
			},
			Projify_Apps::get_apps()
		);

		$response = rest_ensure_response(
			array(
				'version' => PROJIFY_APPS_VERSION,
				'apps'    => $apps,
				'banners' => self::banners_payload(),
			)
		);
		$response->header( 'Cache-Control', 'public, max-age=60' );
		return $response;
	}
}
