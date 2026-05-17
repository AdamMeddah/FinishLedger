<?php
session_start();

$error = $_SESSION['error'] ?? null;
$success = $_SESSION['success'] ?? null;
unset($_SESSION['error'], $_SESSION['success']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In | FinishLedger</title>
    <link rel="stylesheet" href="../css/login.css">
</head>
<body>
    <?php if ($error): ?>
        <p class="flash error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <?php if ($success): ?>
        <p class="flash success"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <div class="container" id="signup" style="display:none">
        <h1 class="form-title">Create Account</h1>
        <form method="post" action="register.php">
            <div class="input-group">
                <label for="fName">First Name</label>
                <input type="text" name="fName" id="fName" placeholder="First Name" pattern="[A-Za-z][A-Za-z -]{1,49}" required>
            </div>
            <div class="input-group">
                <label for="lName">Last Name</label>
                <input type="text" name="lName" id="lName" placeholder="Last Name" pattern="[A-Za-z][A-Za-z -]{1,49}" required>
            </div>
            <div class="input-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" placeholder="email" required>
            </div>
            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" placeholder="password" minlength="8" required>
            </div>
            <input type="submit" class="btn" value="Sign Up" name="signUp">
        </form>
        <div class="links">
            <p>Already have an account?</p>
            <button id="signInButton">Sign In</button>
        </div>
    </div>

    <div class="container" id="signIn">
        <h1 class="form-title">Sign In</h1>
        <form method="post" action="register.php">
            <div class="input-group">
                <label for="loginEmail">Email</label>
                <input type="email" name="email" id="loginEmail" placeholder="email" required>
            </div>
            <div class="input-group">
                <label for="loginPassword">Password</label>
                <input type="password" name="password" id="loginPassword" placeholder="password" required>
            </div>
            <input type="submit" class="btn" value="Sign In" name="signIn">
        </form>
        <div class="links">
            <p>Need an account?</p>
            <button id="signUpButton">Sign Up</button>
        </div>
    </div>
    <script src="../js/login.js"></script>
</body>
</html>
