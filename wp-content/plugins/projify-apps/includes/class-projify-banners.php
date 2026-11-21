<?php
/**
 * Banner quảng cáo dạng slide (tỉ lệ 2:1) trên trang chủ.
 */

defined( 'ABSPATH' ) || exit;

class Projify_Banners {

	const CPT = 'projify_banner';

	const META_LINK      = '_projify_banner_link';
	const META_ENABLED   = '_projify_banner_enabled';
	const META_SHOW_TEXT = '_projify_banner_show_text';
	const META_CTA       = '_projify_banner_cta';
	const META_NEW_TAB   = '_projify_banner_new_tab';
	const META_GRADIENT  = '_projify_banner_gradient';

	const NONCE = 'projify_banner_meta_nonce';

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_post_type' ) );
		add_action( 'add_meta_boxes_' . self::CPT, array( __CLASS__, 'add_meta_boxes' ) );
		add_action( 'save_post_' . self::CPT, array( __CLASS__, 'save' ), 10, 2 );
		add_filter( 'manage_' . self::CPT . '_posts_columns', array( __CLASS__, 'columns' ) );
		add_action( 'manage_' . self::CPT . '_posts_custom_column', array( __CLASS__, 'column_content' ), 10, 2 );
		add_action( 'pre_get_posts', array( __CLASS__, 'admin_default_order' ) );
		add_action( 'admin_head', array( __CLASS__, 'admin_css' ) );
		add_filter( 'enter_title_here', array( __CLASS__, 'title_placeholder' ), 10, 2 );
		add_filter( 'admin_post_thumbnail_html', array( __CLASS__, 'thumbnail_hint' ), 10, 2 );
	}

	/**
	 * Màu nền dùng khi banner chưa có ảnh.
	 */
	public static function gradients() {
		return array(
			array( 'label' => 'Xanh đậm', 'css' => 'linear-gradient(150deg, #0B1636 0%, #1D3FD6 60%, #2F5BFF 100%)', 'swatch' => array( '#0B1636', '#2F5BFF' ) ),
			array( 'label' => 'Tím hồng', 'css' => 'linear-gradient(150deg, #7C3AED 0%, #A855F7 55%, #EC4899 100%)', 'swatch' => array( '#7C3AED', '#EC4899' ) ),
			array( 'label' => 'Cyan', 'css' => 'linear-gradient(150deg, #0E7490 0%, #06B6D4 50%, #2F5BFF 100%)', 'swatch' => array( '#0E7490', '#2F5BFF' ) ),
			array( 'label' => 'Xanh tím', 'css' => 'linear-gradient(150deg, #1D3FD6 0%, #6366F1 55%, #A855F7 100%)', 'swatch' => array( '#1D3FD6', '#A855F7' ) ),
		);
	}

	public static function register_post_type() {
		register_post_type(
			self::CPT,
			array(
				'labels'              => array(
					'name'                  => __( 'Banner quảng cáo', 'projify-apps' ),
					'singular_name'         => __( 'Banner', 'projify-apps' ),
					'menu_name'             => __( 'Banner', 'projify-apps' ),
					'add_new'               => __( 'Thêm banner', 'projify-apps' ),
					'add_new_item'          => __( 'Thêm banner mới', 'projify-apps' ),
					'edit_item'             => __( 'Sửa banner', 'projify-apps' ),
					'new_item'              => __( 'Banner mới', 'projify-apps' ),
					'search_items'          => __( 'Tìm banner', 'projify-apps' ),
					'not_found'             => __( 'Chưa có banner nào', 'projify-apps' ),
					'not_found_in_trash'    => __( 'Thùng rác trống', 'projify-apps' ),
					'all_items'             => __( 'Tất cả banner', 'projify-apps' ),
					'featured_image'        => __( 'Ảnh banner 2:1', 'projify-apps' ),
					'set_featured_image'    => __( 'Chọn ảnh banner 2:1', 'projify-apps' ),
					'remove_featured_image' => __( 'Bỏ ảnh', 'projify-apps' ),
					'use_featured_image'    => __( 'Dùng làm ảnh banner', 'projify-apps' ),
				),
				'public'              => false,
				'publicly_queryable'  => false,
				'exclude_from_search' => true,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'show_in_rest'        => false,
				'menu_position'       => 6,
				'menu_icon'           => 'dashicons-images-alt2',
				'supports'            => array( 'title', 'excerpt', 'thumbnail', 'page-attributes' ),
				'hierarchical'        => false,
				'has_archive'         => false,
				'rewrite'             => false,
				'capability_type'     => 'post',
			)
		);
	}

	/**
	 * Banner đang bật, theo thứ tự.
	 *
	 * @return array[]
	 */
	public static function get_banners() {
		$posts = get_posts(
			array(
				'post_type'        => self::CPT,
				'post_status'      => 'publish',
				'posts_per_page'   => 20,
				'orderby'          => array(
					'menu_order' => 'ASC',
					'date'       => 'DESC',
				),
				'meta_key'         => self::META_ENABLED,
				'meta_value'       => '1',
				'suppress_filters' => false,
			)
		);

		$gradients = self::gradients();
		$out       = array();

		foreach ( $posts as $post ) {
			$g = (int) get_post_meta( $post->ID, self::META_GRADIENT, true );
			if ( ! isset( $gradients[ $g ] ) ) {
				$g = 0;
			}
			$cta = (string) get_post_meta( $post->ID, self::META_CTA, true );

			$out[] = array(
				'id'          => (int) $post->ID,
				'title'       => get_the_title( $post ),
				'description' => wp_strip_all_tags( $post->post_excerpt ),
				'link'        => esc_url_raw( (string) get_post_meta( $post->ID, self::META_LINK, true ) ),
				'new_tab'     => '1' === get_post_meta( $post->ID, self::META_NEW_TAB, true ),
				'show_text'   => '0' !== get_post_meta( $post->ID, self::META_SHOW_TEXT, true ),
				'cta'         => '' !== $cta ? $cta : __( 'Xem ngay', 'projify-apps' ),
				'image'       => has_post_thumbnail( $post ) ? (string) get_the_post_thumbnail_url( $post, 'full' ) : '',
				'background'  => $gradients[ $g ]['css'],
				'order'       => (int) $post->menu_order,
			);
		}

		return $out;
	}

	/**
	 * Tạo 1 banner mẫu (chỉ một lần) để trang chủ có đúng bố cục ngay khi cài.
	 */
	public static function seed() {
		if ( get_option( 'projify_banners_seeded' ) ) {
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
			$id = wp_insert_post(
				array(
					'post_type'    => self::CPT,
					'post_status'  => 'publish',
					'post_title'   => 'Projify Lab đang được xây dựng',
					'post_excerpt' => 'Các mini app sẽ lần lượt ra mắt. Hẹn bạn sớm nhé!',
					'menu_order'   => 0,
				)
			);
			if ( $id && ! is_wp_error( $id ) ) {
				update_post_meta( $id, self::META_ENABLED, '1' );
				update_post_meta( $id, self::META_SHOW_TEXT, '1' );
				update_post_meta( $id, self::META_GRADIENT, 0 );
				update_post_meta( $id, self::META_LINK, '' );
			}
		}
		update_option( 'projify_banners_seeded', 1, false );
	}

	/* ---------- Quản trị ---------- */

	public static function title_placeholder( $text, $post ) {
		return ( $post && self::CPT === $post->post_type ) ? __( 'Tiêu đề banner', 'projify-apps' ) : $text;
	}

	public static function thumbnail_hint( $html, $post_id ) {
		if ( self::CPT === get_post_type( $post_id ) ) {
			$html .= '<p class="description">' . esc_html__( 'Ảnh ngang tỉ lệ 2:1, khuyên dùng 1600 × 800 px.', 'projify-apps' ) . '</p>';
		}
		return $html;
	}

	public static function add_meta_boxes() {
		add_meta_box( 'projify_banner_settings', __( 'Cài đặt banner', 'projify-apps' ), array( __CLASS__, 'render_meta_box' ), self::CPT, 'normal', 'high' );
	}

	public static function render_meta_box( $post ) {
		wp_nonce_field( 'projify_banner_save', self::NONCE );

		$is_new    = 'auto-draft' === $post->post_status;
		$enabled   = $is_new ? true : '1' === get_post_meta( $post->ID, self::META_ENABLED, true );
		$show_text = '0' !== get_post_meta( $post->ID, self::META_SHOW_TEXT, true );
		$link      = (string) get_post_meta( $post->ID, self::META_LINK, true );
		$new_tab   = '1' === get_post_meta( $post->ID, self::META_NEW_TAB, true );
		$cta       = (string) get_post_meta( $post->ID, self::META_CTA, true );
		$gradient  = (int) get_post_meta( $post->ID, self::META_GRADIENT, true );
		?>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><?php esc_html_e( 'Bật/tắt', 'projify-apps' ); ?></th>
				<td><label><input type="checkbox" name="projify_banner_enabled" value="1" <?php checked( $enabled ); ?>> <?php esc_html_e( 'Hiển thị banner này trên trang chủ', 'projify-apps' ); ?></label></td>
			</tr>
			<tr>
				<th scope="row"><label for="projify_banner_link"><?php esc_html_e( 'Link', 'projify-apps' ); ?></label></th>
				<td>
					<input type="url" class="regular-text code" id="projify_banner_link" name="projify_banner_link" value="<?php echo esc_attr( $link ); ?>" placeholder="https://">
					<p><label><input type="checkbox" name="projify_banner_new_tab" value="1" <?php checked( $new_tab ); ?>> <?php esc_html_e( 'Mở ở tab mới', 'projify-apps' ); ?></label></p>
					<p class="description"><?php esc_html_e( 'Để trống nếu banner chỉ để xem, không bấm được.', 'projify-apps' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Chữ trên banner', 'projify-apps' ); ?></th>
				<td>
					<label><input type="checkbox" name="projify_banner_show_text" value="1" <?php checked( $show_text ); ?>> <?php esc_html_e( 'Hiện tiêu đề, mô tả và nút trên banner', 'projify-apps' ); ?></label>
					<p class="description"><?php esc_html_e( 'Bỏ chọn khi chữ đã nằm sẵn trong ảnh: banner chỉ hiện ảnh (vẫn bấm được nếu có link).', 'projify-apps' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="projify_banner_cta"><?php esc_html_e( 'Chữ trên nút', 'projify-apps' ); ?></label></th>
				<td><input type="text" id="projify_banner_cta" name="projify_banner_cta" value="<?php echo esc_attr( $cta ); ?>" placeholder="<?php esc_attr_e( 'Xem ngay', 'projify-apps' ); ?>" maxlength="24"></td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Màu nền', 'projify-apps' ); ?></th>
				<td>
					<fieldset class="projify-swatches">
						<legend class="screen-reader-text"><?php esc_html_e( 'Màu nền', 'projify-apps' ); ?></legend>
						<?php foreach ( self::gradients() as $i => $g ) : ?>
							<label class="projify-swatch projify-swatch--wide" style="background: linear-gradient(135deg, <?php echo esc_attr( $g['swatch'][0] ); ?>, <?php echo esc_attr( $g['swatch'][1] ); ?>);">
								<input type="radio" name="projify_banner_gradient" value="<?php echo (int) $i; ?>" <?php checked( $gradient, $i ); ?>>
								<span class="screen-reader-text"><?php echo esc_html( $g['label'] ); ?></span>
							</label>
						<?php endforeach; ?>
					</fieldset>
					<p class="description"><?php esc_html_e( 'Dùng khi chưa có ảnh banner.', 'projify-apps' ); ?></p>
				</td>
			</tr>
		</table>
		<p class="description"><?php esc_html_e( 'Mô tả: nhập ở khung "Tóm tắt" bên dưới. Thứ tự slide: ô "Thứ tự" trong khung Thuộc tính (số nhỏ chạy trước).', 'projify-apps' ); ?></p>
		<?php
	}

	public static function save( $post_id, $post ) {
		if ( ! isset( $_POST[ self::NONCE ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ self::NONCE ] ) ), 'projify_banner_save' ) ) {
			return;
		}
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		update_post_meta( $post_id, self::META_ENABLED, empty( $_POST['projify_banner_enabled'] ) ? '0' : '1' );
		update_post_meta( $post_id, self::META_SHOW_TEXT, empty( $_POST['projify_banner_show_text'] ) ? '0' : '1' );
		update_post_meta( $post_id, self::META_NEW_TAB, empty( $_POST['projify_banner_new_tab'] ) ? '0' : '1' );

		$link = isset( $_POST['projify_banner_link'] ) ? esc_url_raw( wp_unslash( $_POST['projify_banner_link'] ), array( 'http', 'https' ) ) : '';
		update_post_meta( $post_id, self::META_LINK, $link );

		$cta = isset( $_POST['projify_banner_cta'] ) ? sanitize_text_field( wp_unslash( $_POST['projify_banner_cta'] ) ) : '';
		update_post_meta( $post_id, self::META_CTA, mb_substr( $cta, 0, 24 ) );

		$g = isset( $_POST['projify_banner_gradient'] ) ? absint( $_POST['projify_banner_gradient'] ) : 0;
		update_post_meta( $post_id, self::META_GRADIENT, $g < count( self::gradients() ) ? $g : 0 );
	}

	public static function columns( $columns ) {
		$new = array( 'cb' => $columns['cb'] );
		$new['projify_banner_img'] = __( 'Banner', 'projify-apps' );
		$new['title']              = $columns['title'];
		$new['projify_banner_on']  = __( 'Trạng thái', 'projify-apps' );
		$new['projify_banner_link'] = __( 'Link', 'projify-apps' );
		$new['menu_order']         = __( 'Thứ tự', 'projify-apps' );
		return $new;
	}

	public static function column_content( $column, $post_id ) {
		switch ( $column ) {
			case 'projify_banner_img':
				if ( has_post_thumbnail( $post_id ) ) {
					echo get_the_post_thumbnail( $post_id, array( 120, 60 ), array( 'class' => 'projify-banner-thumb' ) );
				} else {
					$g = self::gradients();
					$i = (int) get_post_meta( $post_id, self::META_GRADIENT, true );
					$i = isset( $g[ $i ] ) ? $i : 0;
					printf( '<span class="projify-banner-thumb" style="background:%s"></span>', esc_attr( $g[ $i ]['css'] ) );
				}
				break;
			case 'projify_banner_on':
				if ( '1' === get_post_meta( $post_id, self::META_ENABLED, true ) ) {
					echo '<span class="projify-badge is-active">' . esc_html__( 'Đang bật', 'projify-apps' ) . '</span>';
				} else {
					echo '<span class="projify-badge is-dev">' . esc_html__( 'Đang tắt', 'projify-apps' ) . '</span>';
				}
				break;
			case 'projify_banner_link':
				$link = (string) get_post_meta( $post_id, self::META_LINK, true );
				echo $link ? '<code>' . esc_html( wp_parse_url( $link, PHP_URL_HOST ) ) . '</code>' : '—';
				break;
			case 'menu_order':
				echo (int) get_post_field( 'menu_order', $post_id );
				break;
		}
	}

	public static function admin_default_order( $query ) {
		if ( is_admin() && $query->is_main_query() && self::CPT === $query->get( 'post_type' ) && ! $query->get( 'orderby' ) ) {
			$query->set( 'orderby', 'menu_order' );
			$query->set( 'order', 'ASC' );
		}
	}

	public static function admin_css() {
		$screen = get_current_screen();
		if ( ! $screen || self::CPT !== $screen->post_type ) {
			return;
		}
		?>
		<style>
			.projify-swatches{display:flex;gap:10px;flex-wrap:wrap;border:0;margin:0;padding:0}
			.projify-swatch{width:40px;height:40px;border-radius:12px;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;box-shadow:inset 0 0 0 2px rgba(255,255,255,.4)}
			.projify-swatch--wide{width:80px}
			.projify-swatch input{margin:0;accent-color:#fff}
			.projify-banner-thumb{display:inline-block;width:120px;height:60px;border-radius:10px;object-fit:cover}
			.column-projify_banner_img{width:136px}
			.column-projify_banner_on{width:110px}
			.column-menu_order{width:80px}
			.projify-badge{display:inline-block;padding:3px 10px;border-radius:999px;font-weight:600;font-size:12px}
			.projify-badge.is-dev{background:#F1F5F9;color:#334155}
			.projify-badge.is-active{background:#DCFCE7;color:#166534}
		</style>
		<?php
	}
}
