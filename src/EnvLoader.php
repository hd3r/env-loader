<?php

declare(strict_types=1);

namespace Hd3r\EnvLoader;

class EnvLoader
{
    /**
     * Load environment variables from a .env file into $_ENV.
     *
     * @param string $path Path to the .env file
     * @param bool $overwrite Whether to overwrite existing $_ENV variables (default: false)
     * @param array|string $required Required keys - array or comma-separated string
     *
     * @throws Exception\FileNotFoundException If file does not exist
     * @throws Exception\FileNotReadableException If file is not readable
     * @throws Exception\InvalidKeyException If a key has invalid format
     * @throws Exception\MissingRequiredKeyException If a required key is missing after loading
     */
    public static function load(
        string $path,
        bool $overwrite = false,
        array|string $required = []
    ): void {
        $values = self::parse($path);

        foreach ($values as $key => $value) {
            if ($overwrite || !isset($_ENV[$key])) {
                $_ENV[$key] = $value;
            }
        }

        // Handle required keys
        if (is_string($required) && $required !== '') {
            $required = array_map('trim', explode(',', $required));
            $required = array_filter($required, fn($key) => $key !== '');
        }

        foreach ($required as $key) {
            if (!isset($_ENV[$key])) {
                throw new Exception\MissingRequiredKeyException("Missing required key: $key");
            }
        }
    }


    /**
     * Parse a .env file and return key-value pairs without setting $_ENV.
     *
     * @param string $path Path to the .env file
     * @return array<string, string> Parsed key-value pairs
     *
     * @throws Exception\FileNotFoundException If file does not exist
     * @throws Exception\FileNotReadableException If file is not readable
     * @throws Exception\InvalidKeyException If a key has invalid format
     */
    public static function parse(string $path): array
    {
        if (!file_exists($path)) {
            throw new Exception\FileNotFoundException("File not found: $path");
        }

        if (!is_file($path)) {
            throw new Exception\FileNotFoundException("Not a file: $path");
        }

        if (!is_readable($path)) {
            throw new Exception\FileNotReadableException("File not readable: $path");
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $result = [];

        foreach ($lines as $line) {
            $parsed = self::parseLine($line);

            if ($parsed !== null) {
                [$key, $value] = $parsed;
                self::validateKey($key);
                $result[$key] = $value;
            }
        }

        return $result;
    }


    /**
     * Parse a single line into key-value pair.
     *
     * Skips:
     * - Empty lines
     * - Comment lines (starting with #)
     * - Lines without = separator
     *
     * @param string $line Raw line from .env file
     * @return array{0: string, 1: string}|null Key-value pair or null if line should be skipped
     */
    private static function parseLine(string $line): ?array
    {
        $line = trim($line);

        // Skip empty lines and comments
        if ($line === '' || str_starts_with($line, '#')) {
            return null;
        }

        // Must contain =
        if (!str_contains($line, '=')) {
            return null;
        }

        // Split only on first =
        $pos = strpos($line, '=');
        $key = trim(substr($line, 0, $pos));
        $value = substr($line, $pos + 1);

        $value = self::parseValue($value);

        return [$key, $value];
    }

    /**
     * Parse value: handle quotes, escaped characters, and inline comments.
     *
     * Supports:
     * - Double quoted values: "value" (with escaped quotes via \")
     * - Single quoted values: 'value' (no escape processing)
     * - Unquoted values with inline comment removal (after " #")
     *
     * @param string $value Raw value from .env line (everything after =)
     * @return string Processed value with quotes removed and escapes handled
     */
    private static function parseValue(string $value): string
    {
        $value = trim($value);

        if ($value === '') {
            return '';
        }

        // Double quoted
        if (str_starts_with($value, '"')) {
            if (preg_match('/^"(.*?)"\s*(#.*)?$/', $value, $matches)) {
                return stripcslashes($matches[1]);
            }
        }

        // Single quoted
        if (str_starts_with($value, "'")) {
            if (preg_match("/^'(.*?)'\s*(#.*)?$/", $value, $matches)) {
                return $matches[1];
            }
        }

        // Unquoted - remove inline comment
        if (str_contains($value, ' #')) {
            $value = trim(substr($value, 0, strpos($value, ' #')));
        }

        return $value;
    }

    /**
     * Validate key format against standard ENV naming rules.
     *
     * Valid: MY_KEY, _PRIVATE, DB_HOST_1
     * Invalid: 123KEY, MY-KEY, MY KEY
     *
     * @param string $key Key name to validate
     * @throws Exception\InvalidKeyException If key format is invalid
     */
    private static function validateKey(string $key): void
    {
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $key)) {
            throw new Exception\InvalidKeyException("Invalid key: $key");
        }
    }
}