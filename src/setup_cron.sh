#!/bin/bash
# This script should set up a CRON job to run cron.php every 5 minutes.
# You need to implement the CRON setup logic here.
<?php
require_once __DIR__ . '/functions.php';
session_start();

// Enable full error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Step 1: User submits email to unsubscribe
    if (isset($_POST['unsubscribe_email']) && isset($_POST['submit-unsubscribe'])) {
        $email = filter_var(trim($_POST['unsubscribe_email']), FILTER_VALIDATE_EMAIL);

        if ($email) {
            $code = generateVerificationCode();
            $_SESSION['unsubscribe_email'] = $email;
            $_SESSION['unsubscribe_code'] = $code;

            if (sendUnsubscribeEmail($email, $code)) {
                $message = "✅ Unsubscribe verification code sent to <strong>" . htmlspecialchars($email) . "</strong>";
            } else {
                $message = "❌ Failed to send verification code to <strong>" . htmlspecialchars($email) . "</strong>.";
                error_log("❌ sendUnsubscribeEmail failed for $email\n", 3, __DIR__ . "/debug_log.txt");
            }
        } else {
            $message = "⚠ Please enter a valid email address.";
        }

    // Step 2: User submits the verification code
    } elseif (isset($_POST['unsubscribe_verification_code']) && isset($_POST['verify-unsubscribe'])) {
        $inputCode = trim($_POST['unsubscribe_verification_code']);

        if (isset($_SESSION['unsubscribe_code'], $_SESSION['unsubscribe_email']) && $inputCode === $_SESSION['unsubscribe_code']) {
            $email = $_SESSION['unsubscribe_email'];
            unsubscribeEmail($email);

            $message = "✅ You have been unsubscribed successfully.";
            error_log("✔ Unsubscribed: $email\n", 3, __DIR__ . "/debug_log.txt");

            unset($_SESSION['unsubscribe_code'], $_SESSION['unsubscribe_email']);
        } else {
            $message = "❌ Incorrect verification code.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Unsubscribe</title>
    <meta charset="UTF-8">
</head>
<body>
    <h1>Unsubscribe from GitHub Timeline Updates</h1>

    <?php if ($message): ?>
        <p><?= $message ?></p>
    <?php endif; ?>

    <!-- Email Unsubscribe Form -->
    <form method="POST">
        <input type="email" name="unsubscribe_email" required placeholder="Enter your email">
        <button type="submit" id="submit-unsubscribe" name="submit-unsubscribe">Unsubscribe</button>
    </form>

    <!-- Unsubscribe Code Verification Form -->
    <form method="POST">
        <input type="text" name="unsubscribe_verification_code" required placeholder="Enter verification code">
        <button type="submit" id="verify-unsubscribe" name="verify-unsubscribe">Verify</button>
    </form>
</body>
</html>
