<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$title = function_exists( '__' ) ? call_user_func( '__', 'Styling control', 'onlive-wa-order' ) : 'Styling control';
$note  = function_exists( '__' ) ? call_user_func( '__', 'Disable the built-in stylesheet if your theme already has button styles, then paste overrides into the custom CSS box.', 'onlive-wa-order' ) : 'Disable the built-in stylesheet if your theme already has button styles, then paste overrides into the custom CSS box.';
?>
<div class="onlive-wa-tab-card onlive-wa-tab-card--design">
	<h2><?php echo htmlspecialchars( $title, ENT_QUOTES, 'UTF-8' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></h2>
	<p><?php echo htmlspecialchars( $note, ENT_QUOTES, 'UTF-8' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
</div>
