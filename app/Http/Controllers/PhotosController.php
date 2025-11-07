<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Controller;
use App\Auth\Permissions;
use App\Storage\Minio;
use App\Util\Response;

final class PhotosController extends Controller
{
    private Minio $minio;

    public function __construct()
    {
        $config = require __DIR__ . '/../../../config.php';
        $this->minio = new Minio($config['minio']);
    }

    public function presign(): void
    {
        Permissions::authorize('photos.upload');
        $filename = $_POST['filename'] ?? 'upload.bin';
        $contentType = $_POST['content_type'] ?? 'application/octet-stream';
        $key = sprintf('uploads/%s/%s', date('Y/m/d'), bin2hex(random_bytes(8)) . '-' . basename($filename));
        $url = $this->minio->presignPut($this->bucket(), $key, $contentType);
        Response::json([
            'url' => $url,
            'path' => $key,
            'expires_in' => 900,
        ]);
    }

    private function bucket(): string
    {
        $config = require __DIR__ . '/../../../config.php';
        return $config['minio']['bucket_uploads'];
    }
}
