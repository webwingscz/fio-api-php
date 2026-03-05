# Project Instructions

- Always run project shell commands through WSL, not directly in Windows PowerShell.
- Use `wsl.exe --cd /home/dorazil/projects/fio-api-php bash -lc "<command>"` as the default wrapper.
- Prefer running all checks and dependency commands from the WSL project path `/home/dorazil/projects/fio-api-php`.
- Do not call Windows `composer`, `php`, or `docker` for project tasks.
- PHP runtime is containerized; project PHP tooling should run via Docker in WSL.
- Use `docker/docker-compose.yml` as the default compose file for project tasks.
- Prefer project automation via `Makefile` targets where applicable.
- For complex changes, break work into smaller steps and confirm assumptions early.

## Preflight (run before task commands)

- Verify WSL is available: `wsl.exe --status`.
- Verify project path in WSL exists: `wsl.exe --cd /home/dorazil/projects/fio-api-php bash -lc "pwd"`.

## Composer execution policy

- First try native WSL Composer: `wsl.exe --cd /home/dorazil/projects/fio-api-php bash -lc "composer --version"`.
- If Composer is missing in WSL, run Composer via Docker in WSL:
  `wsl.exe --cd /home/dorazil/projects/fio-api-php bash -lc "docker compose -f docker/docker-compose.yml run --rm php composer <args>"`.
- For package pinning/upgrade, prefer `composer require vendor/package:version --with-all-dependencies --no-interaction`
  over `composer update vendor/package` to ensure `composer.json` and `composer.lock` stay aligned.

## Docker execution policy

- When working with Docker for this project, always use Docker inside WSL:
  `wsl.exe --cd /home/dorazil/projects/fio-api-php bash -lc "docker <args>"`.
- Git operations run in WSL on the host project checkout (not inside the PHP container).

## Verification after dependency changes

- Confirm target package version in `composer.lock`.
- Run `git status --short` and report which dependency files changed.
- If unrelated dependency changes appear, report them explicitly instead of silently ignoring them.
