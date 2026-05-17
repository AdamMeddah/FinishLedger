<?php

function json_response(array $payload, int $statusCode = 200): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($payload);
    exit;
}

function require_post(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_response(['error' => 'POST requests only.'], 405);
    }
}

function client_error(string $message, array $fields = []): void
{
    json_response(['error' => $message, 'fields' => $fields], 422);
}
