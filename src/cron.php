<?php

require_once __DIR__ . '/functions.php';

// Log that the cron ran
file_put_contents(__DIR__ . "/cron_log.txt", "Ran at: " . date("Y-m-d H:i:s") . "\n", FILE_APPEND);

// Fetch GitHub public events
$options = [
    "http" => [
        "method" => "GET",
        "header" => "User-Agent: GitHub-Timeline-App\r\n"
    ]
];
$context = stream_context_create($options);
$json = file_get_contents("https://api.github.com/events", false, $context);

if (!$json) {
    file_put_contents(__DIR__ . "/cron_log.txt", "❌ Failed to fetch GitHub data\n", FILE_APPEND);
    die("GitHub fetch failed.");
}

$data = json_decode($json, true);

// Format GitHub data into HTML
$html = "<h2>GitHub Timeline Updates</h2>";
$html .= "<table border='1'>";
$html .= "<tr><th>Event</th><th>User</th></tr>";

foreach ($data as $event) {
    $eventType = htmlspecialchars($event['type'] ?? 'N/A');
    $username = htmlspecialchars($event['actor']['login'] ?? 'N/A');
    $html .= "<tr><td>$eventType</td><td>$username</td></tr>";
}
$html .= "</table>"; // ✅ Close table after the loop ends

// Read registered emails
$emailFile = __DIR__ . '/registered_emails.txt';
$emails = file_exists($emailFile) ? file($emailFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];

$subject = "Latest GitHub Updates";
$headers = "From: no-reply@example.com\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8\r\n";

foreach ($emails as $email) {
    $unsubscribeLink = "https://localhost/github-timeline-Ayushijaiswal24/src/unsubscribe.php?email=" . urlencode($email);
    $message = $html . "<p><a href='$unsubscribeLink' id='unsubscribe-button'>Unsubscribe</a></p>";
    
    $success = mail($email, $subject, $message, $headers);

    error_log($success 
        ? "✅ Cron update sent to $email\n"
        : "❌ Cron failed for $email\n", 3, __DIR__ . "/debug_log.txt");
}
