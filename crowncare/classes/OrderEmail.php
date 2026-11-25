<?php

require_once __DIR__ . '/../core/email_layout.php';

/*
 * Emails about orders: the receipt after payment, and status updates.
 * $order is an order row joined with the customer (getOrderWithCustomer).
 */
class OrderEmail
{
    public static function sendConfirmation(array $order, array $items)
    {
        $number = cc_order_number($order);
        $firstName = self::firstName($order);

        return self::send($order, 'Your CrownCare order ' . $number, [
            'label' => 'Order received',
            'title' => 'Thank you for your order',
            'intro' => 'Hello ' . $firstName . ', we have received your payment and your order is being prepared. We will email you when it is out for delivery.',
            'highlight' => ['Order', $number],
            'lines' => self::lines($items),
            'details' => self::totals($order) + self::delivery($order),
            'button' => self::button($order),
        ]);
    }

    public static function sendStatusUpdate(array $order, array $items)
    {
        $number = cc_order_number($order);
        $status = $order['order_status'];
        $firstName = self::firstName($order);

        $messages = [
            'processing' => ['Your order is being prepared', 'Hello ' . $firstName . ', we are preparing your order. We will email you again when it leaves our studio.'],
            'out_for_delivery' => ['Your order is on its way', 'Hello ' . $firstName . ', your order is out for delivery. Our rider will call you on ' . $order['delivery_phone'] . ' when they arrive.'],
            'delivered' => ['Your order has been delivered', 'Hello ' . $firstName . ', your order has been delivered. We hope you enjoy your products.'],
            'cancelled' => ['Your order has been cancelled', 'Hello ' . $firstName . ', your order has been cancelled. If you paid, your refund will be sent to the same card or mobile money account within 5 to 10 working days.'],
        ];

        if (!isset($messages[$status])) {
            return false;
        }

        [$title, $intro] = $messages[$status];

        return self::send($order, $title . ' (' . $number . ')', [
            'label' => cc_order_status_label($status),
            'title' => $title,
            'intro' => $intro,
            'highlight' => ['Order', $number],
            'lines' => self::lines($items),
            'details' => self::totals($order),
            'button' => self::button($order),
        ]);
    }

    private static function send(array $order, $subject, array $email)
    {
        $to = $order['customer_email'] ?? '';
        if ($to === '' || ($order['customer_status'] ?? 'active') !== 'active') {
            return false;
        }
        return cc_email_send($to, $order['customer_name'] ?? '', $subject, $email);
    }

    private static function firstName(array $order)
    {
        $name = $order['delivery_name'] ?: ($order['customer_name'] ?? '');
        return strtok((string) $name, ' ') ?: 'there';
    }

    private static function lines(array $items)
    {
        $lines = [];
        foreach ($items as $item) {
            $label = $item['product_title'];
            if (!empty($item['size_name'])) {
                $label .= ' (' . cc_size_label($item['size_name']) . ')';
            }
            $label .= ' x ' . (int) $item['qty'];
            $lines[] = [$label, cc_money((int) $item['qty'] * (float) $item['product_price'])];
        }
        return $lines;
    }

    private static function totals(array $order)
    {
        $rows = ['Subtotal' => cc_money($order['subtotal'])];
        if ((float) $order['delivery_fee'] > 0) {
            $rows['Delivery'] = cc_money($order['delivery_fee']);
        }
        $rows['Total paid'] = cc_money($order['total_amount']);
        return $rows;
    }

    private static function delivery(array $order)
    {
        return [
            'Deliver to' => trim($order['delivery_name'] . ', ' . $order['delivery_phone'], ', '),
            'Address' => trim($order['delivery_address'] . ', ' . $order['delivery_city'], ', '),
        ];
    }

    /** Signed-in customers get a link to the order; guests have no account to open. */
    private static function button(array $order)
    {
        if (empty($order['customer_id'])) {
            return null;
        }
        return ['View your order', self::orderUrl($order)];
    }

    private static function orderUrl(array $order)
    {
        return absolute_url(BASE_URL . 'views/account/order.php?id=' . (int) $order['order_id']);
    }
}
