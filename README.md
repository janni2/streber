# Streber Project Management

Streber is a free, wiki-driven project management tool written in PHP. This document provides instructions for setting up and running the application in a local development environment using Podman.

## Prerequisites

- [Podman](https://podman.io/getting-started/installation) (or [Docker](https://www.docker.com/get-started) as an alternative)
- Podman 4.0+ includes compose support built-in (or [Docker Compose](https://docs.docker.com/compose/install/))

## Local Development Setup

1.  **Clone the repository:**

    ```bash
    git clone https://github.com/your-username/streber.git
    cd streber
    ```

2.  **Build and start the containers:**

    ```bash
    podman compose up --build
    ```

    (Or with Docker: `docker compose up --build`)

    This will start two services:
    - `php`: PHP 8.3 with Apache web server (port 8080)
    - `db`: MySQL 5.7 database server (port 3306)

3.  **Complete the web-based installation:**

    - Open your web browser and navigate to `http://localhost:8080/install/install.php`.
    - Follow the on-screen instructions to configure the application.
    - **IMPORTANT:** For the database setup, use the following credentials:
      - **Database Host:** `db` ⚠️ **NOT** `localhost` (use the container service name)
      - **Database Name:** `streber`
      - **Database User:** `user` (or `root` for root access)
      - **Database Password:** `password` (or `rootpassword` for root)

    After the installation is complete, a `db_settings.php` file will be created in the `_settings/` directory. This file is ignored by Git.

    **Why `db` and not `localhost`?** In container environments (Podman/Docker Compose), services communicate via service names defined in `docker-compose.yml`. The PHP container cannot reach MySQL via `localhost` because that refers to the PHP container itself. Use `db` to connect to the MySQL service.

4.  **Access the application:**

    Once the installation is complete, you can access the application at `http://localhost:8080`.

## Container Architecture

The container setup consists of:

- **PHP Container**: Runs PHP 8.3 with Apache, includes Composer for dependency management
- **MySQL Container**: Runs MySQL 5.7 for the database
- **Named Volumes**: Persistent storage for:
  - `_settings/` - Database configuration (generated during installation)
  - `_tmp/` - Temporary files and caches
  - `_files/` - User-uploaded files
  - `_image_cache/` - Cached images
  - `_rss/` - RSS feed cache
  - `db_data` - MySQL database files

The application source code is mounted from your host into the container, so changes take effect immediately (no rebuild needed for PHP code changes).

## Troubleshooting

### "What a Bummer! The wiki is offline" / Maintenance View

If you see this message with "could not connect to database", it means the database settings file is missing or incorrect. This typically happens when:

1. **First-time setup**: You haven't run the installer yet
2. **Volume reset**: The `_settings/` volume was deleted or cleared
3. **Wrong database type**: The installer was configured with MySQL instead of MySQLi

**Solution:**

1. Navigate to the installer: `http://localhost:8080/install/install.php`
2. Fill in the database configuration:
   - **Database Server**: `db` (NOT `localhost`)
   - **Database Name**: `streber`
   - **Username**: `user`
   - **Password**: `password`
   - **Table Prefix**: `streber_` (or leave default)
   - **Database Type**: Select **MySQLi** ⚠️ (NOT MySQL - the old extension is not available in PHP 8)
3. Complete the installation wizard
4. After successful installation, remove the installer by visiting: `http://localhost:8080/install/remove_install_dir.php`

**Note:** The installer creates `_settings/db_settings.php` which contains the database connection configuration. This file is stored in a named volume and is not tracked in Git.

### "No such file or directory" MySQL connection error

If you see `mysqli_sql_exception: No such file or directory`, you likely entered `localhost` as the database host during installation. Use `db` instead.

### Permission errors for `_settings/`, `_tmp/`, etc.

These directories use named volumes with proper permissions. If you see permission errors, try:

```bash
podman compose down
podman compose up --build
```

(Or with Docker: `docker compose down && docker compose up --build`)

### Container won't start or build fails

Check for:
- Port conflicts (8080, 3306 already in use)
- Podman/Docker service running
- Sufficient disk space for volumes

View logs:
```bash
podman compose logs php
podman compose logs db
```

(Or with Docker: `docker compose logs php` and `docker compose logs db`)

## Development Tasks

You can run various development tasks using `podman compose exec` (or `docker compose exec`).

### Running Tests

To run the PHPUnit test suite, use the following command:

```bash
podman compose exec php vendor/bin/phpunit
```

(Or with Docker: `docker compose exec php vendor/bin/phpunit`)

### Static Analysis

To run PHPStan for static analysis, use the following command:

```bash
podman compose exec php vendor/bin/phpstan analyse
```

(Or with Docker: `docker compose exec php vendor/bin/phpstan analyse`)

### Code Style

To check for code style violations, run:

```bash
podman compose exec php vendor/bin/php-cs-fixer fix --dry-run --diff
```

To automatically fix code style issues, run:

```bash
podman compose exec php vendor/bin/php-cs-fixer fix
```

(Or with Docker: replace `podman compose` with `docker compose`)