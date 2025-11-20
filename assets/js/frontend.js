(function ($) {
	'use strict';

	/**
	 * Extract variation attributes from form
	 */
	function getVariations($form) {
		var attributes = {};
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

	/**
	 * Build AJAX request payload
	 */
	function getPayload($button) {
		var context = $button.data('context') || 'product';
		var payload = {
			action: 'onlive_wa_build_message',
			context: context
		};

		if (context === 'product') {
			var $form = $button.closest('form.cart');
			payload.product_id = $button.data('product') || ($form.length ? $form.find('input[name="product_id"]').val() : 0);
			payload.variation_id = $form.length ? $form.find('input[name="variation_id"]').val() : 0;
			payload.quantity = $form.length ? $form.find('input[name="quantity"]').val() : 1;
			payload.variations = JSON.stringify(getVariations($form));
		}

		return payload;
	}

	/**
	 * Handle button click
	 */
	function handleClick(event) {
		event.preventDefault();
		var $button = $(this);

		// Show loading state
		$button.prop('disabled', true);
		var originalText = $button.html();
		$button.html('⏳ Loading...');

		// Send request
		$.ajax({
			type: 'POST',
			url: onliveWAOrder.ajaxUrl,
			data: getPayload($button),
			dataType: 'json',

			success: function (response) {
				if (response.success && response.data && response.data.url) {
					window.open(response.data.url, '_blank');
				} else {
					showError(response.message || 'An error occurred');
				}
			},

			error: function () {
				showError('Request failed - please try again');
			},

			complete: function () {
				// Restore button
				$button.prop('disabled', false);
				$button.html(originalText);
			}
		});
	}

	/**
	 * Show error message to user
	 */
	function showError(message) {
		alert('WhatsApp Error: ' + message);
	}

	// Initialize on document ready
	$(document).ready(function () {
		$(document).on('click', '.onlive-wa-order-button', handleClick);
	});

})(jQuery);
