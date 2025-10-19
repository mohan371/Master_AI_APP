<?php
// Start session & include keys
session_start();
require_once "../config/keys.php"; // We need the HF key

// --- Security Check ---
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    http_response_code(401); 
    echo json_encode(["error" => "You must be logged in."]);
    exit;
}
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed."]);
    exit;
}
$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['messages']) || empty($input['messages'])) {
    http_response_code(400);
    echo json_encode(["error" => "No message history provided."]);
    exit;
}

$messages_history = $input['messages']; // Includes the system prompt

// --- Hugging Face API Call (v1 Router) ---
$api_key = HF_API_KEY;
// It probably looks like this right now, which is wrong:
// $model_url = '[https://router.huggingface.co/v1/chat/completions]'; 

// --- CORRECT THIS LINE ---
// Make sure it contains ONLY the URL string, with no brackets:
$model_url = 'https://router.huggingface.co/v1/chat/completions';
$data = [
    'model' => 'meta-llama/Llama-3.1-8B-Instruct', // Use the same powerful model
    'messages' => $messages_history, // Pass the history including system prompt
    'stream' => false
];


$ch = curl_init($model_url); // Now initialize cURL
$ch = curl_init($model_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $api_key
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 60); // Code generation might take a bit longer
curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4); // Force IPv4
// DEBUG: Show the URL cURL is using
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

// --- Process the Response ---
// --- Process the Response ---
$bot_reply = "";
$curl_errno = curl_errno($ch); // Get the cURL error number immediately after exec

if ($curl_errno > 0) {
    // If curl_errno is greater than 0, a cURL-specific error occurred
    $bot_reply = "cURL Error (" . $curl_errno . "): " . curl_strerror($curl_errno);
} elseif ($http_code == 200) {
    // cURL worked, HTTP status is 200 (OK)
    $result = json_decode($response, true);
    if (isset($result['choices'][0]['message']['content'])) {
        $bot_reply = $result['choices'][0]['message']['content'];
    } else {
        $bot_reply = "Got a 200, but the response format was unexpected. " . $response;
    }
} else {
    // cURL worked, but HTTP status is an error (4xx, 5xx)
    $error_data = json_decode($response, true);
    if (isset($error_data['error']['message'])) {
        $error_message = $error_data['error']['message'];
    } elseif (isset($error_data['detail'])) {
        $error_message = $error_data['detail'];
    } else {
        $error_message = "Unknown error from API. Full response: " . $response;
    }
    $bot_reply = "API Error (Code: $http_code): " . $error_message;
}
// Note: We closed the curl handle ($ch) earlier, which is correct.
// curl_errno and curl_strerror work even after curl_close.
// Send the response back to the frontend as JSON
header('Content-Type: application/json');
echo json_encode(["reply" => $bot_reply]);

?>