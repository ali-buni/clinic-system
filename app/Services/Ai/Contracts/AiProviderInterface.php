<?php

namespace App\Services\Ai\Contracts;

interface AiProviderInterface
{
    public function chat(array $messages, array $options = []): ?string;

    public function chatJson(array $messages, array $options = []): ?array;

    public function parseJson(?string $content): ?array;
}
