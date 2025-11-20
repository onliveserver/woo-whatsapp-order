(function ($) {
	'use strict';

	function initColorPickers() {
		if ($.fn.wpColorPicker) {
			$('.onlive-wa-color-field').wpColorPicker();
		}
	}

	function sampleData() {
		return {
			'{{product_name}}': 'Sample Hoodie',
			'{{product_price}}': '$49',
			'{{product_quantity}}': '2',
			'{{product_variation}}': 'Color: Black, Size: L',
			'{{cart_total}}': '$129',
			'{{site_name}}': document.title,
			'{{customer_name}}': 'John Doe'
		};
	}

	function refreshPreview() {
		var $textarea = $('#onlive-wa-message-template');
		var $preview = $('#onlive-wa-template-preview code');
		if (!$textarea.length || !$preview.length) {
			return;
		}

		var content = $textarea.val() || '';
		var placeholders = sampleData();

		Object.keys(placeholders).forEach(function (token) {
			content = content.replace(new RegExp(token, 'g'), placeholders[token]);
		});

		if (!content.trim()) {
			content = 'Hello, I want to order Sample Hoodie';
		}

		$preview.text(content);
	}

	$(function () {
		initColorPickers();
		refreshPreview();
		$(document).on('input change', '#onlive-wa-message-template', refreshPreview);
	});
})(jQuery);
