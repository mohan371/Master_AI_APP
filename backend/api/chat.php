<?php
// Start session to check if user is logged in
session_start();

// Include config files
require_once "../config/db.php"; // We need $pdo now!
require_once "../config/keys.php";

// --- Security Check ---
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    http_response_code(401); // Unauthorized
    echo json_encode(["error" => "You must be logged in."]);
    exit;
}
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405); // Method Not Allowed
    echo json_encode(["error" => "Method not allowed."]);
    exit;
}
$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['messages']) || empty($input['messages'])) {
    http_response_code(400); // Bad Request
    echo json_encode(["error" => "No message history provided."]);
    exit;
}

// --- NEW: Get User ID and Last Message ---
$user_id = $_SESSION["id"]; // Get the logged-in user's ID
$messages_history = $input['messages'];
// Get the last message sent by the user
$last_user_message = end($messages_history)['content'];
// --- END NEW ---


// --- Hugging Face API Call ---
$api_key = HF_API_KEY;
$model_url = 'https://router.huggingface.co/v1/chat/completions';
$data = [
    'model' => 'meta-llama/Llama-3.1-8B-Instruct',
    'messages' => $messages_history,
    'stream' => false
];
$ch = curl_init($model_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $api_key
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

// --- Process the Response ---
$bot_reply = "";
if (!empty($curl_error)) {
    $bot_reply = "cURL Error: " . $curl_error;
} elseif ($http_code == 200) {
    $result = json_decode($response, true);
    if (isset($result['choices'][0]['message']['content'])) {
        $bot_reply = $result['choices'][0]['message']['content'];
    } else {
        $bot_reply = "Got a 200, but the response format was unexpected. " . $response;
    }
} else {
    $bot_reply = "Error: Could not connect. (Code: $http_code) Response: " . $response;
}

// --- NEW: Log to Database ---
// We log *after* getting the response, so we can save both messages.
try {
    // 1. Log the user's message
    $sql_user = "INSERT INTO chat_history (user_id, role, content) VALUES (:user_id, 'user', :content)";
    $stmt_user = $pdo->prepare($sql_user);
    $stmt_user->execute(['user_id' => $user_id, 'content' => $last_user_message]);

    // 2. Log the bot's reply
    $sql_bot = "INSERT INTO chat_history (user_id, role, content) VALUES (:user_id, 'assistant', :content)";
    $stmt_bot = $pdo->prepare($sql_bot);
    $stmt_bot->execute(['user_id' => $user_id, 'content' => $bot_reply]);

} catch (PDOException $e) {
    // If logging fails, don't stop the chat.
    // In a real production app, you would log this error to a file.
    // error_log("Failed to save chat history: " . $e->getMessage());
}
// --- END NEW ---

// Send the response back to the frontend as JSON
header('Content-Type: application/json');
echo json_encode(["reply" => $bot_reply]);

// We don't unset $pdo here, as it might be used by other parts if included
?>