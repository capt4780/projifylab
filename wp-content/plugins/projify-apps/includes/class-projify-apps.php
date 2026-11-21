<?php
/**
 * Lõi plugin: post type, dữ liệu, seed.
 */

defined( 'ABSPATH' ) || exit;

class Projify_Apps {

	const CPT = 'projify_app';

	const META_URL      = '_projify_url';
	const META_STATUS   = '_projify_status';
	const META_FEATURED = '_projify_featured';
	const META_GLYPH    = '_projify_glyph';
	const META_GRADIENT = '_projify_gradient';

	const STATUS_ACTIVE = 'active';
	const STATUS_DEV    = 'dev';

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_post_type' ) );
	}

	/**
	 * Bộ màu gradient lấy từ logo Projify Lab.
	 *
	 * @return array<int, array{0:string,1:string}>
	 */
	public static function gradients() {
		return array(
			array( '#2F5BFF', '#7C3AED' ),
			array( '#06B6D4', '#2F5BFF' ),
			array( '#6366F1', '#A855F7' ),
			array( '#3B82F6', '#22D3EE' ),
			array( '#8B5CF6', '#EC4899' ),
			array( '#1D3FD6', '#3B82F6' ),
			array( '#0EA5E9', '#6366F1' ),
			array( '#7C3AED', '#C084FC' ),
		);
	}

	public static function statuses() {
		return array(
			self::STATUS_DEV    => __( 'Đang phát triển', 'projify-apps' ),
			self::STATUS_ACTIVE => __( 'Hoạt động', 'projify-apps' ),
		);
	}

	public static function register_post_type() {
		register_post_type(
			self::CPT,
			array(
				'labels'              => array(
					'name'               => __( 'Mini app', 'projify-apps' ),
					'singular_name'      => __( 'Mini app', 'projify-apps' ),
					'menu_name'          => __( 'Mini app', 'projify-apps' ),
					'add_new'            => __( 'Thêm mini app', 'projify-apps' ),
					'add_new_item'       => __( 'Thêm mini app mới', 'projify-apps' ),
					'edit_item'          => __( 'Sửa mini app', 'projify-apps' ),
					'new_item'           => __( 'Mini app mới', 'projify-apps' ),
					'search_items'       => __( 'Tìm mini app', 'projify-apps' ),
					'not_found'          => __( 'Chưa có mini app nào', 'projify-apps' ),
					'not_found_in_trash' => __( 'Thùng rác trống', 'projify-apps' ),
					'all_items'          => __( 'Tất cả mini app', 'projify-apps' ),
					'featured_image'     => __( 'Ảnh vuông 1:1', 'projify-apps' ),
					'set_featured_image' => __( 'Chọn ảnh vuông 1:1', 'projify-apps' ),
					'remove_featured_image' => __( 'Bỏ ảnh', 'projify-apps' ),
					'use_featured_image' => __( 'Dùng làm ảnh card', 'projify-apps' ),
				),
				'public'              => false,
				'publicly_queryable'  => false,
				'exclude_from_search' => true,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'show_in_rest'        => false,
				'menu_position'       => 5,
				'menu_icon'           => 'dashicons-screenoptions',
				'supports'            => array( 'title', 'excerpt', 'thumbnail', 'page-attributes' ),
				'hierarchical'        => false,
				'has_archive'         => false,
				'rewrite'             => false,
				'capability_type'     => 'post',
			)
		);
	}

	/**
	 * Lấy danh sách mini app đã xuất bản, theo thứ tự.
	 *
	 * @return array[]
	 */
	public static function get_apps() {
		$posts = get_posts(
			array(
				'post_type'        => self::CPT,
				'post_status'      => 'publish',
				'posts_per_page'   => 200,
				'orderby'          => array(
					'menu_order' => 'ASC',
					'title'      => 'ASC',
				),
				'suppress_filters' => false,
			)
		);

		$apps          = array();
		$featured_seen = false;
		$gradients     = self::gradients();

		foreach ( $posts as $post ) {
			$status   = get_post_meta( $post->ID, self::META_STATUS, true );
			$status   = self::STATUS_ACTIVE === $status ? self::STATUS_ACTIVE : self::STATUS_DEV;
			$featured = ! $featured_seen && '1' === get_post_meta( $post->ID, self::META_FEATURED, true );
			if ( $featured ) {
				$featured_seen = true;
			}

			$gradient = (int) get_post_meta( $post->ID, self::META_GRADIENT, true );
			if ( ! isset( $gradients[ $gradient ] ) ) {
				$gradient = 0;
			}

			$glyph = (string) get_post_meta( $post->ID, self::META_GLYPH, true );
			if ( '' === $glyph ) {
				$glyph = self::default_glyph( $post->post_title );
			}

			$image = '';
			if ( has_post_thumbnail( $post ) ) {
				$image = (string) get_the_post_thumbnail_url( $post, 'large' );
			}

			$active = self::STATUS_ACTIVE === $status;

			$apps[] = array(
				'id'          => (int) $post->ID,
				'title'       => get_the_title( $post ),
				'description' => wp_strip_all_tags( $post->post_excerpt ),
				// Link chỉ được trả ra khi app đang hoạt động.
				'url'         => $active ? esc_url_raw( (string) get_post_meta( $post->ID, self::META_URL, true ) ) : '',
				'status'      => $status,
				'active'      => $active,
				'featured'    => $featured,
				'glyph'       => $glyph,
				'gradient'    => $gradients[ $gradient ],
				'image'       => $image,
				'order'       => (int) $post->menu_order,
			);
		}

		// Đưa app nổi bật lên đầu danh sách.
		usort(
			$apps,
			static function ( $a, $b ) {
				if ( $a['featured'] !== $b['featured'] ) {
					return $a['featured'] ? -1 : 1;
				}
				return 0;
			}
		);

		return $apps;
	}

	/**
	 * Chữ mặc định: chữ cái đầu của tối đa 2 từ đầu tiên.
	 */
	public static function default_glyph( $title ) {
		$words = preg_split( '/\s+/u', trim( (string) $title ) );
		$out   = '';
		foreach ( array_slice( array_filter( $words ), 0, 2 ) as $w ) {
			$out .= mb_strtoupper( mb_substr( $w, 0, 1 ) );
		}
		return '' !== $out ? $out : 'P';
	}

	/**
	 * Kích hoạt plugin: đăng ký post type và tạo dữ liệu mẫu (chỉ một lần).
	 */
	public static function activate() {
		self::register_post_type();

		if ( get_option( 'projify_apps_seeded' ) ) {
			return;
		}

		$existing = get_posts(
			array(
				'post_type'      => self::CPT,
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'fields'         => 'ids',
			)
		);

		if ( empty( $existing ) ) {
			$seed = array(
				array( 'Thanh Trieu Health', 'TT', 0, true ),
				array( 'Wiflow', 'W', 1, false ),
			);
			for ( $i = 3; $i <= 8; $i++ ) {
				$seed[] = array( 'Mini app ' . $i, (string) $i, $i - 1, false );
			}

			foreach ( $seed as $order => $row ) {
				$id = wp_insert_post(
					array(
						'post_type'   => self::CPT,
						'post_status' => 'publish',
						'post_title'  => $row[0],
						'menu_order'  => $order,
					)
				);
				if ( $id && ! is_wp_error( $id ) ) {
					update_post_meta( $id, self::META_STATUS, self::STATUS_DEV );
					update_post_meta( $id, self::META_GLYPH, $row[1] );
					update_post_meta( $id, self::META_GRADIENT, $row[2] % count( self::gradients() ) );
					update_post_meta( $id, self::META_URL, '' );
					if ( $row[3] ) {
						update_post_meta( $id, self::META_FEATURED, '1' );
					}
				}
			}
		}

		update_option( 'projify_apps_seeded', 1, false );
	}
}
