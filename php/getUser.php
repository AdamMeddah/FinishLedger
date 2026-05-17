<?php

session_start();

require_once __DIR__ . '/http.php';

if (!isset($_SESSION['user'])) {
    json_response(['authenticated' => false]);
}

json_response([
    'authenticated' => true,
    'firstName' => $_SESSION['user']['firstName'],
    'lastName' => $_SESSION['user']['lastName'],
    'email' => $_SESSION['user']['email'],
]);
