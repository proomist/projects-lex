<?php
require __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$jwt = App\Helper\AuthHelper::createJwt(1, 'Admin');
$ch = curl_init('http://127.0.0.1:8000/api/v1/clients');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Cookie: legal_session=' . $jwt
]);
$data = ['client_type' => 'Bireysel', 'first_name' => 'Test', 'last_name' => 'User'];
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
if(curl_errno($ch)){
    echo "cURL error: " . curl_error($ch) . "\n";
}
echo "HTTP CODE: $httpCode\n";
echo "RESPONSE:\n" . $response . "\n";
curl_close($ch);
