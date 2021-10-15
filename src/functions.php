<?php

namespace Zheltikov\StdLib;

use Generator;
use OutOfBoundsException;
use RangeException;
use Throwable;

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

/**
 * @param array $array
 * @return string
 */
function array_to_source(array $array): string
{
    $source = '[';
    $length = count($array);
    $index = 0;
    $is_list = array_is_list($array);

    foreach ($array as $key => $value) {
        if (!$is_list) {
            $source .= var_export($key, true);
            $source .= ' => ';
        }

        if (is_array($value)) {
            $source .= array_to_source($value);
        } else {
            $source .= var_export($value, true);
        }

        if ($index + 1 < $length) {
            $source .= ', ';
        }
        $index++;
    }

    $source .= ']';

    return $source;
}

/**
 * @param array $entries
 * @return array
 */
function array_from_entries(array $entries): array
{
    $array = [];

    foreach ($entries as [$key, $value]) {
        $array[$key] = $value;
    }

    return $array;
}

/**
 * @param array $array
 * @param int $depth
 * @return array
 */
function array_flat(array $array, int $depth = 1): array
{
    return $depth > 0
        ? array_reduce(
            $array,
            function ($carry, $value) use ($depth) {
                return array_concat(
                    $carry,
                    is_array($value)
                        ? array_flat($value, $depth - 1)
                        : $value
                );
            },
            []
        )
        : $array;
}

/**
 * @param float $number
 * @param int $digits
 * @return float
 */
function number_to_fixed(float $number, int $digits): float
{
    if ($digits < 0) {
        throw new RangeException('The $digits argument must be positive');
    }

    $mover = pow(10, $digits);
    $str = (string) ($number * $mover);

    $capped = $number < 0
        ? ceil($str)
        : floor($str);

    return $capped / $mover;
}

/**
 * @param float $number
 * @return int
 */
function number_trunc(float $number): int
{
    return (int) $number;
}

/**
 * @return float
 * @throws \Exception
 */
function math_random(): float
{
    return random_int(0, PHP_INT_MAX) / PHP_INT_MAX;
}

/**
 * @param \Throwable $e
 */
function print_throwable(Throwable $e): void
{
    echo get_class($e) . ': ' . $e->getMessage() . "\n";
}

/**
 * @param int $num
 * @param array $args
 * @param string $name
 */
function assert_num_args(int $num, array $args, string $name): void
{
    if ($num !== count($args)) {
        die(sprintf('Wrong argument count for %s().', $name));
    }
}

/**
 * @param string $lines
 * @return string
 */
function rtrim_lines(string $lines): string
{
    $lines = explode("\n", $lines);
    $lines = array_map('rtrim', $lines);
    return implode("\n", $lines);
}

/**
 * @param string $dir
 * @param int $permissions
 */
function ensure_dir_exists(string $dir, int $permissions = 0755): void
{
    if (is_dir($dir) === false) {
        if (mkdir($dir, $permissions, true) === false) {
            throw new FileSystemException(sprintf('Failed to create %s directory.', $dir));
        }
    }
}

/**
 * @param string $cmd
 * @return string
 */
function exec_cmd(string $cmd): string
{
    $output = trim(shell_exec($cmd . ' 2>&1'));

    if ($output !== '') {
        printf("> %s\n%s", $cmd, $output);
    }

    return $output;
}

/**
 * @param string $location
 */
function redirect(string $location): void
{
    header('Location: ' . $location);
    exit();
}

/**
 * @param mixed $value
 * @param mixed $default
 * @return mixed
 */
function empty_default($value, $default)
{
    return empty($value) ? $default : $value;
}

/**
 * @param mixed $value
 * @param mixed $default
 * @return mixed
 */
function isset_default(&$value, $default)
{
    return isset($value) ? $value : $default;
}

/**
 * @param mixed $value
 * @param mixed $default
 * @return mixed|string
 */
function ie_def(&$value, $default)
{
    $value_2 = isset($value) ? trim($value) : $default;
    return empty($value_2) ? $default : $value_2;
}

/**
 * @param string $source
 * @param string $target
 * @return int
 */
function download_file(string $source, string $target): int
{
    return file_put_contents($target, file_get_contents($source));
}

/**
 * @param mixed $expr
 * @param mixed $def
 * @return mixed
 */
function false_def($expr, $def)
{
    if ($expr === false) {
        return $def;
    }
    return $expr;
}

/**
 * @param bool $undefined
 * @return string
 */
function random_js_falsy(bool $undefined = false): string
{
    $array = ['""', '0', 'false', 'null'];
    if ($undefined) {
        $array[] = 'undefined';
    }
    return $array[array_rand($array)];
}

/**
 * @param string $algo
 * @param int $length
 * @return string
 * @throws \Exception
 */
function get_a_nonce(string $algo = 'crc32', int $length = 4): string
{
    static $nonces = [];

    do {
        $nonce = substr(
            base_convert(
                hash($algo, math_random()),
                16,
                36
            ),
            0,
            $length
        );
    } while (in_array($nonce, $nonces));

    $nonces[] = $nonce;

    return $nonce;
}

/**
 * @param int $count
 * @param callable $action
 * @return bool
 */
