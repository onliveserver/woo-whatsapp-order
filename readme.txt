=== Onlive WooCommerce WhatsApp Order ===
Contributors: onlive
Requires at least: 6.0
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 1.4.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Adds WhatsApp “Order Now” buttons to WooCommerce product and cart pages with customizable templates, shortcode, and Gutenberg block.

== Description ==
* Add WhatsApp buttons to product and cart pages
* Customize button colors, labels, and size
* Build personalized message templates with live preview
* Choose between wa.me, api.whatsapp.com, or custom gateways
* Disable the button on specific products
* Shortcode `[wa_order_button id="123"]`
* Gutenberg block with live preview

== Installation ==
1. Upload the plugin folder to `/wp-content/plugins/`
2. Activate the plugin via **Plugins → Installed Plugins**
3. Open **WhatsApp Order Pro** in the admin menu, enable the plugin, and enter your WhatsApp number

== Shortcode ==
`[wa_order_button id="123" label="Order this" class="my-button" force="yes"]`

== Hooks ==
See `docs/hooks.md` for available filters.

== Frequently Asked Questions ==
= Does it support variable products? =
Yes. Selected variation attributes are automatically included in the WhatsApp message.

= Can I disable the plugin styles? =
Yes. Use the Design tab to disable the default stylesheet and paste custom CSS.

== Changelog ==
= 1.4.0 =
* Security enhancement: Randomized AJAX action names for better protection
* Updated GitHub repository references
* Added plugin settings link in Plugins page
* Enhanced admin panel with last update check timestamp

= 1.3.0 =
* Removed all authentication (nonce) from WhatsApp message AJAX handler for universal guest/user support
* Improved error handling and admin Force Reinstall tool
* Documentation and codebase cleanup

= 1.0.0 =
* Initial release
