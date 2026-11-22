<?php

/*
 * Order status names and badges, used by the account pages, admin pages and emails.
 * core/core.php loads this so every page has the same wording.
 */

/* ---------- Status names and what the admin can change ---------- */

const CC_ORDER_STATUSES = [
    'pending' => 'Awaiting payment',
    'paid' => 'Order received',
    'processing' => 'Being prepared',
    'out_for_delivery' => 'Out for delivery',
    'delivered' => 'Delivered',
    'cancelled' => 'Cancelled',
    'failed' => 'Payment not completed',
];

/* The statuses the admin can move an order to from each status.
   Delivered, cancelled and failed are the end of the line, hence empty lists. */
const CC_ORDER_NEXT_STATUSES = [
    'pending' => ['cancelled'],
    'paid' => ['processing', 'cancelled'],
    'processing' => ['out_for_delivery', 'cancelled'],
    'out_for_delivery' => ['delivered'],
    'delivered' => [],
    'cancelled' => [],
    'failed' => [],
];

/* The steps a paid order goes through, shown to the customer.
   Pending and failed orders sit outside this list. */
const CC_ORDER_STEPS = ['paid', 'processing', 'out_for_delivery', 'delivered'];

/* ---------- Labels, badge colours and the number shown to people ---------- */

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

/**
 * Order number shown to people, for example CC-000123.
 * invoice_no starts at 0 and is filled in after insert, so we fall back to
 * the database ID for rows caught in between (or with no invoice number).
 */
function cc_order_number($order)
{
    return 'CC-' . str_pad((string) ($order['invoice_no'] ?: $order['order_id']), 6, '0', STR_PAD_LEFT);
}
