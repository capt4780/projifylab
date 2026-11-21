<?php
/**
 * Header chung.
 */

defined( 'ABSPATH' ) || exit;
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div class="pj-bg" aria-hidden="true">
	<span class="pj-bg__blob pj-bg__blob--a"></span>
	<span class="pj-bg__blob pj-bg__blob--b"></span>
	<span class="pj-bg__blob pj-bg__blob--c"></span>
</div>

<header class="pj-header">
	<div class="pj-header__bar">
		<a class="pj-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
			<img class="pj-brand__mark" src="<?php echo esc_url( projifylab_logo_url() ); ?>" alt="" width="30" height="30">
			<span class="pj-brand__name"><span>Projify</span> <span class="pj-brand__lab">Lab</span></span>
		</a>
		<?php if ( is_front_page() ) : ?>
			<div class="pj-search" data-pj-search>
				<label class="screen-reader-text" for="pj-search-input"><?php esc_html_e( 'Tìm ứng dụng', 'projifylab' ); ?></label>
				<input class="pj-search__input" id="pj-search-input" type="search" placeholder="<?php esc_attr_e( 'Tìm ứng dụng…', 'projifylab' ); ?>" autocomplete="off" hidden>
				<button type="button" class="pj-icon-btn" data-pj-search-toggle aria-controls="pj-search-input" aria-expanded="false" aria-label="<?php esc_attr_e( 'Tìm ứng dụng', 'projifylab' ); ?>">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true" focusable="false"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
				</button>
			</div>
		<?php endif; ?>
	</div>
</header>
