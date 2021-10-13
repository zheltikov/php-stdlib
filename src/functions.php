<?php

namespace Zheltikov\StdLib;

use Generator;
use OutOfBoundsException;

/**
 * @param array $array
 * @param callable $callback
 * @return bool
 */
function array_every(array $array, callable $callback): bool
{
    foreach ($array as $key => $value) {
        if (!call_user_func($callback, $value, $key, $array)) {
            return false;
        }
    }

    return true;
}

/**
 * @param array $array
 * @param callable $callback
 * @return bool
 */
function array_some(array $array, callable $callback): bool
{
    foreach ($array as $key => $value) {
        if (call_user_func($callback, $value, $key, $array)) {
            return true;
        }
    }

    return false;
}

/**
 * @param array $array
 * @param int|string $index
 * @return mixed
 */
function array_at(array $array, $index)
{
    $key = $index;

    if (
        array_is_list($array)
        && $index < 0
    ) {
        $key = count($array) - $index;
    }

    if (array_key_exists($key, $array)) {
        return $array[$key];
    }

    throw new OutOfBoundsException(sprintf('Undefined index: %d', $index));
}

/**
 * @param array $array
 * @return \Generator
 */
function array_entries(array $array): Generator
{
    foreach ($array as $key => $value) {
        yield [$key, $value];
    }
}

/**
 * @param array $array
 * @param callable $callback
 * @return mixed
 */
function array_find(array $array, callable $callback)
{
    foreach ($array as $key => $value) {
        if (call_user_func($callback, $value, $key, $array)) {
            return $value;
        }
    }

    throw new NotFoundException('No element satisfies the provided condition');
}

/**
 * @param array $array
 * @param callable $callback
 * @return int|string
 */
function array_find_index(array $array, callable $callback)
{
    foreach ($array as $key => $value) {
        if (call_user_func($callback, $value, $key, $array)) {
            return $key;
        }
    }

    return -1;
}

/**
 * @param array $array
 * @param ...$values
 * @return array
 */
function array_concat(array $array = [], ...$values): array
{
    foreach ($values as $value) {
        if (is_array($value)) {
            $is_list = array_is_list($value);

            foreach ($value as $key_2 => $value_2) {
                if ($is_list) {
                    $array[] = $value_2;
                } else {
                    $array[$key_2] = $value_2;
                }
            }
        } else {
            $array[] = $value;
        }
    }

    return $array;
}

/**
 * @param array $array
 * @return bool
 */
function array_is_list(array $array): bool
{
    $expected_key = 0;

    foreach ($array as $key => $value) {
        if ($key !== $expected_key) {
            return false;
        }

        $expected_key++;
    }

    return true;
}

/**
 * @param array $array
 * @param mixed $search
 * @param int|string|null $from_index
 * @return bool
 */
function array_includes(array $array, $search, $from_index = null): bool
{
    $continue = $from_index !== null;

    foreach ($array as $key => $value) {
        if ($continue) {
            if ($from_index === $key) {
                $continue = false;
            }

            continue;
        }

        if ($value === $search) {
            return true;
        }
    }

    return false;
}
