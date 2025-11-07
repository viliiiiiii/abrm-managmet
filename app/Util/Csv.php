<?php
declare(strict_types=1);

namespace App\Util;

final class Csv
{
    public static function stream(string $filename, array $header, iterable $rows): void
    {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $out = fopen('php://output', 'w');
        fputcsv($out, $header);
        foreach ($rows as $row) {
            fputcsv($out, $row);
        }
        fclose($out);
    }
}
