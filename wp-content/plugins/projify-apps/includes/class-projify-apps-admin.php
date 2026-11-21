<?php
/**
 * Màn hình quản trị: meta box, cột danh sách.
 */

defined( 'ABSPATH' ) || exit;

class Projify_Apps_Admin {

	const NONCE = 'projify_app_meta_nonce';

	public static function init() {
		add_action( 'add_meta_boxes_' . Projify_Apps::CPT, array( __CLASS__, 'add_meta_boxes' ) );
		add_action( 'save_post_' . Projify_Apps::CPT, array( __CLASS__, 'save' ), 10, 2 );
		add_filter( 'manage_' . Projify_Apps::CPT . '_posts_columns', array( __CLASS__, 'columns' ) );
		add_action( 'manage_' . Projify_Apps::CPT . '_posts_custom_column', array( __CLASS__, 'column_content' ), 10, 2 );
		add_filter( 'manage_edit-' . Projify_Apps::CPT . '_sortable_columns', array( __CLASS__, 'sortable_columns' ) );
		add_action( 'pre_get_posts', array( __CLASS__, 'admin_default_order' ) );
		add_action( 'admin_head', array( __CLASS__, 'admin_css' ) );
		add_filter( 'enter_title_here', array( __CLASS__, 'title_placeholder' ), 10, 2 );
		add_action( 'admin_head-post.php', array( __CLASS__, 'excerpt_label' ) );
		add_action( 'admin_head-post-new.php', array( __CLASS__, 'excerpt_label' ) );
	}

	public static function title_placeholder( $text, $post ) {
		return ( $post && Projify_Apps::CPT === $post->post_type ) ? __( 'Tên mini app', 'projify-apps' ) : $text;
	}

	public static function add_meta_boxes() {
		add_meta_box(
			'projify_app_settings',
			__( 'Cài đặt mini app', 'projify-apps' ),
			array( __CLASS__, 'render_meta_box' ),
			Projify_Apps::CPT,
			'normal',
			'high'
		);
	}

