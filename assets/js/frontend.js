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
			console.error('onliveWAOrder object not initialized');
			return;
		}

		event.preventDefault();

		var $button = $(this);
		
		var payload = getPayload($button);
		
		// Add loading state to button
		$button.addClass('is-loading');
		$button.prop('disabled', true);
		
		// Update button text to show loading
		var originalText = $button.text();
		$button.html('<span class="spinner" style="display: inline-block; margin-right: 5px;"></span>Processing...');

		// Log the request for debugging
		console.log('WhatsApp AJAX Request:', {
			url: onliveWAOrder.ajaxUrl,
			action: payload.action,
			data: payload,
			method: 'POST'
		});

		$.ajax({
			type: 'POST',
			url: onliveWAOrder.ajaxUrl,
			data: payload,
			dataType: 'json',
			timeout: 15000, // 15 second timeout
			redirect: 'error', // Don't follow redirects
			success: function (response) {
				console.log('WhatsApp AJAX Success:', response);
				if (response && response.success && response.data && response.data.url) {
					window.open(response.data.url, '_blank', 'noopener');
					return;
				}
				var errorMsg = response.data && response.data.message ? response.data.message : onliveWAOrder.strings.error;
				console.warn('WhatsApp AJAX Error (success=false):', errorMsg);
				window.alert(errorMsg);
			},
			error: function (xhr, status, error) {
				var message = onliveWAOrder.strings.error;
				
				// Log the error for debugging - DETAILED
				console.error('=== WhatsApp AJAX Error ===');
				console.error('Status Code:', xhr.status);
				console.error('Status Text:', xhr.statusText);
				console.error('Error:', error);
				console.error('Response Text:', xhr.responseText);
				console.error('Response JSON:', xhr.responseJSON);
				console.error('Request Payload:', payload);
				console.error('========================');

				// Try to get error message from response - TRY MULTIPLE APPROACHES
				if (xhr.responseJSON && xhr.responseJSON.data) {
					if (typeof xhr.responseJSON.data === 'string') {
						message = xhr.responseJSON.data;
					} else if (xhr.responseJSON.data.message) {
						message = xhr.responseJSON.data.message;
					}
				} else if (xhr.responseText) {
					// Try to parse raw response text
					try {
						var parsed = JSON.parse(xhr.responseText);
						if (parsed.data && parsed.data.message) {
							message = parsed.data.message;
						}
					} catch (e) {
						// Response wasn't JSON, will use status code message
					}
				}

				// Handle specific HTTP status codes - DETAILED MESSAGES
				if (xhr.status === 0) {
					message = 'Network error. Please check your internet connection.';
				} else if (xhr.status === 302 || xhr.status === 301 || xhr.status === 307) {
					message = 'Server redirect detected. Admin-ajax.php is being redirected. Check your .htaccess or server configuration.';
				} else if (xhr.status === 404) {
					message = 'Server error (404). The admin-ajax.php file may not exist or the server is returning a 404 page.';
				} else if (xhr.status === 500) {
					message = 'Server error (500). Check server error logs for details.';
				} else if (xhr.status === 403) {
					message = 'Access forbidden (403). Check file permissions or server security rules.';
				} else if (status === 'timeout') {
					message = 'Request timeout after 15 seconds. The server may be overloaded or unresponsive.';
				}

				window.alert(message);
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

		// Verify admin-ajax.php is accessible
		$.ajax({
			type: 'POST',
			url: onliveWAOrder.ajaxUrl,
			data: { action: 'onlive_wa_ping' },
			timeout: 5000,
			success: function () {
				console.log('WhatsApp Plugin: AJAX endpoint is healthy');
			},
			error: function (xhr) {
				if (xhr.status === 0) {
					console.warn('WhatsApp Plugin: AJAX connectivity issue - network error or CORS issue');
				} else if (xhr.status === 404) {
					console.error('WhatsApp Plugin: AJAX endpoint not found (404). The admin-ajax.php file may not exist or WordPress may not be properly configured.');
				} else {
					console.warn('WhatsApp Plugin: AJAX endpoint returned status ' + xhr.status);
				}
			}
		});
	});

	$(document).on('click', '.onlive-wa-order-button', handleClick);
})(jQuery);
