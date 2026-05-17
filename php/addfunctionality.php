<?php

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/http.php';
require_once __DIR__ . '/validation.php';

require_post();

$payload = [
    'clientName' => input_value('clientName'),
    'serviceType' => input_value('jobType'),
    'revenue' => input_value('revenue'),
    'expense' => input_value('expense'),
    'date' => input_value('date'),
];

$errors = validate_order_payload($payload);
if ($errors) {
    client_error('Please fix the highlighted fields.', $errors);
}

$payload = normalize_order_payload($payload);
$profit = (float) $payload['revenue'] - (float) $payload['expense'];

try {
    $stmt = db()->prepare(
        'INSERT INTO serviceinfo (Service_Type, Price, Cost, Client_Name, `Date`)
         VALUES (:serviceType, :revenue, :expense, :clientName, :date)'
    );

    $stmt->execute([
        'serviceType' => $payload['serviceType'],
        'revenue' => $payload['revenue'],
        'expense' => $payload['expense'],
        'clientName' => $payload['clientName'],
        'date' => $payload['date'],
    ]);

    json_response([
        'success' => 'Order saved successfully.',
        'invoiceId' => (int) db()->lastInsertId(),
        'profit' => number_format($profit, 2, '.', ''),
        'profitFormatted' => '$' . number_format($profit, 2),
    ], 201);
} catch (PDOException $exception) {
    json_response(['error' => 'Unable to save this order.'], 500);
}
