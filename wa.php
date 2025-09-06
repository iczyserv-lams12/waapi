<?php

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors to user
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');

// Logging function
function writeLog($message, $level = 'INFO') {
    try {
        $logFile = __DIR__ . '/wa_api.log';
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[$timestamp] [$level] $message" . PHP_EOL;

        // Create logs directory if it doesn't exist
        $logDir = dirname($logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $result = file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
        if ($result === false) {
            error_log("Failed to write to log file: $logFile");
        }
    } catch (Exception $e) {
        error_log("Logging error: " . $e->getMessage());
    }
}

// Log script start
writeLog("WhatsApp API script started");

// Get parameters from GET or POST request
$header = isset($_GET['header']) ? $_GET['header'] : (isset($_POST['header']) ? $_POST['header'] : '');
$body = isset($_GET['body']) ? $_GET['body'] : (isset($_POST['body']) ? $_POST['body'] : '');
$footer = isset($_GET['footer']) ? $_GET['footer'] : (isset($_POST['footer']) ? $_POST['footer'] : '');
$button1_title = isset($_GET['button1_title']) ? $_GET['button1_title'] : (isset($_POST['button1_title']) ? $_POST['button1_title'] : '');
$button1_id = isset($_GET['button1_id']) ? $_GET['button1_id'] : (isset($_POST['button1_id']) ? $_POST['button1_id'] : '');
$button1_url = isset($_GET['button1_url']) ? $_GET['button1_url'] : (isset($_POST['button1_url']) ? $_POST['button1_url'] : '');
$to = isset($_GET['to']) ? $_GET['to'] : (isset($_POST['to']) ? $_POST['to'] : '6281287718800');

// Validate required parameters
if (empty($to)) {
    writeLog("ERROR: 'to' parameter is required", 'ERROR');
    header('Content-Type: application/json');
    header('HTTP/1.1 400');
    echo json_encode([
        'success' => false,
        'error' => "'to' parameter is required",
        'http_code' => 400
    ]);
    exit;
}

// Log incoming request
$request_params = [
    'body' => $body,
    'footer' => $footer,
    'button1_title' => $button1_title,
    'button1_id' => $button1_id,
    'button1_url' => $button1_url,
    'to' => $to
];
writeLog("API Request received: " . json_encode($request_params));

// Log parameter status
$provided_params = [];
if (!empty($body)) $provided_params[] = 'body';
if (!empty($footer)) $provided_params[] = 'footer';
if (!empty($button1_title) && !empty($button1_id) && !empty($button1_url)) $provided_params[] = 'button1';

writeLog("Parameters provided: " . (empty($provided_params) ? 'none' : implode(', ', $provided_params)));

// Authorization token
$auth_token = 'Z95mSWnZ5ITokUB5b5hkYspLo86NjZaD';

// SSL Configuration
// TEMPORARY FIX: Set to false if you get SSL certificate errors
// WARNING: Only use in development/testing, not recommended for production
$ssl_verify_peer = false;  // Set to true for production
$ssl_verify_host = 0;      // Set to 2 for production

// Log SSL configuration
writeLog("SSL Configuration - Verify Peer: " . ($ssl_verify_peer ? 'true' : 'false') . ", Verify Host: $ssl_verify_host");

// Build JSON payload matching the new cURL format
$buttons = [];

// Add button 1 if provided
if (!empty($button1_title) && !empty($button1_id) && !empty($button1_url)) {
    $buttons[] = [
        "type" => "url",
        "title" => addslashes($button1_title),
        "id" => addslashes($button1_id),
        "url" => addslashes($button1_url)
    ];
}

// Build the JSON payload
$payload_data = [
    "type" => "button",
    "to" => addslashes($to)
];

// Always include body (required for WhatsApp API)
$body_text = !empty($body) ? $body : "Message"; // Default message if none provided
$payload_data["body"] = ["text" => addslashes($body_text)];

// Add header if provided
if (!empty($header)) {
    $payload_data["header"] = ["text" => addslashes($header)];
}

// Add footer if provided
if (!empty($footer)) {
    $payload_data["footer"] = ["text" => addslashes($footer)];
}

// Always include action with buttons (required for button type messages)
if (!empty($buttons)) {
    $payload_data["action"] = ["buttons" => $buttons];
} else {
    // Provide default button if none specified
    $payload_data["action"] = [
        "buttons" => [
            [
                "type" => "url",
                "title" => "OK",
                "id" => "default_1",
                "url" => "example.com"
            ]
        ]
    ];
}

$json_payload = json_encode($payload_data);

// Check if JSON encoding failed
if ($json_payload === false) {
    writeLog("JSON encoding failed: " . json_last_error_msg(), 'ERROR');
    header('Content-Type: application/json');
    header('HTTP/1.1 500');
    echo json_encode([
        'success' => false,
        'error' => 'JSON encoding failed: ' . json_last_error_msg(),
        'http_code' => 500
    ]);
    exit;
}

// Log JSON payload being sent
writeLog("JSON Payload built successfully: " . $json_payload);

// Debug: Check if we reach this point
writeLog("About to initialize cURL");

// Check if cURL is available
if (!function_exists('curl_init')) {
    writeLog("cURL extension is not available", 'ERROR');
    header('Content-Type: application/json');
    header('HTTP/1.1 500');
    echo json_encode([
        'success' => false,
        'error' => 'cURL extension is not available',
        'http_code' => 500
    ]);
    exit;
}

$curl = curl_init();

// Log cURL request initiation
writeLog("cURL initialized successfully, initiating request to WhatsApp API");

// Debug: Log request details before sending
writeLog("Request URL: https://gate.whapi.cloud/messages/interactive");
writeLog("Authorization token length: " . strlen($auth_token));
writeLog("Payload length: " . strlen($json_payload));

curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://gate.whapi.cloud/messages/interactive',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30, // Set a reasonable timeout
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => $json_payload,
    CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: Bearer ' . $auth_token
    ),
    // SSL Configuration
    CURLOPT_SSL_VERIFYPEER => $ssl_verify_peer,
    CURLOPT_SSL_VERIFYHOST => $ssl_verify_host,
));

