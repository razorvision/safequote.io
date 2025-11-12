<?php
/**
 * SafeQuote Theme Main Template
 *
 * This template serves as the React application mount point.
 * All routing and rendering is handled by the React application.
 *
 * @package SafeQuote
 */

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
	<?php wp_body_open(); ?>

	<!-- React Application Mount Point -->
	<div id="root"></div>

	<?php wp_footer(); ?>
</body>
</html>
