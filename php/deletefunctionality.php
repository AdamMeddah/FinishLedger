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
    $stmt = db()->prepare('DELETE FROM serviceinfo WHERE InvoiceID = :invoiceId');
    $stmt->execute(['invoiceId' => (int) $invoiceId]);

    if ($stmt->rowCount() === 0) {
        json_response(['error' => 'No matching order was found.'], 404);
    }

    json_response(['success' => 'Order deleted successfully.']);
} catch (PDOException $exception) {
    json_response(['error' => 'Unable to delete this order.'], 500);
}
