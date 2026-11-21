<?php
/**
 * Banner quảng cáo dạng slide ngang 2:1.
 *
 * @var array $args { banners: array[] }
 */

defined( 'ABSPATH' ) || exit;

$banners = $args['banners'];
$total   = count( $banners );
$multi   = $total > 1;
?>
<section class="pj-banner<?php echo $multi ? ' is-multi' : ''; ?>" aria-roledescription="carousel" aria-label="<?php esc_attr_e( 'Quảng cáo', 'projifylab' ); ?>" data-pj-banner>
	<div class="pj-banner__track" data-pj-track>
		<?php
		foreach ( $banners as $i => $b ) :
			$has_link  = '' !== $b['link'];
			$show_text = $b['show_text'] && ( '' !== $b['title'] || '' !== $b['description'] );
			$tag       = $has_link ? 'a' : 'div';
			$attrs     = sprintf(
				'class="pj-slide%1$s" style="--bg:%2$s" role="group" aria-roledescription="slide" aria-label="%3$s"',
				$b['image'] ? ' has-image' : '',
				esc_attr( $b['background'] ),
				esc_attr( sprintf( '%1$d / %2$d', $i + 1, $total ) )
			);
			if ( $has_link ) {
				$attrs .= sprintf( ' href="%s"', esc_url( $b['link'] ) );
				if ( $b['new_tab'] ) {
					$attrs .= ' target="_blank" rel="noopener"';
				}
			}
			if ( $i > 0 ) {
				$attrs .= ' aria-hidden="true"';
				if ( $has_link ) {
					$attrs .= ' tabindex="-1"';
				}
			}
			?>
			<<?php echo $tag; // phpcs:ignore ?> <?php echo $attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- đã escape ở trên. ?> draggable="false">
				<?php if ( $b['image'] ) : ?>
					<img class="pj-slide__img" src="<?php echo esc_url( $b['image'] ); ?>" alt="<?php echo $show_text ? '' : esc_attr( $b['title'] ); ?>" loading="<?php echo 0 === $i ? 'eager' : 'lazy'; ?>" decoding="async" draggable="false">
				<?php else : ?>
					<span class="pj-slide__deco pj-slide__deco--a" aria-hidden="true"></span>
					<span class="pj-slide__deco pj-slide__deco--b" aria-hidden="true"></span>
					<span class="pj-slide__deco pj-slide__deco--c" aria-hidden="true"></span>
				<?php endif; ?>

				<?php if ( $show_text ) : ?>
					<span class="pj-slide__text">
						<?php if ( '' !== $b['title'] ) : ?>
							<span class="pj-slide__title"><?php echo esc_html( $b['title'] ); ?></span>
						<?php endif; ?>
						<?php if ( '' !== $b['description'] ) : ?>
							<span class="pj-slide__desc"><?php echo esc_html( $b['description'] ); ?></span>
						<?php endif; ?>
						<?php if ( $has_link ) : ?>
							<span class="pj-slide__cta"><?php echo esc_html( $b['cta'] ); ?></span>
						<?php endif; ?>
					</span>
				<?php endif; ?>
			</<?php echo $tag; // phpcs:ignore ?>>
		<?php endforeach; ?>
	</div>

	<span class="pj-banner__tag"><?php esc_html_e( 'Quảng cáo', 'projifylab' ); ?></span>

	<?php if ( $multi ) : ?>
		<span class="pj-banner__count" aria-hidden="true" data-pj-count>1/<?php echo (int) $total; ?></span>
		<div class="pj-banner__arrows">
			<button type="button" class="pj-banner__arrow" data-pj-prev aria-label="<?php esc_attr_e( 'Quảng cáo trước', 'projifylab' ); ?>">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M15 6l-6 6 6 6"/></svg>
			</button>
			<button type="button" class="pj-banner__arrow" data-pj-next aria-label="<?php esc_attr_e( 'Quảng cáo sau', 'projifylab' ); ?>">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M9 6l6 6-6 6"/></svg>
			</button>
		</div>
		<div class="pj-banner__dots" role="group" aria-label="<?php esc_attr_e( 'Chọn quảng cáo', 'projifylab' ); ?>">
			<?php for ( $i = 0; $i < $total; $i++ ) : ?>
				<button type="button" class="pj-banner__dot" data-pj-dot="<?php echo (int) $i; ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Quảng cáo %d', 'projifylab' ), $i + 1 ) ); ?>"<?php echo 0 === $i ? ' aria-current="true"' : ''; ?>><span></span></button>
			<?php endfor; ?>
		</div>
	<?php endif; ?>
</section>
