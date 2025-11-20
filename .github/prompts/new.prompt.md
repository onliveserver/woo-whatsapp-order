---
agent: agent
---
📌 Plugin Details

Plugin Name: Onlive WooCommerce WhatsApp Order

Description: Adds WhatsApp “Order Now” button on product and cart pages with full admin settings, template builder, and toggle controls.

Compatibility:

WordPress latest version

WooCommerce latest version

PHP 7.4+

Security:

Use proper sanitization (sanitize_text_field, esc_html, esc_url, nonce).

Use WordPress Settings API.

No deprecated hooks.

📌 Admin Dashboard (Custom Menu)

Create an Admin Menu → WhatsApp Order Pro
Inside the menu, create Multiple Tabs using subpages or tab navigation:

1️⃣ General Settings

Field: Enable/Disable plugin (checkbox)

Field: WhatsApp Number (with country code validation)

Field: Button Position

Single Product Page (checkbox)

Cart Page (checkbox)

2️⃣ Button Settings

Button label for Single Product

Button label for Cart Page

Button color picker

Button text color

Button size (small, medium, large)

3️⃣ Message Template Settings

Enable/Disable custom WhatsApp template

Template builder with variables:

{{product_name}}

{{product_price}}

{{product_quantity}}

{{product_variation}}

{{cart_total}}

{{site_name}}

{{customer_name}} (if logged in)

Textarea for template

Live preview of final message

4️⃣ WhatsApp API Settings

Allow user to select which sending system to use:

Option A: Use wa.me

Option B: Use WhatsApp API (https://api.whatsapp.com/send?phone=)

Option C: Custom gateway URL (input field)

If custom gateway selected:

Add field for query parameter for message formatting

5️⃣ Design Settings

Toggle to load plugin CSS or allow theme to style

Custom CSS input box

📌 Frontend Features
✔ WhatsApp Order Button on Single Product Page

Should work with:

simple products

variable products

When product has variations:

Auto include selected variation attributes in message

Button message example:
"Hello, I want to order Product Name (Color: Red, Size: L). Price: $20"

✔ WhatsApp Order Button on Cart Page

Include all cart items

Add totals, quantities, variations

Build the message using admin template

Example:

New Order from {{site_name}}

Items:
1x Product A (Size: M) - $20
2x Product B - $35

Total: $55

✔ Works with AJAX add-to-cart

No conflict with themes that use ajax

📌 Template Rendering Logic

Parse template placeholders

Replace variables with dynamic WooCommerce values

Encode message using rawurlencode()

📌 Code Requirements

Use object-oriented PHP

Create a main class file

Structure plugin:

/woo-whatsapp-order-pro/
    woo-whatsapp-order-pro.php
    /admin/
        settings-page.php
        tabs/
           general.php
           button.php
           template.php
           api.php
           design.php
    /frontend/
        class-frontend.php
    /assets/
        css/style.css
        js/scripts.js

📌 Additional Features
✔ Shortcode

Create shortcode:
[wa_order_button id="123"]

✔ Gutenberg Block

Block with customizable label + preview

✔ Disable for specific products

Checkbox inside product edit page:

Disable WhatsApp button for this product

📌 Deliverables

Full plugin ZIP with documentation

Readme.txt with complete plugin usage

Proper internationalization: __() and _e()

Hooks list for developers

Clean commented code

✅ Final Output Request

Generate the complete plugin code following all specifications above, including:

PHP files

Admin UI

Shortcodes

CSS/JS

Full sanitization

WhatsApp template parser

WooCommerce integration