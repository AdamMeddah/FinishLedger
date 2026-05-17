<?php

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/http.php';
require_once __DIR__ . '/validation.php';

require_post();

$invoiceId = input_value('InvoiceID');
$invoiceError = validate_invoice_id($invoiceId);

if ($invoiceError !== null) {
    client_error('Please fix the highlighted fields.', ['invoiceId' => $invoiceError]);
}

try {
    $stmt = db()->prepare(
        'SELECT InvoiceID, Service_Type, Price, Cost, Client_Name, `Date`
         FROM serviceinfo
         WHERE InvoiceID = :invoiceId'
    );
    $stmt->execute(['invoiceId' => (int) $invoiceId]);
    $order = $stmt->fetch();

    if (!$order) {
        json_response(['error' => 'Order not found.'], 404);
    }

    json_response($order);
} catch (PDOException $exception) {
    json_response(['error' => 'Unable to load this order.'], 500);
}
