<?php

$url = 'http://127.0.0.1:11434/api/chat';

$payload = json_encode([
    'model' => 'mistral',
    'stream' => false,
    'messages' => [
        [
            'role' => 'user',
            'content' => 'Réponds simplement OK',
        ],
    ],
]);

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($payload),
        ],
        'content' => $payload,
        'timeout' => 600,
        'ignore_errors' => true,
    ],
]);

$start = microtime(true);

$result = file_get_contents(
    $url,
    false,
    $context
);

var_dump([
    'duration' => microtime(true) - $start,
    'result' => $result,
    'headers' => $http_response_header ?? [],
    'error' => error_get_last(),
]);