<?php

/**
 * Get the class "basename" of the given object / class.
 *
 * @param  string|object  $class
 * @return string
 */
function class_basename($class): string
{
    $class = is_object($class) ? get_class($class) : $class;
    
    return basename(str_replace('\\', '/', $class));
}

/**
 * Get the namespace of the given class.
 *
 * @param  string|object  $class
 * @return string
 */
function class_namespace($class): string
{
    $class = is_object($class) ? get_class($class) : $class;
    
    return trim(implode('\\', array_slice(explode('\\', $class), 0, -1)), '\\');
}

/**
 * Determine if a given string contains a given substring.
 *
 * @param  string  $haystack
 * @param  string|array  $needles
 * @return bool
 */
function class_uses_trait($class, string $trait): bool
{
    $uses = class_uses_recursive($class);
    
    return in_array($trait, $uses);
}

/**
 * Returns all traits used by a class, its parent classes and trait of their traits.
 *
 * @param  object|string  $class
 * @return array
 */
function class_uses_recursive($class): array
{
    if (is_object($class)) {
        $class = get_class($class);
    }

    $results = [];

    foreach (array_reverse(class_parents($class)) + [$class => $class] as $className) {
        $results += trait_uses_recursive($className);
    }

    return array_unique($results);
}

/**
 * Returns all traits used by a trait and its traits.
 *
 * @param  string  $trait
 * @return array
 */
function trait_uses_recursive($trait): array
{
    $traits = class_uses($trait);

    foreach ($traits as $usedTrait) {
        $traits += trait_uses_recursive($usedTrait);
    }

    return $traits;
}
