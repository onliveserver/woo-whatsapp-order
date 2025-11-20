# WhatsApp Order Pro Hooks

| Hook | Type | Description |
| --- | --- | --- |
| `onlive_wa_order_message` | Filter | Modify the WhatsApp message before encoding. Receives the message, context, data array, and plugin instance. |
| `onlive_wa_order_endpoint` | Filter | Change the final WhatsApp endpoint URL before redirect. Receives the URL, message, and plugin instance. |
| `onlive_wa_order_button_html` | Filter | Customize the rendered button markup. Receives HTML, context, product ID, args array, and the frontend instance. |
