<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$heading = function_exists( '__' ) ? call_user_func( '__', 'Need a hand?', 'onlive-wa-order' ) : 'Need a hand?';
$line1   = function_exists( '__' ) ? call_user_func( '__', 'Make sure your number includes the international country code. Example: +447911123456.', 'onlive-wa-order' ) : 'Make sure your number includes the international country code. Example: +447911123456.';
$line2   = function_exists( '__' ) ? call_user_func( '__', 'Buttons can be disabled per product inside the Product editor sidebar.', 'onlive-wa-order' ) : 'Buttons can be disabled per product inside the Product editor sidebar.';
?>
<div class="onlive-wa-tab-card onlive-wa-tab-card--general">
	<h2><?php echo htmlspecialchars( $heading, ENT_QUOTES, 'UTF-8' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></h2>
	<p><?php echo htmlspecialchars( $line1, ENT_QUOTES, 'UTF-8' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
	<p><?php echo htmlspecialchars( $line2, ENT_QUOTES, 'UTF-8' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
</div>
