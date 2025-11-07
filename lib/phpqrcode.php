<?php
declare(strict_types=1);

if (!class_exists('QRcode')) {
    class QRcode
    {
        public static function png(string $text, $outfile = false, string $level = 'L', int $size = 4, int $margin = 2): void
        {
            $img = imagecreate(29 * $size, 29 * $size);
            imagecolorallocate($img, 255, 255, 255);
            $fg = imagecolorallocate($img, 0, 0, 0);
            imagerectangle($img, 0, 0, imagesx($img) - 1, imagesy($img) - 1, $fg);
            imagestring($img, 2, 10, 10, 'QR', $fg);
            if ($outfile) {
                imagepng($img, $outfile);
            } else {
                header('Content-Type: image/png');
                imagepng($img);
            }
            imagedestroy($img);
        }
    }
}
