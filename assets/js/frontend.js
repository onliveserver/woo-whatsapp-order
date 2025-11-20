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
			nonce: onliveWAOrder.nonce,
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
		if (!onliveWAOrder.phone) {
			window.alert(onliveWAOrder.strings.phoneMissing);
			return;
		}

		var payload = getPayload($button);
		$button.addClass('is-loading');

		$.post(onliveWAOrder.ajaxUrl, payload)
			.done(function (response) {
				if (response && response.success && response.data && response.data.url) {
					window.open(response.data.url, '_blank', 'noopener');
					return;
				}
				window.alert(onliveWAOrder.strings.error);
			})
			.fail(function (xhr) {
				var message = onliveWAOrder.strings.error;
				if (xhr && xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
					message = xhr.responseJSON.data.message;
				}
				window.alert(message);
			})
			.always(function () {
				$button.removeClass('is-loading');
			});
	}

	$(document).on('click', '.onlive-wa-order-button', handleClick);
})(jQuery);
