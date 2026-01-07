<?php

$searchRoot = __DIR__ . DIRECTORY_SEPARATOR . 'test_search';
$searchName = 'test.txt';
$searchResult = [];

//Рекурсивная функция поиска файлов
function searchFile(string $searchRoot, string $searchName, array &$searchResult): void
{
    if (!is_dir($searchRoot)) {
        return;
    }

    $files = scandir($searchRoot);

    foreach ($files as $file) {
        if ($file === '.' || $file === '..') {
            continue;
        }

        $fullPath = $searchRoot . '/' . $file;

        if (is_dir($fullPath)) {
            searchFile($fullPath, $searchName, $searchResult);
        } elseif ($file === $searchName) {
            $searchResult[] = $fullPath;
        }
    }
}

searchFile($searchRoot, $searchName, $searchResult);

//Функция поиска не пустых файлов
function sizeFile(string $searchResult): string
{
    return filesize($searchResult);
}

if (!empty($searchResult)) {
    print_r(array_filter($searchResult, "sizeFile"));
} else {
    echo 'Файлы не найдены';
}
