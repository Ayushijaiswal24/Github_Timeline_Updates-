

<?php

function generateVerificationCode(): string {
    return strval(rand(100000, 999999));
}

function sendVerificationEmail(string $email, string $code): bool {
    $subject = "Your Verification Code";
    $message = "<p>Your verification code is: <strong>$code</strong></p>";

    $headers = "From: shauryajai8@gmail.com\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    $success = mail($email, $subject, $message, $headers);

    // Log result
   /* $logMessage = $success 
        ? "✅ Verification email sent to $email with code $code\n"
        : "❌ Failed to send verification email to $email\n";

    error_log($logMessage, 3, __DIR__ . '/debug_log.txt');
*/
    return $success;
}

function registerEmail(string $email): bool {
    $file = __DIR__ . '/registered_emails.txt';
    $emails = file_exists($file) ? file($file, FILE_IGNORE_NEW_LINES) : [];

    if (!in_array($email, $emails)) {
        $success = file_put_contents($file, $email . PHP_EOL, FILE_APPEND | LOCK_EX) !== false;

        error_log($success
            ? "✅ Registered email: $email\n"
            : "❌ Failed to register email: $email\n", 3, __DIR__ . '/debug_log.txt');

        return $success;
    }

    return true; // Already registered
}

function fetchGitHubTimeline(): array {
    $url = "https://api.github.com/events";
    $opts = [
        "http" => [
            "method" => "GET",
            "header" => "User-Agent: GitHubTimelineApp\r\n"
        ]
    ];
    $context = stream_context_create($opts);
    $json = @file_get_contents($url, false, $context);
    return json_decode($json, true) ?? [];
}


function formatGitHubData($data): string {
    $html = "<h2>GitHub Timeline Updates</h2>";
    $html .= "<table border='1'>";
    $html .= "<tr><th>Event</th><th>User</th></tr>";

    foreach ($data as $event) {
        $eventType = htmlspecialchars($event['type'] ?? 'N/A');
        $username = htmlspecialchars($event['actor']['login'] ?? 'N/A');
        $html .= "<tr><td>$eventType</td><td>$username</td></tr>";
    }

    $html .= "</table>";
    return $html;
}




function sendGitHubUpdatesToSubscribers(): void {
    $file = __DIR__ . '/registered_emails.txt';
    if (!file_exists($file)) return;

    $emails = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $timeline = fetchGitHubTimeline();
    $content = formatGitHubData($timeline);

    foreach ($emails as $email) {
        $unsubscribeLink = "https://localhost/github-timeline-Ayushijaiswal24/src/unsubscribe.php?email=" . urlencode($email);
        $message = $content . "<p><a href='$unsubscribeLink' id='unsubscribe-button'>Unsubscribe</a></p>";

        $subject = "Latest GitHub Updates";
        $headers = "From: no-reply@example.com\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

        $success = mail($email, $subject, $message, $headers);

        error_log($success 
            ? "✅ Update sent to $email\n"
            : "❌ Failed to send update to $email\n", 3, __DIR__ . '/debug_log.txt');
    }
}


function unsubscribeEmail($email): void {
    $file = __DIR__ . '/registered_emails.txt';
    $emails = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $filtered = array_filter($emails, fn($e) => trim($e) !== trim($email));
    file_put_contents($file, implode(PHP_EOL, $filtered) . PHP_EOL);
    error_log("ℹ️ Unsubscribed: $email\n", 3, __DIR__ . '/debug_log.txt');
}

function sendUnsubscribeEmail($email, $code): bool {
    $subject = "Confirm Unsubscription";
    $message = "<p>To confirm unsubscription, use this code: <strong>$code</strong></p>";

    $headers = "From: no-reply@example.com\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    $success = mail($email, $subject, $message, $headers);

    error_log($success 
        ? "✅ Unsubscribe email sent to $email\n"
        : "❌ Failed to send unsubscribe email to $email\n", 3, __DIR__ . '/debug_log.txt');

    return $success;
}