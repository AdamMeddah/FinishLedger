<?php

session_start();

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/validation.php';

function redirect_with_message(string $type, string $message): void
{
    $_SESSION[$type] = $message;
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

if (isset($_POST['signUp'])) {
    $payload = [
        'firstName' => trim((string) ($_POST['fName'] ?? '')),
        'lastName' => trim((string) ($_POST['lName'] ?? '')),
        'email' => trim((string) ($_POST['email'] ?? '')),
        'password' => (string) ($_POST['password'] ?? ''),
    ];

    $errors = validate_registration_payload($payload);
    if ($errors) {
        redirect_with_message('error', reset($errors));
    }

    try {
        $existing = db()->prepare('SELECT Id FROM users WHERE email = :email LIMIT 1');
        $existing->execute(['email' => $payload['email']]);

        if ($existing->fetch()) {
            redirect_with_message('error', 'Email address already exists.');
        }

        $insert = db()->prepare(
            'INSERT INTO users (firstName, lastName, email, password)
             VALUES (:firstName, :lastName, :email, :password)'
        );
        $insert->execute([
            'firstName' => $payload['firstName'],
            'lastName' => $payload['lastName'],
            'email' => $payload['email'],
            'password' => password_hash($payload['password'], PASSWORD_DEFAULT),
        ]);

        redirect_with_message('success', 'Account created successfully. You can sign in now.');
    } catch (PDOException $exception) {
        redirect_with_message('error', 'Account creation failed.');
    }
}

if (isset($_POST['signIn'])) {
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
        redirect_with_message('error', 'Enter a valid email and password.');
    }

    try {
        $stmt = db()->prepare(
            'SELECT Id, firstName, lastName, email, password
             FROM users
             WHERE email = :email
             LIMIT 1'
        );
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            redirect_with_message('error', 'Incorrect email or password.');
        }

        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => (int) $user['Id'],
            'firstName' => $user['firstName'],
            'lastName' => $user['lastName'],
            'email' => $user['email'],
        ];

        header('Location: ../index.html');
        exit;
    } catch (PDOException $exception) {
        redirect_with_message('error', 'Sign in failed.');
    }
}

header('Location: login.php');
exit;
