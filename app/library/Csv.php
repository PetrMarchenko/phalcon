<?php

namespace Shark\Library;

class Csv
{
    public static function toCsv($data)
    {
        if (is_array($data)) {
            ob_start();

            $fp = fopen('php://output', 'wb');
            fwrite($fp, "\xEF\xBB\xBF");
            foreach ($data as $row) {
                fputcsv($fp, $row, ',', '"');
            }

            return $data = ob_get_clean();
        }
        return false;
    }
}