<?php

const CLIENT_NAME_PATTERN = "/^[A-Za-z][A-Za-z0-9 .,'&-]{1,79}$/";
const SERVICE_TYPE_PATTERN = "/^[A-Za-z][A-Za-z0-9 .,&\/-]{2,79}$/";
const MONEY_PATTERN = '/^\d{1,7}(\.\d{1,2})?$/';
const INVOICE_ID_PATTERN = '/^[1-9]\d{0,9}$/';

function input_value(string $key): string
{
    return trim((string) filter_input(INPUT_POST, $key, FILTER_UNSAFE_RAW));
}

function validate_invoice_id(string $invoiceId): ?string
{
    return preg_match(INVOICE_ID_PATTERN, $invoiceId) ? null : 'Enter a valid invoice ID.';
}

function validate_order_payload(array $payload): array
{
    $errors = [];

    if (!preg_match(CLIENT_NAME_PATTERN, $payload['clientName'] ?? '')) {
        $errors['clientName'] = 'Use 2-80 letters, numbers, spaces, or common punctuation.';
    }

    if (!preg_match(SERVICE_TYPE_PATTERN, $payload['serviceType'] ?? '')) {
        $errors['serviceType'] = 'Use 3-80 letters, numbers, spaces, or service punctuation.';
    }

    foreach (['revenue', 'expense'] as $field) {
        if (!preg_match(MONEY_PATTERN, $payload[$field] ?? '')) {
            $errors[$field] = 'Use dollars with up to two decimals.';
            continue;
        }

        if ((float) $payload[$field] < 0) {
            $errors[$field] = 'Amount must be zero or greater.';
        }
    }

    $date = DateTime::createFromFormat('Y-m-d', $payload['date'] ?? '');
    $dateErrors = DateTime::getLastErrors() ?: ['warning_count' => 0, 'error_count' => 0];
    if (!$date || $dateErrors['warning_count'] > 0 || $dateErrors['error_count'] > 0) {
        $errors['date'] = 'Use a valid order date.';
    }

    return $errors;
}

function normalize_order_payload(array $payload): array
{
    return [
        'clientName' => trim($payload['clientName']),
        'serviceType' => trim($payload['serviceType']),
        'revenue' => number_format((float) $payload['revenue'], 2, '.', ''),
        'expense' => number_format((float) $payload['expense'], 2, '.', ''),
        'date' => trim($payload['date']),
    ];
}

function validate_registration_payload(array $payload): array
{
    $errors = [];

    if (!preg_match('/^[A-Za-z][A-Za-z -]{1,49}$/', $payload['firstName'] ?? '')) {
        $errors['fName'] = 'Enter a valid first name.';
    }

    if (!preg_match('/^[A-Za-z][A-Za-z -]{1,49}$/', $payload['lastName'] ?? '')) {
        $errors['lName'] = 'Enter a valid last name.';
    }

    if (!filter_var($payload['email'] ?? '', FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Enter a valid email address.';
    }

    if (strlen($payload['password'] ?? '') < 8) {
        $errors['password'] = 'Use at least 8 characters.';
    }

    return $errors;
}
