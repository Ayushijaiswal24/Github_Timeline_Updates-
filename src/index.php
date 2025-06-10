<?php
require_once 'functions.php';

// TODO: Implement the form and logic for email registration and verification

session_start();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Step 1: Email submission
    if (isset($_POST['email']) && isset($_POST['submit_email'])) {
        $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);

        if ($email) {
            $code = generateVerificationCode();
            $_SESSION['email'] = $email;
            $_SESSION['verification_code'] = $code;

            if (sendVerificationEmail($email, $code)) {
                $message = "✅ Verification code sent to <strong>" . htmlspecialchars($email) . "</strong>";
            } else {
                $message = "❌ Failed to send email to <strong>" . htmlspecialchars($email) . "</strong>. Check your mail setup.";
                error_log("❌ Failed mail() in index.php for $email\n", 3, __DIR__ . "/debug_log.txt");
            }
        } else {
            $message = "⚠ Please enter a valid email address.";
        }

    // Step 2: Code verification
    } elseif (isset($_POST['verification_code']) && isset($_POST['submit_verification'])) {
        $inputCode = trim($_POST['verification_code']);

        if (isset($_SESSION['verification_code'], $_SESSION['email']) && $inputCode === $_SESSION['verification_code']) {
            $email = $_SESSION['email'];

            if (registerEmail($email)) {
                $message = "✅ Email <strong>" . htmlspecialchars($email) . "</strong> successfully registered!";
                error_log("✔ Registered: $email\n", 3, __DIR__ . "/debug_log.txt");
            } else {
                $message = "⚠ Email already registered or failed to save.";
                error_log("❌ Registration failed: $email\n", 3, __DIR__ . "/debug_log.txt");
            }

            unset($_SESSION['verification_code'], $_SESSION['email']);
        } else {
            $message = "❌ Incorrect verification code.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscribe to GitHub Timeline Updates</title>
</head>
<body>
    <h1>Subscribe to GitHub Timeline Updates</h1>

    <?php if (!empty($message)): ?>
        <p><?= $message ?></p>
    <?php endif; ?>

    <!-- Email Input Form -->
    <form method="POST">
        <input type="email" name="email" required placeholder="Enter your email"
               value="<?= isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : '' ?>">
        <button type="submit" name="submit_email" id="submit-email">Submit</button>
    </form>

    <!-- Verification Code Input Form -->
    <form method="POST">
        <input type="text" name="verification_code" maxlength="6" required placeholder="Enter verification code">
        <button type="submit" name="submit_verification" id="submit-verification">Verify</button>
    </form>
</body>
</html>
