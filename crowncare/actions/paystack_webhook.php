<?php

/*
 * Paystack webhook. Paystack calls this address when a payment succeeds, so an
 * order or a consultation booking is confirmed even if the customer closes the
 * browser before returning to CrownCare. It also records when a consultation
 * refund has been paid out (refund.processed).
 *
 * Set it up once in the Paystack dashboard: Settings > API Keys & Webhooks >
 * Test Webhook URL, for example
 * http://169.239.251.102:442/~petra.asima/e-com_team_project/crowncare/actions/paystack_webhook.php
 *
 * Every call is checked with the secret key (X-Paystack-Signature), so other
 * sites can't fake a payment.
 */

// Paystack doesn't send our form token; the signature check replaces it.
define('CC_SKIP_CSRF', true);

require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/OrderController.php';
require_once __DIR__ . '/../classes/ConsultationClass.php';
require_once __DIR__ . '/../classes/ConsultationPayment.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] ?? '';

if (!paystack_is_configured()
    || $signature === ''
    || !hash_equals(hash_hmac('sha512', $payload, PAYSTACK_SECRET_KEY), $signature)) {
    http_response_code(401);
    exit;
}

$event = json_decode($payload, true);

// Reply straight away; Paystack only needs to know the call arrived.
http_response_code(200);

$data = $event['data'] ?? [];

// A consultation refund has been paid out.
if (($event['event'] ?? '') === 'refund.processed') {
    $refunded = (string) ($data['transaction_reference'] ?? ($data['transaction']['reference'] ?? ''));
    if (ConsultationPayment::isConsultationReference($refunded)) {
        (new ConsultationClass())->markRefundProcessed($refunded);
    }
    exit;
}

if (($event['event'] ?? '') !== 'charge.success') {
    exit;
}

$reference = (string) ($data['reference'] ?? '');

// Consultation payments.
if (ConsultationPayment::isConsultationReference($reference)) {
    $booking = (new ConsultationClass())->getByPaymentReference($reference);
    if ($booking && $booking['payment_status'] === 'unpaid') {
        ConsultationPayment::confirm($booking, $data);
    } elseif (!$booking) {
        error_log('Paystack webhook: no consultation for reference ' . $reference);
    }
    exit;
}

$orderController = new OrderController();
$order = $reference !== '' ? $orderController->getOrderByReference($reference) : false;

if (!$order) {
    error_log('Paystack webhook: no order for reference ' . $reference);
    exit;
}

$expectedAmount = paystack_amount($order['total_amount']);
$paidAmount = (int) ($data['amount'] ?? 0);
$currency = strtoupper((string) ($data['currency'] ?? ''));

if (($data['status'] ?? '') !== 'success'
    || $paidAmount !== $expectedAmount
    || $currency !== strtoupper(PAYSTACK_CURRENCY)) {
    error_log('Paystack webhook: amount or status did not match for reference ' . $reference);
    exit;
}

$paidAt = !empty($data['paid_at']) && strtotime($data['paid_at']) !== false
    ? date('Y-m-d H:i:s', strtotime($data['paid_at']))
    : date('Y-m-d H:i:s');

$orderController->completePayment($order, $reference, $data['channel'] ?? null, $paidAt);
