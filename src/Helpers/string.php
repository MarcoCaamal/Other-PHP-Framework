<?php

function snakeCase(string $str): string
{
    $snakeCased = [];
    $skip = [' ', '-', '_', '/', '\\', '|', ',', '.', ';', ':'];

    $i = 0;

    while ($i < strlen($str)) {
        $last = count($snakeCased) > 0
        ? $snakeCased[count($snakeCased) - 1]
        : null;

        $character = $str[$i++];

        if(ctype_upper($character)) {
            if($last !== '_') {
                $snakeCased[] = '_';
            }
            $snakeCased[] = strtolower($character);
        } elseif(ctype_lower($character)) {
            $snakeCased[] = $character;
        } elseif(in_array($character, $skip)) {
            if($last !== '_') {
                $snakeCased[] = '_';
            }

            while($i < strlen($str) && in_array($str[$i], $skip)) {
                $i++;
            }
        }
    }

    if ($snakeCased[0] == '_') {
        $snakeCased[0] = '';
    }

    if ($snakeCased[count($snakeCased) - 1] == '_') {
        $snakeCased[count($snakeCased) - 1] = '';
    }

    return implode($snakeCased);
}
