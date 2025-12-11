<?php

declare(strict_types=1);

namespace Hd3r\EnvLoader\Exception;

/**
 * Thrown when the .env file exists but is not readable.
 */
class FileNotReadableException extends EnvLoaderException
{
}
