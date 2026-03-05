# Contributing

Thank you for contributing to `webwingscz/fio-api-php`.

## Development Setup

Use WSL and project Docker setup:

```bash
wsl.exe --cd /home/dorazil/projects/fio-api-php bash -lc "docker compose -f docker/local/docker-compose.yml run --rm php composer install"
```

## Validation Before PR

Run full checks before opening a PR:

```bash
wsl.exe --cd /home/dorazil/projects/fio-api-php bash -lc "docker compose -f docker/local/docker-compose.yml run --rm php composer ci"
```

## Pull Request Rules

- Keep PRs focused and small.
- Include tests for behavior changes and bug fixes.
- Keep public API backward compatible within the same major line.
- Update README or changelog notes when behavior/configuration changes.

## Commit Message Guidance

Use clear, imperative commit messages, for example:
- `Fix PSR-12 formatting in uploader entities`
- `Handle invalid JSON response in downloader`
