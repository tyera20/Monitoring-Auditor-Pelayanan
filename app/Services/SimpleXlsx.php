<?php

namespace App\Services;

use RuntimeException;
use ZipArchive;

class SimpleXlsx
{
    public function write(string $path, array $sheets): void
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('Ekstensi PHP zip harus diaktifkan.');
        }

        $zip = new ZipArchive();

        if ($zip->open(
            $path,
            ZipArchive::CREATE | ZipArchive::OVERWRITE
        ) !== true) {
            throw new RuntimeException('Gagal membuat file Excel.');
        }

        $types = '<?xml version="1.0" encoding="UTF-8"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>';

        $workbook = '<?xml version="1.0" encoding="UTF-8"?>'
            .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
            .'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<sheets>';

        $rels = '<?xml version="1.0" encoding="UTF-8"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';

        foreach ($sheets as $index => $sheet) {
            $number = $index + 1;

            $types .= '<Override PartName="/xl/worksheets/sheet'
                .$number
                .'.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';

            $workbook .= '<sheet name="'
                .$this->xml($sheet['name'])
                .'" sheetId="'.$number.'" r:id="rId'.$number.'"/>';

            $rels .= '<Relationship Id="rId'
                .$number
                .'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" '
                .'Target="worksheets/sheet'.$number.'.xml"/>';

            $xml = '<?xml version="1.0" encoding="UTF-8"?>'
                .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
                .'<sheetData>';

            foreach ($sheet['rows'] as $i => $row) {
                $xml .= '<row r="'.($i + 1).'">';

                foreach ($row as $j => $value) {
                    $letters = '';

                    for (
                        $column = $j + 1;
                        $column > 0;
                        $column = intdiv($column - 1, 26)
                    ) {
                        $letters = chr(65 + ($column - 1) % 26).$letters;
                    }

                    $text = (string) ($value ?? '');

                    // Cegah isi sel ditafsirkan sebagai rumus Excel.
                    if (preg_match('/^[\s]*[=+\-@]/u', $text)) {
                        $text = "'".$text;
                    }

                    $xml .= '<c r="'
                        .$letters.($i + 1)
                        .'" t="inlineStr"><is><t xml:space="preserve">'
                        .$this->xml($text)
                        .'</t></is></c>';
                }

                $xml .= '</row>';
            }

            $zip->addFromString(
                'xl/worksheets/sheet'.$number.'.xml',
                $xml.'</sheetData></worksheet>'
            );
        }

        $zip->addFromString('[Content_Types].xml', $types.'</Types>');
        $zip->addFromString(
            'xl/workbook.xml',
            $workbook.'</sheets></workbook>'
        );
        $zip->addFromString(
            'xl/_rels/workbook.xml.rels',
            $rels.'</Relationships>'
        );
        $zip->addFromString(
            '_rels/.rels',
            '<?xml version="1.0" encoding="UTF-8"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            .'</Relationships>'
        );

        $zip->close();
    }

    private function xml(string $value): string
    {
        $value = preg_replace(
            '/[\x00-\x08\x0B\x0C\x0E-\x1F]/',
            '',
            $value
        ) ?? '';

        return htmlspecialchars(
            $value,
            ENT_XML1 | ENT_QUOTES,
            'UTF-8'
        );
    }
}