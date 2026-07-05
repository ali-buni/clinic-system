<?php

return [
    /*
    | Comma-separated list of providers to try in order.
    | Example: AI_PROVIDER=groq,deepseek,ollama
    */
    'default' => env('AI_PROVIDER', 'openrouter,groq,deepseek'),

    'providers' => [
        'ollama' => [
            'driver' => 'ollama',
            'model' => env('OLLAMA_MODEL', 'qwen2.5:3b'),
            'url' => env('OLLAMA_URL', 'http://localhost:11434'),
            'timeout' => env('OLLAMA_TIMEOUT', 180),
        ],

        'openrouter' => [
            'driver' => 'openai',
            'model' => env('OPENROUTER_MODEL', 'mistralai/mistral-7b-instruct:free'),
            'api_key' => env('OPENROUTER_API_KEY'),
            'base_url' => env('OPENROUTER_BASE_URL', 'https://openrouter.ai/api/v1/chat/completions'),
            'timeout' => env('OPENROUTER_TIMEOUT', 60),
        ],

        'groq' => [
            'driver' => 'openai',
            'model' => env('GROQ_MODEL', 'llama-3.3-70b-versatile'),
            'api_key' => env('GROQ_API_KEY'),
            'base_url' => env('GROQ_BASE_URL', 'https://api.groq.com/openai/v1/chat/completions'),
            'timeout' => env('GROQ_TIMEOUT', 60),
        ],

        'deepseek' => [
            'driver' => 'openai',
            'model' => env('DEEPSEEK_MODEL', 'deepseek-v4-flash'),
            'api_key' => env('DEEPSEEK_API_KEY'),
            'base_url' => env('DEEPSEEK_BASE_URL', 'https://api.deepseek.com/v1/chat/completions'),
            'timeout' => env('DEEPSEEK_TIMEOUT', 60),
        ],
    ],
];
