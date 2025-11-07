<?php
declare(strict_types=1);

namespace App\Http;

use App\Security\Csrf;

abstract class Controller
{
    protected function view(string $template, array $data = []): string
    {
        extract($data, EXTR_SKIP);
        $csrfToken = Csrf::token();
        $csrfField = Csrf::field();
        ob_start();
        require __DIR__ . '/../../views/' . $template . '.php';
        return ob_get_clean();
    }
}
