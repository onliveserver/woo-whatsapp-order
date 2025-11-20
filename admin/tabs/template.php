<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$heading = function_exists( '__' ) ? call_user_func( '__', 'Template placeholders', 'onlive-wa-order' ) : 'Template placeholders';
$list    = [
	'{{product_name}}',
	'{{product_price}}',
	'{{product_quantity}}',
	'{{product_variation}}',
	'{{product_sku}}',
	'{{cart_total}}',
	'{{site_name}}',
	'{{customer_name}}',
];
?>
<div class="onlive-wa-tab-card onlive-wa-tab-card--template">
	<h2><?php echo htmlspecialchars( $heading, ENT_QUOTES, 'UTF-8' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></h2>
	<ul>
		<?php foreach ( $list as $item ) : ?>
			<li><code><?php echo htmlspecialchars( $item, ENT_QUOTES, 'UTF-8' ); ?></code></li>
		<?php endforeach; ?>
	</ul>
</div>
