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
			)
		);
		$response->header( 'Cache-Control', 'public, max-age=60' );
		return $response;
	}
}
