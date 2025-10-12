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
    docker-compose up -d --build
    ```

3.  **Install Composer dependencies:**

    Run the following command to install the project's PHP dependencies:

    ```bash
    docker-compose exec php sh -c "curl -sS https://getcomposer.org/installer | php && php composer.phar install"
    ```

4.  **Complete the web-based installation:**

    - Open your web browser and navigate to `http://localhost:8080/install/install.php`.
    - Follow the on-screen instructions to configure the application.
    - For the database setup, use the following credentials:
      - **Database Host:** `db`
      - **Database Name:** `streber`
      - **Database User:** `user`
      - **Database Password:** `password`

    After the installation is complete, a `db_settings.php` file will be created in the `_settings/` directory. This file is ignored by Git.

5.  **Access the application:**

    Once the installation is complete, you can access the application at `http://localhost:8080`.

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