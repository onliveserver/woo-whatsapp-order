<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$title = function_exists( '__' ) ? call_user_func( '__', 'WhatsApp endpoints', 'onlive-wa-order' ) : 'WhatsApp endpoints';
$note  = function_exists( '__' ) ? call_user_func( '__', 'Choose between wa.me, api.whatsapp.com or provide your own gateway URL.', 'onlive-wa-order' ) : 'Choose between wa.me, api.whatsapp.com or provide your own gateway URL.';
?>
<div class="onlive-wa-tab-card onlive-wa-tab-card--api">
	<h2><?php echo htmlspecialchars( $title, ENT_QUOTES, 'UTF-8' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></h2>
	<p><?php echo htmlspecialchars( $note, ENT_QUOTES, 'UTF-8' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
</div>
