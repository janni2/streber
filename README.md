# Streber Project Management

Streber is a free, wiki-driven project management tool written in PHP. This document provides instructions for setting up and running the application in a local development environment using Podman.

## Prerequisites

- [Podman](https://podman.io/getting-started/installation) 4.0+ (includes compose support) or [Docker](https://www.docker.com/get-started) with [Docker Compose](https://docs.docker.com/compose/install/)

**Note:** All `podman compose` commands below can be replaced with `docker compose` if using Docker.

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

    This will start two services:
    - `php`: PHP 8.3 with Apache web server (port 8080)
    - `db`: MySQL 5.7 database server (port 3306)

3.  **Complete the web-based installation:**

    - Open your web browser and navigate to `http://localhost:8080/install/install.php`.
    - Follow the on-screen instructions to configure the application.
    - **IMPORTANT:** For the database setup, use these credentials:
      - **Database Host:** `db` ⚠️ (NOT `localhost` - see troubleshooting below)
      - **Database Name:** `streber`
      - **Database User:** `user` (or `root`)
      - **Database Password:** `password` (or `rootpassword`)
      - **Database Type:** Select **MySQLi** ⚠️ (NOT MySQL - old extension unavailable in PHP 8)

    After installation completes, `_settings/db_settings.php` will be created (ignored by Git).

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

## Production Deployment (Bare-Metal)

For production environments using a traditional Apache/MySQL setup on bare-metal servers or VMs:

### Prerequisites

- PHP 8.1+ with extensions:
  - `mysqli` (required - MySQL/MariaDB connectivity)
  - `gd` (required - image manipulation)
  - `mbstring` (required - multibyte string handling)
  - `xml` (required)
  - `zip` (optional - for backups)
- Apache 2.4+ with `mod_rewrite` (optional but recommended)
- MySQL 5.7+ or MariaDB 10.3+
- Composer (for dependency management)

### Installation Steps

1.  **Install system dependencies:**

    ```bash
    # Ubuntu/Debian
    sudo apt update
    sudo apt install apache2 mysql-server php8.3 php8.3-mysql php8.3-gd \
                     php8.3-mbstring php8.3-xml php8.3-zip libapache2-mod-php8.3

    # Enable Apache modules
    sudo a2enmod rewrite
    sudo systemctl restart apache2
    ```

2.  **Clone the repository:**

    ```bash
    cd /var/www
    sudo git clone https://github.com/your-username/streber.git
    sudo chown -R www-data:www-data streber
    ```

3.  **Install Composer dependencies:**

    ```bash
    cd /var/www/streber
    sudo -u www-data php composer.phar install --no-dev --optimize-autoloader
    ```

4.  **Create MySQL database and user:**

    ```bash
    sudo mysql -u root -p
    ```

    ```sql
    CREATE DATABASE streber CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    CREATE USER 'streber_user'@'localhost' IDENTIFIED BY 'secure_password_here';
    GRANT ALL PRIVILEGES ON streber.* TO 'streber_user'@'localhost';
    FLUSH PRIVILEGES;
    EXIT;
    ```

5.  **Set file permissions:**

    ```bash
    cd /var/www/streber

    # Create directories for writable content
    sudo mkdir -p _settings _tmp _files _image_cache _rss

    # Set ownership and permissions
    sudo chown -R www-data:www-data _settings _tmp _files _image_cache _rss
    sudo chmod 755 _settings _tmp _files _image_cache _rss

    # Make logs writable
    sudo touch errors.log.php
    sudo chown www-data:www-data errors.log.php
    sudo chmod 644 errors.log.php
    ```

6.  **Configure Apache virtual host:**

    Create `/etc/apache2/sites-available/streber.conf`:

    ```apache
    <VirtualHost *:80>
        ServerName streber.example.com
        ServerAdmin admin@example.com

        DocumentRoot /var/www/streber

        <Directory /var/www/streber>
            Options -Indexes +FollowSymLinks
            AllowOverride All
            Require all granted

            # Redirect all requests to index.php (if not using mod_rewrite)
            DirectoryIndex index.php
        </Directory>

        # Security: Deny access to sensitive directories
        <Directory /var/www/streber/_settings>
            Require all denied
        </Directory>
        <Directory /var/www/streber/install>
            Require all denied
        </Directory>

        # Logging
        ErrorLog ${APACHE_LOG_DIR}/streber_error.log
        CustomLog ${APACHE_LOG_DIR}/streber_access.log combined
    </VirtualHost>
    ```

    Enable the site:

    ```bash
    sudo a2ensite streber.conf
    sudo systemctl reload apache2
    ```

7.  **Run the web installer:**

    - Navigate to `http://streber.example.com/install/install.php` (temporarily allow access)
    - Use these database settings:
      - **Database Host:** `localhost`
      - **Database Name:** `streber`
      - **Database User:** `streber_user`
      - **Database Password:** (the password you created in step 4)
      - **Database Type:** Select **MySQLi**

8.  **Secure the installation:**

    After installation completes:

    ```bash
    # Remove or rename the install directory
    sudo rm -rf /var/www/streber/install

    # Or if you prefer to keep it, deny web access (already done in Apache config above)
    ```

9.  **Production configuration tweaks:**

    Edit `/var/www/streber/customize.inc.php`:

    ```php
    <?php
    // Production settings
    confChange('LOG_LEVEL', LOG_MESSAGE_ERROR);  // Only log errors
    confChange('DISPLAY_ERROR_LIST', 'NONE');    // Hide error details from users
    confChange('USE_MOD_REWRITE', true);         // Enable clean URLs (if mod_rewrite is enabled)

    // Email settings
    confChange('EMAIL_ADMINISTRATOR', 'admin@example.com');
    confChange('NOTIFICATION_EMAIL_SENDER', 'Streber PM <noreply@example.com>');

    // Performance
    confChange('USE_PROFILER', false);  // Disable profiling in production
    ```

### Security Hardening

1.  **Disable directory listing:** Ensure `Options -Indexes` is set in Apache config
2.  **Restrict file uploads:** Configure `upload_max_filesize` and `post_max_size` in `php.ini`
3.  **Enable HTTPS:** Use Let's Encrypt or another SSL certificate provider
4.  **Database security:** Use strong passwords, restrict MySQL to localhost
5.  **Remove development tools:** The NOTICE messages are development warnings - disable them by setting `LOG_LEVEL` to `LOG_MESSAGE_ERROR` in `customize.inc.php`
6.  **Regular backups:** Backup both the database and `_files/` directory regularly

### Error Logging

In production, errors are logged to `errors.log.php` instead of being displayed. Monitor this file:

```bash
sudo tail -f /var/www/streber/errors.log.php
```

To disable development notices (like "Only variables should be passed by reference"), ensure your `customize.inc.php` has:

```php
confChange('LOG_LEVEL', LOG_MESSAGE_ERROR);  // Only log actual errors, not notices
confChange('DISPLAY_ERROR_LIST', 'NONE');    // Never show errors to end users
```

### Performance Optimization

1.  **Enable PHP OpCache:** Edit `/etc/php/8.3/apache2/php.ini`:
    ```ini
    opcache.enable=1
    opcache.memory_consumption=128
    opcache.interned_strings_buffer=8
    opcache.max_accelerated_files=10000
    ```

2.  **Database optimization:** Regularly optimize tables:
    ```sql
    USE streber;
    OPTIMIZE TABLE task, project, person, effort, comment;
    ```

3.  **Enable Apache caching:** Configure `mod_expires` for static assets

## Troubleshooting

### "What a Bummer! The wiki is offline" / Maintenance View

If you see this message with "could not connect to database", the database settings file is missing or incorrect. Common causes:

1. **First-time setup**: You haven't run the installer yet
2. **Volume reset**: The `_settings/` volume was deleted or cleared
3. **Wrong database type**: MySQL extension selected instead of MySQLi

**Solution:** Run the installer at `http://localhost:8080/install/install.php` with the database credentials from step 3 above. After successful installation, remove the installer: `http://localhost:8080/install/remove_install_dir.php`

### "No such file or directory" MySQL connection error

If you see `mysqli_sql_exception: No such file or directory`, you entered `localhost` as the database host.

**Why use `db` instead of `localhost`?** In container environments, services communicate via service names defined in `docker-compose.yml`. The PHP container cannot reach MySQL via `localhost` (which refers to the PHP container itself). Use `db` to connect to the MySQL service.

Re-run the installer with the correct host: `db`

### Permission errors for `_settings/`, `_tmp/`, etc.

These directories use named volumes with proper permissions. If you see permission errors, try:

```bash
podman compose down
podman compose up --build
```

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

## Development Tasks

Run development tasks using `podman compose exec`:

### Running Tests

```bash
podman compose exec php vendor/bin/phpunit
```

### Static Analysis

```bash
podman compose exec php vendor/bin/phpstan analyse
```

### Code Style

Check for violations:
```bash
podman compose exec php vendor/bin/php-cs-fixer fix --dry-run --diff
```

Auto-fix issues:
```bash
podman compose exec php vendor/bin/php-cs-fixer fix
```