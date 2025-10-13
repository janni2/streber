# Streber Project Management

Streber is a free, wiki-driven project management tool written in PHP. This document provides instructions for setting up and running the application in a local development environment using Docker.

## Prerequisites

- [Docker](https://www.docker.com/get-started)
- [Docker Compose](https://docs.docker.com/compose/install/)

## Local Development Setup

1.  **Clone the repository:**

    ```bash
    git clone https://github.com/your-username/streber.git
    cd streber
    ```

2.  **Build and start the Docker containers:**

    ```bash
    docker-compose up --build
    ```

    This will start two services:
    - `php`: PHP 8.3 with Apache web server (port 8080)
    - `db`: MySQL 5.7 database server (port 3306)

3.  **Complete the web-based installation:**

    - Open your web browser and navigate to `http://localhost:8080/install/install.php`.
    - Follow the on-screen instructions to configure the application.
    - **IMPORTANT:** For the database setup, use the following credentials:
      - **Database Host:** `db` ⚠️ **NOT** `localhost` (use the Docker service name)
      - **Database Name:** `streber`
      - **Database User:** `user` (or `root` for root access)
      - **Database Password:** `password` (or `rootpassword` for root)

    After the installation is complete, a `db_settings.php` file will be created in the `_settings/` directory. This file is ignored by Git.

    **Why `db` and not `localhost`?** In Docker Compose, services communicate via service names defined in `docker-compose.yml`. The PHP container cannot reach MySQL via `localhost` because that refers to the PHP container itself. Use `db` to connect to the MySQL service.

4.  **Access the application:**

    Once the installation is complete, you can access the application at `http://localhost:8080`.

## Docker Architecture

The Docker setup consists of:

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

### "No such file or directory" MySQL connection error

If you see `mysqli_sql_exception: No such file or directory`, you likely entered `localhost` as the database host during installation. Use `db` instead.

### Permission errors for `_settings/`, `_tmp/`, etc.

These directories use Docker named volumes with proper permissions. If you see permission errors, try:

```bash
docker-compose down
docker-compose up --build
```

### Container won't start or build fails

Check for:
- Port conflicts (8080, 3306 already in use)
- Docker daemon running
- Sufficient disk space for volumes

View logs:
```bash
docker-compose logs php
docker-compose logs db
```

## Development Tasks

You can run various development tasks using `docker-compose exec`.

### Running Tests

To run the PHPUnit test suite, use the following command:

```bash
docker-compose exec php vendor/bin/phpunit
```

### Static Analysis

To run PHPStan for static analysis, use the following command:

```bash
docker-compose exec php vendor/bin/phpstan analyse
```

### Code Style

To check for code style violations, run:

```bash
docker-compose exec php vendor/bin/php-cs-fixer fix --dry-run --diff
```

To automatically fix code style issues, run:

```bash
docker-compose exec php vendor/bin/php-cs-fixer fix
```