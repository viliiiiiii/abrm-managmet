<?php
declare(strict_types=1);

namespace App\Util;

class Response
{
    public static function json($data, int $status = 200, array $headers = []): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        foreach ($headers as $key => $value) {
            header($key . ': ' . $value, true);
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public static function problem(string $title, string $detail, int $status = 400, array $extra = []): void
    {
        $payload = array_merge([
            'type' => 'about:blank',
            'title' => $title,
            'detail' => $detail,
            'status' => $status,
        ], $extra);
        self::json($payload, $status, ['Content-Type' => 'application/problem+json']);
    }
}
