<?php
namespace App\Components;

class FileUtils
{
    /**
     * Count lines in text file
     * @param $filepath
     */
    public static function countLines($filepath): int
    {
        $count = 0;
        $handle = fopen($filepath, "r");
        while(!feof($handle)){
            fgets($handle);
            $count++;
        }
        fclose($handle);
        return $count;
    }

    public static function bytesToHuman($bytes): string
    {
        $units = ['B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB'];
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
