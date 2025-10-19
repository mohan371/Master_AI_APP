<?php
// Start session & include keys
session_start();
require_once "../config/keys.php";

// --- Security Check ---
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "You must be logged in."]);
    exit;
}
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method not allowed."]);
    exit;
}
$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['prompt']) || empty($input['prompt'])) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "No prompt provided."]);
    exit;
}

$prompt = $input['prompt'];

// --- Hugging Face API Call (Original Endpoint) ---
$api_key = HF_API_KEY;

// 1. URL: Use the ORIGINAL inference API
// 2. MODEL: Use the most popular SDXL model
$model_url = 'https://api-inference.huggingface.co/models/stabilityai/stable-diffusion-xl-base-1.0';

$data = ['inputs' => $prompt];

$ch = curl_init($model_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $api_key
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); 
curl_setopt($ch, CURLOPT_TIMEOUT, 120); // 120 seconds
curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4); // Force IPv4
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
$curl_error = curl_error($ch);
curl_close($ch);

// --- Process Response (Raw Image Bytes) ---
header('Content-Type: application/json');

if ($curl_error) {
    echo json_encode(["status" => "error", "message" => "cURL Error: " . $curl_error]);
} elseif ($http_code == 200 && strpos($content_type, 'image/jpeg') !== false) {
    // SUCCESS: The API returned raw image data.
    $base64_image = base64_encode($response);
    echo json_encode(["status" => "success", "base64_image" => $base64_image]);
} else {
    // ERROR: The API returned a JSON error (e.g., model is loading)
    $error_data = json_decode($response, true);
    
    if (isset($error_data['error'])) {
        $error_message = $error_data['error'];
    } else {
        $error_message = "Unknown error from API. Full response: " . $response;
    }

    // This is the most common error: the model is loading.
    if (isset($error_data['estimated_time'])) {
        $error_message .= " The model is loading, please try again in " . (int)$error_data['estimated_time'] . " seconds.";
    }
    
    echo json_encode(["status" => "error", "message" => $error_message]);
}
?>