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
        // TODO: Implementation
    }

    public static function parse(string $path): array
    {
        // TODO: Implementation
        return [];
    }

    private static function parseLine(string $line): ?array
    {
        // TODO: Implementation
        return null;
    }

    private static function parseValue(string $value): string
    {
        // TODO: Implementation
        return '';
    }

    private static function validateKey(string $key): void
    {
        // TODO: Implementation
    }
}