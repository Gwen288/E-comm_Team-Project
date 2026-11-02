<?php

/**
 * Minimal Paystack API client for the CrownCare checkout flow.
 *
 * Uses the server-side secret key only. Card details are collected by
 * Paystack's hosted checkout page and are never stored by CrownCare.
 */

define('PAYSTACK_API_BASE', 'https://api.paystack.co');


/**
 * Check whether usable Paystack keys have been configured.
 */
function paystack_is_configured()
{
    if (!defined('PAYSTACK_SECRET_KEY') || PAYSTACK_SECRET_KEY === '') {
        return false;
    }

    if (strpos(PAYSTACK_SECRET_KEY, 'your_secret_key') !== false) {
        return false;
    }

    if (
        strpos(PAYSTACK_SECRET_KEY, 'sk_test_') !== 0 &&
        strpos(PAYSTACK_SECRET_KEY, 'sk_live_') !== 0
    ) {
        return false;
    }

    if (
        defined('PAYSTACK_MODE') &&
        PAYSTACK_MODE === 'test' &&
        strpos(PAYSTACK_SECRET_KEY, 'sk_test_') !== 0
    ) {
        return false;
    }

    return true;
}


/**
 * Send a request to the Paystack API.
 *
 * @return array Decoded response, or an array with status=false on failure.
 */
function paystack_request($method, $path, $payload = null)
{
    if (!paystack_is_configured()) {
        return [
            'status' => false,
            'message' => 'Paystack test keys are not configured. Add them to core/paystack_config.php.'
        ];
    }

    $ch = curl_init(PAYSTACK_API_BASE . $path);

    $headers = [
        'Authorization: Bearer ' . PAYSTACK_SECRET_KEY,
        'Content-Type: application/json',
        'Accept: application/json'
    ];

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 30
    ]);

    if (strtoupper($method) === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload ?? []));
    }

    $response = curl_exec($ch);
    $curlError = curl_error($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    if ($response === false) {
        error_log('Paystack cURL error: ' . $curlError);

        return [
            'status' => false,
            'message' => 'Could not reach Paystack. Please try again.'
        ];
    }

    $data = json_decode($response, true);

    if (!is_array($data)) {
        error_log('Paystack returned an invalid response: ' . $response);

        return [
            'status' => false,
            'message' => 'Invalid response from Paystack.'
        ];
    }

    if ($httpCode >= 400) {
        error_log(
            'Paystack HTTP ' . $httpCode . ': '
            . ($data['message'] ?? 'Unknown error')
        );
    }

    return $data;
}


/**
 * Initialise a Paystack transaction.
 *
 * @param string $email       Customer email address.
 * @param int    $amount      Amount in the smallest currency unit (pesewas).
 * @param string $reference   Unique transaction reference.
 * @param string $callbackUrl URL Paystack redirects to after payment.
 * @param array  $metadata    Optional metadata.
 */
function paystack_initialize(
    $email,
    $amount,
    $reference,
    $callbackUrl,
    $metadata = []
) {
    return paystack_request('POST', '/transaction/initialize', [
        'email' => $email,
        'amount' => $amount,
        'currency' => PAYSTACK_CURRENCY,
        'reference' => $reference,
        'callback_url' => $callbackUrl,
        'metadata' => $metadata
    ]);
}


/**
 * Verify a Paystack transaction by reference.
 */
function paystack_verify($reference)
{
    return paystack_request(
        'GET',
        '/transaction/verify/' . rawurlencode($reference)
    );
}


/**
 * Convert a Ghana cedi amount to pesewas for Paystack.
 */
function paystack_amount($amount)
{
    return (int) round((float) $amount * 100);
}


/**
 * Build a unique payment reference for an order.
 */
function paystack_reference($orderId)
{
    return 'CC_' . (int) $orderId . '_' . time() . '_'
        . bin2hex(random_bytes(4));
}
