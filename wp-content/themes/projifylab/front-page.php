<?php
/**
 * Trang chủ: launcher các mini web app.
 */

defined( 'ABSPATH' ) || exit;

get_header();

$apps = projifylab_get_apps();
?>
<main class="pj-main" id="main">
	<h1 class="screen-reader-text"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></h1>

	<?php if ( null === $apps ) : ?>
		<div class="pj-empty">
			<p><?php esc_html_e( 'Chưa bật plugin Projify Apps.', 'projifylab' ); ?></p>
			<?php if ( current_user_can( 'activate_plugins' ) ) : ?>
				<p><a href="<?php echo esc_url( admin_url( 'plugins.php' ) ); ?>"><?php esc_html_e( 'Vào trang Plugin để bật', 'projifylab' ); ?></a></p>
			<?php endif; ?>
		</div>
	<?php elseif ( empty( $apps ) ) : ?>
		<div class="pj-empty">
			<p><?php esc_html_e( 'Chưa có mini app nào.', 'projifylab' ); ?></p>
			<?php if ( current_user_can( 'edit_posts' ) ) : ?>
				<p><a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=projify_app' ) ); ?>"><?php esc_html_e( 'Thêm mini app', 'projifylab' ); ?></a></p>
			<?php endif; ?>
		</div>
	<?php else : ?>
		<?php
		$banners = projifylab_get_banners();
		$hero    = ( ! empty( $apps[0]['featured'] ) ) ? $apps[0] : null;
		$rest    = $hero ? array_slice( $apps, 1 ) : $apps;
		?>
		<?php if ( $banners ) : ?>
			<div class="pj-top<?php echo $hero ? '' : ' pj-top--banner-only'; ?>" data-pj-grid>
				<?php
				if ( $hero ) {
					projifylab_render_card( $hero, true );
				}
				get_template_part( 'template-parts/banner-slider', null, array( 'banners' => $banners ) );
				?>
			</div>
			<div class="pj-grid" data-pj-grid>
				<?php
				foreach ( $rest as $app ) {
					projifylab_render_card( $app, false );
				}
				?>
			</div>
		<?php else : ?>
			<div class="pj-grid pj-grid--legacy" data-pj-grid>
				<?php
				foreach ( $apps as $app ) {
					projifylab_render_card( $app, ! empty( $app['featured'] ) );
				}
				?>
			</div>
		<?php endif; ?>
		<p class="pj-empty pj-empty--search" data-pj-noresult hidden><?php esc_html_e( 'Không tìm thấy ứng dụng phù hợp.', 'projifylab' ); ?></p>
	<?php endif; ?>
</main>
<?php
get_footer();
