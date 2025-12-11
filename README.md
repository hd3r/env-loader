# env-loader

Lightweight .env file loader for PHP. Zero dependencies.

## Installation

```bash
composer require hd3r/env-loader
```

## Usage

### Basic Usage

```php
use Hd3r\EnvLoader\EnvLoader;

// Loads .env into $_ENV (does not overwrite existing, no required keys)
EnvLoader::load(__DIR__ . '/.env');

echo $_ENV['DB_HOST'];
```

### Options

```php
// Overwrite existing $_ENV variables
EnvLoader::load('.env', overwrite: true);

// Require specific keys (throws exception if missing)
EnvLoader::load('.env', required: ['DB_HOST', 'DB_NAME']);

// Required keys as comma-separated string
EnvLoader::load('.env', required: 'DB_HOST,DB_NAME');

// Combine options
EnvLoader::load('.env', overwrite: true, required: ['DB_HOST']);
```

### Parse Without Loading

```php
// Returns array without setting $_ENV
$values = EnvLoader::parse('.env');

print_r($values);
// ['DB_HOST' => 'localhost', 'DB_NAME' => 'myapp', ...]
```

## Supported .env Syntax

```env
# Comments
DB_HOST=localhost

# Empty values
EMPTY_VAR=

# Values with equals sign
PASSWORD=val=ue=with=equals

# Double quotes (supports escaped quotes)
MESSAGE="Hello World"
ESCAPED="Say \"Hello\""

# Single quotes (no escape processing)
SINGLE='Hello World'

# Inline comments
API_KEY=secret123 # this is ignored
QUOTED="value with # hash" # comment outside quotes

# Whitespace is trimmed
  SPACED_KEY  =  value
```

## Exceptions

All exceptions extend `EnvLoaderException` for easy catching:

```php
use Hd3r\EnvLoader\EnvLoader;
use Hd3r\EnvLoader\Exception\EnvLoaderException;
use Hd3r\EnvLoader\Exception\FileNotFoundException;
use Hd3r\EnvLoader\Exception\MissingRequiredKeyException;

try {
    EnvLoader::load('.env', required: ['API_KEY']);
} catch (FileNotFoundException $e) {
    // File does not exist
} catch (MissingRequiredKeyException $e) {
    // Required key not found
} catch (EnvLoaderException $e) {
    // Any other EnvLoader error
}
```

| Exception | When |
|-----------|------|
| `FileNotFoundException` | File does not exist or is a directory |
| `FileNotReadableException` | File exists but not readable |
| `InvalidKeyException` | Key has invalid format (e.g. `123KEY`, `MY-KEY`) |
| `UnterminatedQuoteException` | Quoted value missing closing quote |
| `MissingRequiredKeyException` | Required key missing after loading |

## Key Naming Rules

Valid keys must:
- Start with a letter or underscore
- Contain only letters, numbers, and underscores

```
DB_HOST      ✓
_PRIVATE     ✓
API_KEY_2    ✓
123KEY       ✗ (starts with number)
MY-KEY       ✗ (contains hyphen)
MY KEY       ✗ (contains space)
```

## Why $_ENV Only?

This library intentionally writes **only to `$_ENV`**, not `putenv()` or `$_SERVER`.

**Reason: Thread Safety**

`putenv()` and `getenv()` are **not thread-safe**. In modern PHP runtimes like:

- Swoole
- RoadRunner
- FrankenPHP
- ReactPHP

...concurrent requests can overwrite each other's environment variables, causing hard-to-debug race conditions.

`$_ENV` is process-local and safe. Use `$_ENV['KEY']` instead of `getenv('KEY')` in your application.

```php
// Safe
$host = $_ENV['DB_HOST'];

// Not recommended (not set by this loader)
$host = getenv('DB_HOST');
```

## When to Use

- Development environments
- Shared hosting where system ENV is not available
- Simple projects without framework
- **Modern async PHP** (Swoole, RoadRunner, FrankenPHP)

## When NOT to Use

- Production with proper system ENV configuration
- When you need variable expansion (`${OTHER_VAR}`)
- When you need multiline values
- When you must support legacy code using `getenv()`

For advanced features, consider [vlucas/phpdotenv](https://github.com/vlucas/phpdotenv).

## Requirements

- PHP ^8.1

## License

MIT
