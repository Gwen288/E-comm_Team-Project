<?php

/*
 * Order status names and badges, used by the account pages, admin pages and emails.
 */

const CC_ORDER_STATUSES = [
    'pending' => 'Awaiting payment',
    'paid' => 'Order received',
    'processing' => 'Being prepared',
    'out_for_delivery' => 'Out for delivery',
    'delivered' => 'Delivered',
    'cancelled' => 'Cancelled',
    'failed' => 'Payment not completed',
];

/* The statuses the admin can move an order to from each status. */
const CC_ORDER_NEXT_STATUSES = [
    'pending' => ['cancelled'],
    'paid' => ['processing', 'cancelled'],
    'processing' => ['out_for_delivery', 'cancelled'],
    'out_for_delivery' => ['delivered'],
    'delivered' => [],
    'cancelled' => [],
    'failed' => [],
];

/* The steps a paid order goes through, shown to the customer. */
const CC_ORDER_STEPS = ['paid', 'processing', 'out_for_delivery', 'delivered'];

function cc_order_status_label($status)
{
    return CC_ORDER_STATUSES[$status] ?? ucfirst(str_replace('_', ' ', (string) $status));
}

function cc_order_status_class($status)
{
    switch ($status) {
        case 'delivered':
            return 'cc-status cc-status--good';
        case 'cancelled':
        case 'failed':
            return 'cc-status cc-status--bad';
        case 'pending':
            return 'cc-status cc-status--wait';
        default:
            return 'cc-status';
    }
}

/** Order number shown to people, for example CC-000123. */
function cc_order_number($order)
{
    return 'CC-' . str_pad((string) ($order['invoice_no'] ?: $order['order_id']), 6, '0', STR_PAD_LEFT);
}
