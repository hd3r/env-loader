<?php

declare(strict_types=1);

namespace Hd3r\EnvLoader;

class EnvLoader
{
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
        }

        foreach ($required as $key) {
            if (!isset($_ENV[$key])) {
                throw new Exception\MissingRequiredKeyException("Missing required key: $key");
            }
        }
    }


    public static function parse(string $path): array
    {
        if (!file_exists($path)) {
            throw new Exception\FileNotFoundException("File not found: $path");
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

    private static function validateKey(string $key): void
    {
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $key)) {
            throw new Exception\InvalidKeyException("Invalid key: $key");
        }
    }
}