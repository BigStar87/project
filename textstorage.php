<?php

$textStorage = [];

//Добавление в массив заголовка и текста
function add(string $title, string $text, &$array): void
{
    $array[] = [
        "title" => $title,
        "text" => $text
    ];
}

//Удаление массива по индексу
function remove(int $index, &$array): bool
{
    if (!empty($array[$index])) {
        unset($array[$index]);
        return true;
    }
    return false;
}

//Изменение заголовка и текста в массиве по индексу
function edit(int $index, string $title, string $text, &$array): bool
{
    if (!empty($array[$index])) {
        $array[$index] = [
            "title" => $title,
            "text" => $text
        ];
        return true;
    }
    return false;
}

add("Hello World", "My name Alex", $textStorage);
add("Hello World!", "My name Alex!", $textStorage);

print_r($textStorage);

echo remove(1, $textStorage);

print_r($textStorage);

echo edit(0, "edit title", "edit text", $textStorage);

print_r($textStorage);
