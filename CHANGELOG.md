# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.3] - 2024-12-14

### Fixed
- Fixed parsing of `.env` files with UTF-8 BOM (Byte Order Mark).
  Files created with Windows Notepad include a BOM (`\xEF\xBB\xBF`) that caused
  `InvalidKeyException` on the first key.

## [1.0.2] - 2024-12-11

### Fixed
- Fixed data corruption in double-quoted values containing backslashes.
  Windows paths like `"C:\Users\name"` were incorrectly processed by `stripcslashes()`,
  which interpreted `\U` and `\n` as escape sequences.
- Only `\"` (escaped quote) and `\\` (escaped backslash) are now unescaped;
  other sequences like `\n`, `\t`, `\r` are preserved literally.
- Added TOCTOU (time-of-check time-of-use) protection for file reading.

### Added
- PHPStan static analysis at level 9 (maximum strictness).
- PHP-CS-Fixer for PSR-12 code style enforcement.
- GitHub Actions CI pipeline testing PHP 8.1, 8.2, 8.3, and 8.4.
- CONTRIBUTING.md with development setup instructions.
- Comprehensive test coverage (41 tests, 100% code coverage).

## [1.0.1] - 2024-12-11

### Fixed
- Strict validation for file paths (directory vs file detection).
- Proper handling of trailing commas in required keys string.
- Validation for unterminated quotes in values.

### Added
- `.env.example` demonstrating all supported syntax.

## [1.0.0] - 2024-12-11

### Added
- Initial release.
- Load `.env` files into `$_ENV` superglobal.
- `parse()` method to get values without setting `$_ENV`.
- Support for double-quoted values with escape sequences (`\"`, `\\`).
- Support for single-quoted values (literal, no escaping).
- Inline comment support (` #` syntax).
- Required keys validation (array or comma-separated string).
- Overwrite option for existing `$_ENV` values.
- Exception hierarchy for granular error handling.

[Unreleased]: https://github.com/hd3r/env-loader/compare/v1.0.2...HEAD
[1.0.2]: https://github.com/hd3r/env-loader/compare/v1.0.1...v1.0.2
[1.0.1]: https://github.com/hd3r/env-loader/compare/v1.0.0...v1.0.1
[1.0.0]: https://github.com/hd3r/env-loader/releases/tag/v1.0.0
