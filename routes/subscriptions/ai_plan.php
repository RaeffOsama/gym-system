<?php
session_start();
require_once __DIR__ . '/../../helpers/response.php';

if (!isset($_SESSION['user_id'])) {
    sendJson(401, false, 'Unauthorized: Please log in');
}

$body = json_decode(file_get_contents('php://input'), true);

$required = ['goal', 'weight', 'height', 'age', 'gender', 'body_fat', 'muscle_mass', 'water_perc'];
foreach ($required as $field) {
    if (!isset($body[$field]) || $body[$field] === '') {
        sendJson(400, false, "Missing required field: $field");
    }
}

$goal = $body['goal'];
if (!in_array($goal, ['Weight Loss', 'Weight Gain', 'Muscle Gain'])) {
    sendJson(400, false, 'goal must be one of: Weight Loss, Weight Gain, Muscle Gain');
}

$payload = json_encode([
    'goal'        => $goal,
    'weight'      => (float) $body['weight'],
    'height'      => (float) $body['height'],
    'age'         => (int)   $body['age'],
    'gender'      => $body['gender'],
    'body_fat'    => (float) $body['body_fat'],
    'muscle_mass' => (float) $body['muscle_mass'],
    'water_perc'  => (float) $body['water_perc'],
]);

$ch = curl_init('https://ai.oralpharm.com/predict');
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 15,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError) {
    sendJson(503, false, 'AI plan service unavailable: ' . $curlError);
}

$result = json_decode($response, true);

if ($httpCode !== 200 || !$result) {
    sendJson(502, false, 'AI plan service returned an unexpected response');
}

sendJson(200, true, 'AI plan generated', $result);