function repeat(int $count, callable $action): bool
{
    for ($i = 0; $i < $count; $i++) {
        if (call_user_func($action, $i) === false) {
            return false;
        }
    }

    return true;
}

/**
 * @param array $items
 * @param callable $action
 * @return bool
 */
function do_each(array $items, callable $action): bool
{
    foreach ($items as $key => $value) {
        if (call_user_func($action, $key, $value) === false) {
            return false;
        }
    }

    return true;
}

/**
 * @param array $one
 * @param array $two
 * @return array
 */
function deep_merge(array $one, array $two): array
{
    $three = $one;
    foreach ($two as $key => $value) {
        if (is_array($one[$key] ?? null) && is_array($value)) {
            $three[$key] = deep_merge($one[$key], $value);
        } else {
            $three[$key] = $value;
        }
    }

    return $three;
}

/**
 * @param array $data
 * @return \stdClass
 */
function objectify(array $data = []): \stdClass
{
    $object = new \stdClass();

    foreach ($data as $key => $value) {
        $object->{$key} = $value;
    }

    return $object;
}

/**
 * @param array $one
 * @return \stdClass
 */
function deep_objectify(array $one): \stdClass
{
    $two = [];

    foreach ($one as $key => $value) {
        if (is_array($value) && array_is_associative($value)) {
            $two[$key] = objectify($value);
        } else {
            $two[$key] = $value;
        }
    }

    return objectify($two);
}

/**
 * @param string $email
 * @return array
 */
function email_encode(string $email): array
{
    return array_map('base64_encode', explode('@', $email));
}

/**
 * @param array $encoded
 * @return string
 */
function email_decode(array $encoded): string
{
    return implode('@', array_map('base64_decode', $encoded));
}

/**
 * @param array $array
 * @return bool
 */
function array_is_associative(array $array): bool
{
    if ([] === $array) {
        return false;
    }

    return array_keys($array) !== range(0, count($array) - 1);
}

/**
 * @param array $array
 * @return array
 */
function array_shuffle(array $array): array
{
    shuffle($array);
    return $array;
}

/**
 * @param array $input
 * @param int $num
 * @param bool $preserve_keys
 * @return array|mixed
 */
function array_random(array $input, int $num = 1, bool $preserve_keys = false)
{
    $result = [];
    $keys = array_rand($input, $num);
    $keys = is_array($keys) ? $keys : [$keys];

    foreach ($keys as $k) {
        $result[$preserve_keys ? $k : count($result)] = $input[$k];
    }

    return count($result) === 1 ? $result[0] : $result;
}

/**
 * @return array
 */
function get_terminal_size(): array
{
    // TODO: do some error checking here
    // TODO: be able to pass some "default" value, i.e. 80x24
    // TODO: add a check for another OS, e.g. Windows

    $column_output = [];
    exec('tput cols', $column_output);

    $line_output = [];
    exec('tput lines', $line_output);

    return [
        trim($column_output[0]),
        trim($line_output[0]),
    ];
}

/**
 * @param string $prompt
 * @param int $length
 * @param string $ending
 * @return string
 */
function read_line(string $prompt = '', int $length = 1024, string $ending = "\n"): string
{
    if ($prompt !== '') {
        echo $prompt;
    }

    return stream_get_line(STDIN, $length, $ending);
}

/**
 * @param int $length
 * @param bool $raw
 * @return string
 */
function openssl_random_pseudo_bytes(int $length, bool $raw = false): string
{
    if (function_exists('\openssl_random_pseudo_bytes')) {
        $data = \openssl_random_pseudo_bytes($length);
    } else {
        $handle = popen('/usr/bin/openssl rand ' . $length, 'r');
        $data = stream_get_contents($handle);
        pclose($handle);
    }
    
    return $raw ? $data : bin2hex($data);
}

/**
 * @param int $length
 * @return string
 */
function base64_random_bytes(int $length): string
{
    return base64_encode(openssl_random_pseudo_bytes($length, true));
}

/**
 * @param string $data
 * @param bool $trim
 * @return string
 */
function base64_encode(string $data, bool $trim = true): string
{
    $encoded = \base64_encode($data);
    return $trim
        ? rtrim($encoded, '=')
        : $encoded;
}

/**
 * @param string $data
 * @param bool $strict
 * @return string
 */
function base64_decode(string $data, bool $strict = false): string
{
    return \base64_decode($data, $strict);
}

/**
 * @param bool $raw_output
 * @return string
 * @throws \Exception
 */
function random_md5(bool $raw_output = false): string
{
    return random_hash('md5', $raw_output);
}

/**
 * @param string $algo
 * @param bool $raw_output
 * @return string
 * @throws \Exception
 */
function random_hash(string $algo, bool $raw_output = false): string
{
    return hash(
        $algo,
        openssl_random_pseudo_bytes(
            random_int(0, hash_algos_longest()),
            true
        ),
        $raw_output
    );
}

/**
 * @return int
 */
function hash_algos_longest(): int
{
    static $longest = null;

    if ($longest === null) {
        $longest = 0;
        $hash_algos = hash_algos();
        foreach ($hash_algos as $hash_algo) {
            $length = strlen(hash($hash_algo, ''));
            if ($length > $longest) {
                $longest = $length;
            }
        }
    }

    return $longest;
}
