<?php
declare(strict_types = 1);

define('ROOT_FOLDER', __DIR__ . '/');

if (!function_exists('getFilePath')) {
    function getFilePath(string $fileName, string $searchFolder = ''): string
    {
        $fileName = '/' .ltrim($fileName, '/\\');
        $searchFolder = trim($searchFolder, '/\\');

        if($searchFolder !== '') {
            return ROOT_FOLDER . $searchFolder . $fileName;
        }

        return ROOT_FOLDER . $fileName;
    }
}