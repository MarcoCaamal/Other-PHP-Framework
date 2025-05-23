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

        if (ctype_upper($character)) {
            if ($last !== '_') {
                $snakeCased[] = '_';
            }
            $snakeCased[] = strtolower($character);
        } elseif (ctype_lower($character)) {
            $snakeCased[] = $character;
        } elseif (in_array($character, $skip)) {
            if ($last !== '_') {
                $snakeCased[] = '_';
            }

            while ($i < strlen($str) && in_array($str[$i], $skip)) {
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

/**
 * Convierte un string a PascalCase.
 *
 * @param string $input
 * @return string
 */
function pascalCase(string $input): string
{
    $parts = explode('_', snakeCase($input));
    $parts = array_map('ucfirst', $parts);
    return implode('', $parts);
}

/**
 * Convierte un string a camelCase.
 *
 * @param string $input
 * @return string
 */
function camelCase(string $input): string
{
    return lcfirst(pascalCase($input));
}

/**
 * Genera el path para las rutas basado en el nombre del controlador.
 *
 * @param string $controllerName
 * @return string
 */
function routePath(string $controllerName): string
{
    // Convertir a snake_case y luego reemplazar guiones bajos por guiones
    $path = strtolower(snakeCase($controllerName));
    return str_replace('_', '-', $path);
}

/**
 * Convierte un string a snake_case para nombres de tabla y pluraliza.
 *
 * @param string $input
 * @return string
 */
function tableName(string $input): string
{
    // Usa la función snakeCase y pluraliza
    return snakeCase($input) . 's';
}
