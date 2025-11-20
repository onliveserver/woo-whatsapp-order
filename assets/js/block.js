(function (blocks, element, components, i18n) {
	'use strict';

	var __ = i18n.__;
	var registerBlockType = blocks.registerBlockType;
	var el = element.createElement;
	var Fragment = element.Fragment;
	var TextControl = components.TextControl;

	registerBlockType('onlive/wa-order-button', {
		title: __('WhatsApp Order Button', 'onlive-wa-order'),
		icon: 'whatsapp',
		category: 'widgets',
		attributes: {
			productId: {
				type: 'number',
				default: 0
			},
			label: {
				type: 'string',
				default: ''
			}
		},
		edit: function (props) {
			var attributes = props.attributes;
			return el(
				Fragment,
				null,
				el(TextControl, {
					label: __('Product ID', 'onlive-wa-order'),
					type: 'number',
					value: attributes.productId || '',
					onChange: function (value) {
						props.setAttributes({ productId: parseInt(value, 10) || 0 });
					}
				}),
				el(TextControl, {
					label: __('Button label', 'onlive-wa-order'),
					value: attributes.label,
					onChange: function (value) {
						props.setAttributes({ label: value });
					}
				}),
				el('div', { className: 'onlive-wa-block-button' }, attributes.label || __('Order via WhatsApp', 'onlive-wa-order'))
			);
		},
		save: function () {
			return null;
		}
	});
})(window.wp.blocks, window.wp.element, window.wp.components, window.wp.i18n);
