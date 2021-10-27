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

// -----------------------------------------------------------------------------

/**
 * @param string|null $version
 * @param bool $min
 * @return string
 */
function jquery_google_cdn_link(?string $version = null, bool $min = JQUERY_MIN): string
{
    return sprintf(
        'https://ajax.googleapis.com/ajax/libs/jquery/%s/jquery%s.js',
        $version ?? JQUERY_VERSION,
        $min ? '.min' : ''
    );
}

/**
 * @param string|null $version
 * @param bool $min
 * @return string
 */
function jquery_cdn_script(?string $version = null, bool $min = JQUERY_MIN): string
{
    return sprintf(
        '<script src="%s"></script>',
        htmlspecialchars(
            jquery_google_cdn_link($version ?? JQUERY_VERSION, $min)
        )
    );
}

/**
 * @param $value
 * @param int $options
 * @param int $depth
 * @return string
 */
function json_colorize($value, int $options = 0, int $depth = 512): string
{
    $json = json_encode(
        json_decode(
            json_encode($value, $options, $depth)
        ),
        JSON_PRETTY_PRINT
    );

    // TODO: find a prettier way of doing this

    // Object string key
    $pattern = '/("(.+)?":)/imU';
    $replacement = '<span style="color:#75507b;">"$2"</span>:';
    $json = preg_replace($pattern, $replacement, $json);

    // String object value
    $pattern = '/(: "(.+)?")/imU';
    $replacement = ': <span style="color:#cc0000;">"$2"</span>';
    $json = preg_replace($pattern, $replacement, $json);

    // String value
    $pattern = '/^((\s+)"(.+)?")/imU';
    $replacement = '$2<span style="color:#cc0000;">"$3"</span>';
    $json = preg_replace($pattern, $replacement, $json);

    // Boolean false
    $pattern = '/^((\s+)false)/imU';
    $replacement = '$2<span style="color:#f57900;">false</span>';
    $json = preg_replace($pattern, $replacement, $json);

    // Boolean true
    $pattern = '/^((\s+)true)/imU';
    $replacement = '$2<span style="color:#f57900;">true</span>';
    $json = preg_replace($pattern, $replacement, $json);

    // Number, why can it contain spaces?
    $pattern = '/^((\s+)([0-9 \\.]*?))/imU';
    $replacement = '$2<span style="color:#3465a4;">$3</span>';
    $json = preg_replace($pattern, $replacement, $json);

    // Object value boolean false
    $pattern = ': false';
    $replacement = ': <span style="color:#f57900;">false</span>';
    $json = str_replace($pattern, $replacement, $json);

    // Object value boolean true
    $pattern = ': true';
    $replacement = ': <span style="color:#f57900;">true</span>';
    $json = str_replace($pattern, $replacement, $json);

    // Number, why can it contain spaces?
    $pattern = '/(: ([0-9 \\.]*?))/imU';
    $replacement = ': <span style="color:#3465a4;">$2</span>';
    $json = preg_replace($pattern, $replacement, $json);

    return '<pre style="font-size: 16px; color: inherit; text-align: left;">' . $json . '</pre>';
}

/**
 * @param string $string
 * @return int
 */
function strlen(string $string): int
{
    if (function_exists('mb_strlen')) {
        return mb_strlen($string);
    }
    return \strlen($string);
}

/**
 * @param string $s
 * @return string
 */
function css_url(string $s): string
{
    $unquoted = str_replace(
        ['(', ')', "\n", "\t", ' ', "'", '"', ','],
        ['\\(', '\\)', "\\\n", "\\\t", '\\ ', "\\'", '\\"', '\\,'],
        $s
    );

    $quoted_s = "'" . str_replace(
            ["'", "\n"],
            ["\\'", "\\\n"],
            $s
        ) . "'";

    $quoted_d = '"' . str_replace(
            ['"', "\n"],
            ['\\"', "\\\n"],
            $s
        ) . '"';

    $variants = [
        $unquoted,
        $quoted_s,
        $quoted_d,
    ];
    usort(
        $variants,
        function (string $a, string $b): int {
            return strlen($a) - strlen($b);
        }
    );

    return 'url(' . $variants[0] . ')';
}

/**
 * @param string $string
 * @param string $separator
 * @param int $index
 * @return string
 */
function inject_every_index(string $string, string $separator, int $index): string
{
    $output = '';
    $length = strlen($string);
    for ($j = 0; $j < $length; $j++) {
        if ($j % $index === 0 && $j !== 0) {
            $output .= $separator;
        }
        $output .= $string[$j];
    }
    return $output;
}

/**
 * @param string $string
 * @return string
 */
function normalize_new_lines(string $string): string
{
    return preg_replace("/\\r\\n?/", "\n", $string);
}

/**
 * @param array $input_segments
 * @param callable $min_extractor
 * @param callable $max_extractor
 * @param float $min_step
 * @param float $max_step
 * @param float $min_gap
 * @param float $max_gap
 * @param bool $sort_input
 * @return array{bool, ?array-key}
 */
function check_segments(
    array &$input_segments,
    callable $min_extractor,
    callable $max_extractor,
    float $min_step,
    float $max_step,
    float $min_gap,
    float $max_gap,
    bool $sort_input = true
): array {
    if ($sort_input) {
        $segments =& $input_segments;
    } else {
        $segments = $input_segments;
    }

    // Sort the input array so that the segments are in ascending order
    usort(
        $segments,
        function ($a, $b) use ($max_extractor, $min_extractor): float {
            /** @var int|float $a_min */
            $a_min = $min_extractor($a);

            /** @var int|float $b_min */
            $b_min = $min_extractor($b);

            /** @var int|float $min_diff */
            $min_diff = $a_min - $b_min;

            // First, sort by the min value
            if ((float) $min_diff !== (float) 0) {
                return $min_diff;
            }

            /** @var int|float $a_max */
            $a_max = $max_extractor($a);

            /** @var int|float $b_max */
            $b_max = $max_extractor($b);

            // Then, sort by the max value
            return $a_max - $b_max;
        }
    );

    /** @var int|float|null $prev_max */
    $prev_max = null;
    $continue = true;

    // Iterate through the segments and check their lengths and boundaries
    foreach ($segments as $index => $segment) {
        /** @var int|float $min */
        $min = $min_extractor($segment);

        /** @var int|float $max */
        $max = $max_extractor($segment);

        /** @var int|float $max */
        $step = $max - $min;

        // Check the segment length
        if ($step < $min_step || $step > $max_step) {
            // Bad, the length of the current segment does not satisfy the
            // conditions
            return [false, $index];
        }

        // Skip the first segment, as we cannot check it against any
        // previous segment
        if ($continue) {
            $continue = false;
            $prev_max = $max;
            continue;
        }

        /** @var int|float $gap */
        $gap = $min - $prev_max;

        // Check the boundaries of the current segment with the previous one
        if ($gap < $min_gap || $gap > $max_gap) {
            // Bad, the gap between the segments does not satisfy the
            // conditions
            return [false, $index];
        }

        $prev_max = $max;
    }

    // Good, all segments satisfy the conditions
    return [true, null];
}

/**
 * @param float $lat
 * @param float $lon
 * @return bool
 */
function check_coordinates(float $lat, float $lon): bool
{
    $lat_valid = -90 <= $lat && $lat <= 90;
    $lon_valid = -180 <= $lon && $lon <= 180;
    return $lat_valid && $lon_valid;
}
