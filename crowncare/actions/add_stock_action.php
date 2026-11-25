<?php

/*
 * Inventory: adds units to an existing product (or one of its sizes).
 * Answers with JSON when called from the inventory page script,
 * otherwise goes back to the inventory page with a message.
 */

require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../classes/StockClass.php';

require_admin();

$inventoryPage = BASE_URL . 'views/admin/inventory.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect($inventoryPage);
}

$productId = (int) ($_POST['product_id'] ?? 0);
$sizeId = (int) ($_POST['size_id'] ?? 0);
$qtyText = trim((string) ($_POST['qty'] ?? ''));
$qty = ctype_digit($qtyText) ? (int) $qtyText : 0;

if ($qty < 1 || $qty > 10000) {
    cc_form_error('Enter a whole number from 1 to 10,000.', ['qty' => 'Enter a whole number from 1 to 10,000.'], $inventoryPage);
}

$newStock = (new StockClass())->addStock($productId, $sizeId, $qty);

if ($newStock === false) {
    cc_form_error('We could not find that product. Please reload the page and try again.', [], $inventoryPage);
}

$message = $qty . ' added. ' . $newStock . ' now in stock.';

if (cc_is_ajax()) {
    cc_json(['ok' => true, 'stock' => $newStock, 'message' => $message, 'low' => CC_LOW_STOCK]);
}

$_SESSION['success'] = $message;
redirect($inventoryPage . '#stock-' . $productId . '-' . $sizeId);
