<?php

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/http.php';
require_once __DIR__ . '/validation.php';

require_post();

$invoiceId = input_value('InvoiceID');
$invoiceError = validate_invoice_id($invoiceId);

$payload = [
    'clientName' => input_value('Client_Name'),
    'serviceType' => input_value('Service_Type'),
    'revenue' => input_value('Price'),
    'expense' => input_value('Cost'),
    'date' => input_value('Date'),
];

$errors = validate_order_payload($payload);
if ($invoiceError !== null) {
    $errors['invoiceId'] = $invoiceError;
}

if ($errors) {
    client_error('Please fix the highlighted fields.', $errors);
}

$payload = normalize_order_payload($payload);

try {
    $stmt = db()->prepare(
        'UPDATE serviceinfo
         SET Service_Type = :serviceType,
             Price = :revenue,
             Cost = :expense,
             Client_Name = :clientName,
             `Date` = :date
         WHERE InvoiceID = :invoiceId'
    );

    $stmt->execute([
        'serviceType' => $payload['serviceType'],
        'revenue' => $payload['revenue'],
        'expense' => $payload['expense'],
        'clientName' => $payload['clientName'],
        'date' => $payload['date'],
        'invoiceId' => (int) $invoiceId,
    ]);

    if ($stmt->rowCount() === 0) {
        json_response(['error' => 'No matching order was found.'], 404);
    }

    json_response(['success' => 'Order updated successfully.']);
} catch (PDOException $exception) {
    json_response(['error' => 'Unable to update this order.'], 500);
}
