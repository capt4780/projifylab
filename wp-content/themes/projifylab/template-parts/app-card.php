<?php
/**
 * Một card mini app (vuông 1:1).
 *
 * @var array $args { app: array, hero: bool }
 */

defined( 'ABSPATH' ) || exit;

$app  = $args['app'];
$hero = ! empty( $args['hero'] );

$classes = array( 'pj-card' );
if ( $hero ) {
	$classes[] = 'pj-card--hero';
}
if ( ! $app['active'] ) {
	$classes[] = 'is-locked';
}
if ( $app['image'] ) {
	$classes[] = 'has-image';
}

$style = sprintf(
	'--g1:%1$s;--g2:%2$s;',
	esc_attr( $app['gradient'][0] ),
	esc_attr( $app['gradient'][1] )
);

$title = $app['title'];
$name  = function_exists( 'mb_strtolower' ) ? mb_strtolower( $title ) : strtolower( $title );
$open  = $app['active'] && $app['url'];

$common = sprintf(
	'class="%1$s" style="%2$s" data-name="%3$s"',
	esc_attr( implode( ' ', $classes ) ),
	$style,
	esc_attr( $name )
);

if ( $open ) {
	printf( '<a %1$s href="%2$s">', $common, esc_url( $app['url'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $common đã escape.
} else {
	printf(
		'<button type="button" %1$s aria-disabled="true" aria-label="%2$s">', // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		$common, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		esc_attr( sprintf( '%s – Đang phát triển', $title ) )
	);
}
?>
	<span class="pj-card__bubble pj-card__bubble--a" aria-hidden="true"></span>
	<?php if ( $hero ) : ?>
		<span class="pj-card__bubble pj-card__bubble--b" aria-hidden="true"></span>
	<?php endif; ?>

	<?php if ( $app['image'] ) : ?>
		<img class="pj-card__img" src="<?php echo esc_url( $app['image'] ); ?>" alt="" loading="<?php echo $hero ? 'eager' : 'lazy'; ?>" decoding="async">
	<?php else : ?>
		<span class="pj-card__glyph" aria-hidden="true"><?php echo esc_html( $app['glyph'] ); ?></span>
	<?php endif; ?>

	<?php if ( $hero ) : ?>
		<span class="pj-card__tag"><?php esc_html_e( 'Nổi bật', 'projifylab' ); ?></span>
	<?php endif; ?>

	<?php if ( ! $app['active'] ) : ?>
		<span class="pj-card__veil" aria-hidden="true"></span>
		<span class="pj-card__lock" aria-hidden="true"><?php echo projifylab_lock_svg(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
	<?php endif; ?>

	<?php if ( $hero ) : ?>
		<span class="pj-card__bar">
			<span class="pj-card__text">
				<span class="pj-card__title"><?php echo esc_html( $title ); ?></span>
				<?php if ( $app['description'] ) : ?>
					<span class="pj-card__desc"><?php echo esc_html( $app['description'] ); ?></span>
				<?php endif; ?>
			</span>
			<?php if ( $open ) : ?>
				<span class="pj-card__cta">
					<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M7 4.5v15l13-7.5z" fill="currentColor"/></svg>
					<?php esc_html_e( 'Mở', 'projifylab' ); ?>
				</span>
			<?php else : ?>
				<span class="pj-card__cta pj-card__cta--locked"><?php esc_html_e( 'Đang phát triển', 'projifylab' ); ?></span>
			<?php endif; ?>
		</span>
	<?php else : ?>
		<span class="pj-card__label">
			<span class="pj-card__title"><?php echo esc_html( $title ); ?></span>
			<?php if ( ! $app['active'] ) : ?>
				<span class="pj-card__status"><?php esc_html_e( 'Đang phát triển', 'projifylab' ); ?></span>
			<?php endif; ?>
		</span>
	<?php endif; ?>
<?php echo $open ? '</a>' : '</button>'; ?>
