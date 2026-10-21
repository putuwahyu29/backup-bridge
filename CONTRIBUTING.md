# Contributing to Backup Bridge

Thank you for considering contributing! Here are guidelines to help you get started.

## Development Setup

1. Fork and clone the repository.
2. Install PHP dependencies:
   ```bash
   composer install
   ```
3. Run PHP syntax validation:
   ```bash
   find src -name "*.php" -exec php -l {} \;
   ```

## Pull Request Guidelines

- Follow PSR-12 coding standards.
- Keep commits descriptive and atomic.
- Update `CHANGELOG.md` with your additions, fixes, or breaking changes.
- Ensure all public methods have proper docblocks and types.
