(function ($) {
	'use strict';

	function getVariations($form) {
		var attributes = {};
		if (!$form || !$form.length) {
			return attributes;
		}

		$form.find('[name^="attribute_"]').each(function () {
			var $field = $(this);
			var name = $field.attr('name').replace('attribute_', '');
			var value = $field.val();
			if (value) {
				attributes[name] = value;
			}
		});

		return attributes;
	}

	function getPayload($button) {
		var context = $button.data('context') || 'product';
		var payload = {
			action: 'onlive_wa_build_message',
			context: context
		};

		if (context === 'product') {
			var $form = $button.closest('form.cart');
			if (!$form.length && $button.data('product')) {
				$form = $('form.cart').filter(function () {
					return $(this).find('input[name="product_id"]').val() === String($button.data('product'));
				}).first();
			}

			payload.product_id = $button.data('product') || ($form.find('input[name="product_id"]').val() || 0);
			payload.variation_id = $form.find('input[name="variation_id"]').val() || 0;
			payload.quantity = $form.find('input[name="quantity"]').val() || 1;
			payload.variations = JSON.stringify(getVariations($form));
		}

		return payload;
	}

	function handleClick(event) {
		if (typeof onliveWAOrder === 'undefined') {
			return;
		}

		event.preventDefault();

		var $button = $(this);
		var payload = getPayload($button);
		
		// Add loading state to button
		$button.addClass('is-loading');
		$button.prop('disabled', true);
		var originalText = $button.text();
		$button.html('<span class="spinner" style="display: inline-block; margin-right: 5px;"></span>Processing...');

		$.ajax({
			type: 'POST',
			url: onliveWAOrder.ajaxUrl,
			data: payload,
			dataType: 'json',
			timeout: 15000,
			success: function (response) {
				if (response && response.success && response.data && response.data.url) {
					window.open(response.data.url, '_blank', 'noopener');
				} else {
					var msg = response.data && response.data.message ? response.data.message : onliveWAOrder.strings.error;
					alert(msg);
				}
			},
			error: function (xhr) {
				var msg = onliveWAOrder.strings.error;
				if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
					msg = xhr.responseJSON.data.message;
				}
				alert(msg);
			},
			complete: function () {
				$button.removeClass('is-loading');
				$button.prop('disabled', false);
				$button.html(originalText);
			}
		});
	}

	// Check admin-ajax.php accessibility on page load
	$(function () {
		if (typeof onliveWAOrder === 'undefined' || !onliveWAOrder.ajaxUrl) {
			return;
		}

		$.ajax({
			type: 'POST',
			url: onliveWAOrder.ajaxUrl,
			data: { action: 'onlive_wa_ping' },
			timeout: 5000
		});
	});

	$(document).on('click', '.onlive-wa-order-button', handleClick);
})(jQuery);
