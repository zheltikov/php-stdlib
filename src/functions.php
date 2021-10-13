<?php

namespace Zheltikov\StdLib;

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
