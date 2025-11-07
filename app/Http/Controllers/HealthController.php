<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Controller;
use App\Model\DB;
use App\Storage\Minio;
use App\Util\Response;

final class HealthController extends Controller
{
    public function index(): void
    {
        $config = require __DIR__ . '/../../../config.php';
        $status = [
            'app' => 'ok',
            'db' => [
                'core' => $this->checkCore(),
                'punchlist' => $this->checkOps(),
            ],
            'minio' => $this->checkMinio($config['minio']),
        ];
        Response::json($status);
    }

    private function checkCore(): bool
    {
        try {
            DB::core()->query('SELECT 1');
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function checkOps(): bool
    {
        try {
            DB::ops()->query('SELECT 1');
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function checkMinio(array $config): bool
    {
        return !empty($config['endpoint']) && !empty($config['access_key']);
    }
}