	public static function render_meta_box( $post ) {
		wp_nonce_field( 'projify_app_save', self::NONCE );

		$url      = (string) get_post_meta( $post->ID, Projify_Apps::META_URL, true );
		$status   = (string) get_post_meta( $post->ID, Projify_Apps::META_STATUS, true );
		$status   = '' === $status ? Projify_Apps::STATUS_DEV : $status;
		$featured = '1' === get_post_meta( $post->ID, Projify_Apps::META_FEATURED, true );
		$glyph    = (string) get_post_meta( $post->ID, Projify_Apps::META_GLYPH, true );
		$gradient = (int) get_post_meta( $post->ID, Projify_Apps::META_GRADIENT, true );
		?>
		<table class="form-table projify-meta" role="presentation">
			<tr>
				<th scope="row"><label for="projify_status"><?php esc_html_e( 'Trạng thái', 'projify-apps' ); ?></label></th>
				<td>
					<select name="projify_status" id="projify_status">
						<?php foreach ( Projify_Apps::statuses() as $key => $label ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $status, $key ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
					<p class="description"><?php esc_html_e( '"Đang phát triển": card hiện ổ khóa, người dùng không mở được.', 'projify-apps' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="projify_url"><?php esc_html_e( 'Link mini app', 'projify-apps' ); ?></label></th>
				<td>
					<input type="url" class="regular-text code" name="projify_url" id="projify_url" value="<?php echo esc_attr( $url ); ?>" placeholder="https://">
					<p class="description"><?php esc_html_e( 'Trang sẽ mở khi bấm vào card (chỉ khi trạng thái là Hoạt động).', 'projify-apps' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Nổi bật', 'projify-apps' ); ?></th>
				<td>
					<label><input type="checkbox" name="projify_featured" value="1" <?php checked( $featured ); ?>> <?php esc_html_e( 'Hiển thị app này ở card siêu to trên cùng', 'projify-apps' ); ?></label>
					<p class="description"><?php esc_html_e( 'Chỉ một app được nổi bật; chọn app này sẽ bỏ nổi bật ở app khác.', 'projify-apps' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="projify_glyph"><?php esc_html_e( 'Chữ trên card', 'projify-apps' ); ?></label></th>
				<td>
					<input type="text" name="projify_glyph" id="projify_glyph" value="<?php echo esc_attr( $glyph ); ?>" maxlength="3" class="small-text">
					<p class="description"><?php esc_html_e( 'Tối đa 3 ký tự, hiện khi chưa có ảnh. Để trống sẽ lấy chữ cái đầu của tên.', 'projify-apps' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Màu card', 'projify-apps' ); ?></th>
				<td>
					<fieldset class="projify-swatches">
						<legend class="screen-reader-text"><?php esc_html_e( 'Màu card', 'projify-apps' ); ?></legend>
						<?php foreach ( Projify_Apps::gradients() as $i => $g ) : ?>
							<label class="projify-swatch" style="background: linear-gradient(145deg, <?php echo esc_attr( $g[0] ); ?>, <?php echo esc_attr( $g[1] ); ?>);">
								<input type="radio" name="projify_gradient" value="<?php echo (int) $i; ?>" <?php checked( $gradient, $i ); ?>>
								<span class="screen-reader-text"><?php echo esc_html( sprintf( __( 'Màu %d', 'projify-apps' ), $i + 1 ) ); ?></span>
							</label>
						<?php endforeach; ?>
					</fieldset>
					<p class="description"><?php esc_html_e( 'Dùng khi chưa có ảnh vuông. Ảnh vuông 1:1 đặt ở khung "Ảnh vuông 1:1" bên phải.', 'projify-apps' ); ?></p>
				</td>
			</tr>
		</table>
		<p class="description"><?php esc_html_e( 'Mô tả ngắn: nhập ở khung "Mô tả ngắn" bên dưới. Thứ tự hiển thị: ô "Thứ tự" trong khung Thuộc tính (số nhỏ đứng trước).', 'projify-apps' ); ?></p>
		<?php
	}

	public static function save( $post_id, $post ) {
		if ( ! isset( $_POST[ self::NONCE ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ self::NONCE ] ) ), 'projify_app_save' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( wp_is_post_revision( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$status = isset( $_POST['projify_status'] ) ? sanitize_key( wp_unslash( $_POST['projify_status'] ) ) : Projify_Apps::STATUS_DEV;
		if ( ! array_key_exists( $status, Projify_Apps::statuses() ) ) {
			$status = Projify_Apps::STATUS_DEV;
		}
		update_post_meta( $post_id, Projify_Apps::META_STATUS, $status );

		$url = isset( $_POST['projify_url'] ) ? esc_url_raw( wp_unslash( $_POST['projify_url'] ), array( 'http', 'https' ) ) : '';
		update_post_meta( $post_id, Projify_Apps::META_URL, $url );

		$glyph = isset( $_POST['projify_glyph'] ) ? sanitize_text_field( wp_unslash( $_POST['projify_glyph'] ) ) : '';
		update_post_meta( $post_id, Projify_Apps::META_GLYPH, mb_substr( $glyph, 0, 3 ) );

		$gradient = isset( $_POST['projify_gradient'] ) ? absint( $_POST['projify_gradient'] ) : 0;
		if ( $gradient >= count( Projify_Apps::gradients() ) ) {
			$gradient = 0;
		}
		update_post_meta( $post_id, Projify_Apps::META_GRADIENT, $gradient );

		$featured = ! empty( $_POST['projify_featured'] );
		if ( $featured ) {
			update_post_meta( $post_id, Projify_Apps::META_FEATURED, '1' );
			// Bỏ nổi bật ở các app khác.
			$others = get_posts(
				array(
					'post_type'      => Projify_Apps::CPT,
					'post_status'    => 'any',
					'posts_per_page' => -1,
					'fields'         => 'ids',
					'post__not_in'   => array( $post_id ),
					'meta_key'       => Projify_Apps::META_FEATURED,
					'meta_value'     => '1',
				)
			);
			foreach ( $others as $other_id ) {
				delete_post_meta( $other_id, Projify_Apps::META_FEATURED );
			}
		} else {
			delete_post_meta( $post_id, Projify_Apps::META_FEATURED );
		}
	}

	public static function columns( $columns ) {
		$new = array();
		foreach ( $columns as $key => $label ) {
			if ( 'title' === $key ) {
				$new['projify_card'] = __( 'Card', 'projify-apps' );
			}
			$new[ $key ] = $label;
			if ( 'title' === $key ) {
				$new['projify_status']   = __( 'Trạng thái', 'projify-apps' );
				$new['projify_featured'] = __( 'Nổi bật', 'projify-apps' );
				$new['menu_order']       = __( 'Thứ tự', 'projify-apps' );
			}
		}
		unset( $new['date'] );
		return $new;
	}

	public static function column_content( $column, $post_id ) {
		switch ( $column ) {
			case 'projify_card':
				if ( has_post_thumbnail( $post_id ) ) {
					echo get_the_post_thumbnail( $post_id, array( 48, 48 ), array( 'class' => 'projify-thumb' ) );
				} else {
					$g     = Projify_Apps::gradients();
					$i     = (int) get_post_meta( $post_id, Projify_Apps::META_GRADIENT, true );
					$i     = isset( $g[ $i ] ) ? $i : 0;
					$glyph = (string) get_post_meta( $post_id, Projify_Apps::META_GLYPH, true );
					$glyph = '' === $glyph ? Projify_Apps::default_glyph( get_the_title( $post_id ) ) : $glyph;
					printf(
						'<span class="projify-thumb" style="background: linear-gradient(145deg, %1$s, %2$s);">%3$s</span>',
						esc_attr( $g[ $i ][0] ),
						esc_attr( $g[ $i ][1] ),
						esc_html( $glyph )
					);
				}
				break;
			case 'projify_status':
				$s = get_post_meta( $post_id, Projify_Apps::META_STATUS, true );
				if ( Projify_Apps::STATUS_ACTIVE === $s ) {
					echo '<span class="projify-badge is-active">' . esc_html__( 'Hoạt động', 'projify-apps' ) . '</span>';
				} else {
					echo '<span class="projify-badge is-dev">' . esc_html__( 'Đang phát triển', 'projify-apps' ) . '</span>';
				}
				break;
			case 'projify_featured':
				echo '1' === get_post_meta( $post_id, Projify_Apps::META_FEATURED, true ) ? '<span class="dashicons dashicons-star-filled" aria-label="' . esc_attr__( 'Nổi bật', 'projify-apps' ) . '"></span>' : '—';
				break;
			case 'menu_order':
				echo (int) get_post_field( 'menu_order', $post_id );
				break;
		}
	}

	public static function sortable_columns( $columns ) {
		$columns['menu_order'] = 'menu_order';
		return $columns;
	}

	public static function admin_default_order( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() ) {
			return;
		}
		if ( Projify_Apps::CPT === $query->get( 'post_type' ) && ! $query->get( 'orderby' ) ) {
			$query->set( 'orderby', 'menu_order' );
			$query->set( 'order', 'ASC' );
		}
	}

	public static function excerpt_label() {
		$screen = get_current_screen();
		if ( ! $screen || Projify_Apps::CPT !== $screen->post_type ) {
			return;
		}
		?>
		<script>
		document.addEventListener('DOMContentLoaded', function () {
			var h = document.querySelector('#postexcerpt .hndle, #postexcerpt h2');
			if (h) { h.textContent = <?php echo wp_json_encode( __( 'Mô tả ngắn (1 dòng)', 'projify-apps' ) ); ?>; }
			var p = document.querySelector('#postexcerpt .inside p');
			if (p) { p.style.display = 'none'; }
		});
		</script>
		<?php
	}

	public static function admin_css() {
		$screen = get_current_screen();
		if ( ! $screen || Projify_Apps::CPT !== $screen->post_type ) {
			return;
		}
		?>
		<style>
			.projify-swatches{display:flex;gap:10px;flex-wrap:wrap;border:0;margin:0;padding:0}
			.projify-swatch{width:40px;height:40px;border-radius:12px;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;box-shadow:inset 0 0 0 2px rgba(255,255,255,.4)}
			.projify-swatch input{margin:0;accent-color:#fff}
			.projify-thumb{width:48px;height:48px;border-radius:14px;display:inline-flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:16px;object-fit:cover}
			.column-projify_card{width:64px}
			.column-projify_status{width:140px}
			.column-projify_featured,.column-menu_order{width:80px}
			.projify-badge{display:inline-block;padding:3px 10px;border-radius:999px;font-weight:600;font-size:12px}
			.projify-badge.is-dev{background:#EEF2FF;color:#1E3A8A}
			.projify-badge.is-active{background:#DCFCE7;color:#166534}
			.column-projify_featured .dashicons-star-filled{color:#D97706}
		</style>
		<?php
	}
}
