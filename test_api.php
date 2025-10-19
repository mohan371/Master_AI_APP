<?php
echo "<h1>API Test Page</h1>";

// 1. Include your keys
require_once "backend/config/keys.php";

// 2. Check if the key constant is loaded
if (defined('GEMINI_API_KEY') && GEMINI_API_KEY !== 'PASTE_YOUR_API_KEY_HERE') {
    echo "<p style='color:green;'>SUCCESS: API Key constant is loaded.</p>";
} else {
    echo "<p style='color:red;'>ERROR: API Key is not loaded. Check backend/config/keys.php</p>";
    exit;
}

// 3. Set up the API call (using gemini-pro)
$api_key = GEMINI_API_KEY;
$url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.0-pro:generateContent?key=' . $api_key;
$data = [
    'contents' => [
        [
            'parts' => [
                ['text' => "Hello"]
            ]
        ]
    ]
];

// 4. Make the cURL request
echo "<p>Attempting to connect to Google API...</p>";
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // Use the XAMPP SSL fix

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

// 5. Show the results
echo "<h2>Test Results:</h2>";
echo "<strong>HTTP Code:</strong> " . $http_code . "<br>";

if ($curl_error) {
    echo "<strong>cURL Error:</strong> <pre style='color:red;'>" . $curl_error . "</pre>";
}

echo "<strong>Raw API Response:</strong>";
echo "<pre style='background:#f4f4f4; border:1px solid #ccc; padding:10px;'>";
print_r($response);
echo "</pre>";

?>