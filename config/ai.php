<?php

return [
    'model' => env('OLLAMA_MODEL', 'qwen2.5:1.5b'),
    'url' => env('OLLAMA_URL', 'http://localhost:11434'),
    'timeout' => env('OLLAMA_TIMEOUT', 180),
];
