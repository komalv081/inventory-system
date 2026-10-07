<?php

return [
    'message_template' => env(
        'PURCHASE_ORDER_MESSAGE_TEMPLATE',
        "Hello {supplier_name},\n\nI would like to order the following products:\n\n{product_list}\nPlease confirm availability.\n\nThank you,\n{shop_name}"
    ),
];