<?php
declare(strict_types=1);

namespace App\Util;

final class Xlsx
{
    public static function stream(string $filename, array $header, iterable $rows): void
    {
        $xmlHeader = '<?xml version="1.0" encoding="UTF-8"?>';
        $worksheet = '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>';
        $worksheet .= self::row($header, true);
        foreach ($rows as $row) {
            $worksheet .= self::row($row, false);
        }
        $worksheet .= '</sheetData></worksheet>';
        $zip = new \ZipArchive();
        $tmp = tempnam(sys_get_temp_dir(), 'xlsx');
        $zip->open($tmp, \ZipArchive::CREATE);
        $zip->addFromString('[Content_Types].xml', self::contentTypes());
        $zip->addFromString('_rels/.rels', self::rels());
        $zip->addFromString('xl/_rels/workbook.xml.rels', self::workbookRels());
        $zip->addFromString('xl/workbook.xml', self::workbook());
        $zip->addFromString('xl/worksheets/sheet1.xml', $xmlHeader . $worksheet);
        $zip->close();
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        readfile($tmp);
        unlink($tmp);
    }

    private static function row(array $cells, bool $header): string
    {
        $xml = '<row>';
        foreach ($cells as $value) {
            $xml .= '<c t="inlineStr"><is><t>' . htmlspecialchars((string)$value) . '</t></is></c>';
        }
        $xml .= '</row>';
        return $xml;
    }

    private static function contentTypes(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '</Types>';
    }

    private static function rels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>';
    }

    private static function workbook(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="Sheet1" sheetId="1" r:id="rId1"/></sheets>'
            . '</workbook>';
    }

    private static function workbookRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '</Relationships>';
    }
}
