<!DOCTYPE html>
<html class="no-js" <?php language_attributes(); ?>>

	<head>

		<meta charset="<?php bloginfo( 'charset' ); ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1.0" >

		<link rel="profile" href="https://gmpg.org/xfn/11">

		<?php wp_head(); ?>

	</head>

	<body <?php body_class(); ?>>

		<?php wp_body_open(); ?>

		<div id="page">

			<a class="sr-only sr-only-focusable" href="#main">Skip to content</a>
			
			<?php get_template_part( 'template-parts/site-announcement' ); ?>

			<?php get_template_part( 'template-parts/site-header' ); ?>

			<div id="main" role="main">
				<div class="header-spacer"></div>
				<div class="inner">
					<div class="container">
						<div class="inner">