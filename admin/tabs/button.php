<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$heading = function_exists( '__' ) ? call_user_func( '__', 'Design tip', 'onlive-wa-order' ) : 'Design tip';
$text    = function_exists( '__' ) ? call_user_func( '__', 'Use consistent button colors that match your brand. You can also override the styles completely via the Design tab.', 'onlive-wa-order' ) : 'Use consistent button colors that match your brand. You can also override the styles completely via the Design tab.';
?>
<div class="onlive-wa-tab-card onlive-wa-tab-card--button">
	<h2><?php echo htmlspecialchars( $heading, ENT_QUOTES, 'UTF-8' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></h2>
	<p><?php echo htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
</div>