$response = curl_exec($curl);
$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
$total_time = curl_getinfo($curl, CURLINFO_TOTAL_TIME);
$connect_time = curl_getinfo($curl, CURLINFO_CONNECT_TIME);

// Log cURL timing information
writeLog("cURL Request completed - Total time: " . round($total_time, 2) . "s, Connect time: " . round($connect_time, 2) . "s");

// Check for cURL errors
if (curl_errno($curl)) {
    $curl_error = curl_error($curl);
    writeLog("cURL Error: $curl_error", 'ERROR');
    curl_close($curl);
    header('Content-Type: application/json');
    header('HTTP/1.1 500');
    echo json_encode([
        'success' => false,
        'error' => 'cURL Error: ' . $curl_error,
        'http_code' => 500
    ]);
    exit;
}

// Log API response with full details
writeLog("API Response received - HTTP Code: $http_code");
writeLog("Full Response: " . $response);

// Try to parse and log response details
$response_data = json_decode($response, true);
if ($response_data) {
    writeLog("Response parsed successfully - Keys: " . implode(', ', array_keys($response_data)));
    if (isset($response_data['chat'])) {
        writeLog("Chat ID: " . $response_data['chat']);
    }
    if (isset($response_data['message'])) {
        writeLog("Message ID: " . $response_data['message']);
    }
    if (isset($response_data['error'])) {
        writeLog("API Error: " . json_encode($response_data['error']), 'ERROR');
    }
} else {
    writeLog("Response is not valid JSON or empty", 'WARNING');
}

curl_close($curl);

// Return response with proper headers
header('Content-Type: application/json');
header('HTTP/1.1 ' . $http_code);

if ($http_code >= 200 && $http_code < 300) {
    $client_response = [
        'success' => true,
        'response' => json_decode($response, true),
        'http_code' => $http_code
    ];
    writeLog("Request processed successfully with HTTP $http_code");
    writeLog("Client response sent: " . json_encode($client_response));
    echo json_encode($client_response);
} else {
    $client_response = [
        'success' => false,
        'error' => json_decode($response, true),
        'http_code' => $http_code
    ];
    writeLog("Request failed with HTTP $http_code", 'ERROR');
    writeLog("Client error response sent: " . json_encode($client_response), 'ERROR');
    echo json_encode($client_response);
}
?>
