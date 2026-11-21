<?php
/**
 * Mẫu chung cho các trang khác (trang, bài viết, 404…).
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main class="pj-main pj-main--page" id="main">
	<div class="pj-page">
		<?php if ( have_posts() ) : ?>
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<article <?php post_class( 'pj-article' ); ?>>
					<?php if ( is_singular() ) : ?>
						<h1 class="pj-article__title"><?php the_title(); ?></h1>
					<?php else : ?>
						<h2 class="pj-article__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
					<?php endif; ?>
					<div class="pj-article__content">
						<?php is_singular() ? the_content() : the_excerpt(); ?>
					</div>
				</article>
			<?php endwhile; ?>
			<?php the_posts_pagination(); ?>
		<?php else : ?>
			<h1 class="pj-article__title"><?php esc_html_e( 'Không tìm thấy trang', 'projifylab' ); ?></h1>
			<p><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Về trang chủ', 'projifylab' ); ?></a></p>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
