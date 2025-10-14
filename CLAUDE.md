# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Streber is a PHP5-based wiki-driven project management tool for freelancers and small teams. It manages projects, tasks, issues, bugs, efforts, and provides user rights management with multi-language support.

## Development Environment

This is a **PHP-based web application** with no build system. Changes to PHP files take effect immediately.

**Key commands:**
- There is NO build system - PHP files are executed directly by the web server
- The application is accessed through `index.php`
- Testing: Unit tests exist in `/tests/` directory using SimpleTest framework
- Installation: Run `/install/install.php` (should be deleted in production)

**Modern development tools (added during modernization):**
- Composer is used via `composer.phar` (local installation)
- Run commands: `php composer.phar <command>`

**Code quality commands:**
- `php composer.phar cs-check` - Check code style compliance (dry-run)
- `php composer.phar cs-fix` - Automatically fix code style issues
- `php composer.phar analyse` - Run PHPStan static analysis
- `php composer.phar test` - Run PHPUnit tests (if configured)

**Important notes about code quality tools:**
- PHP-CS-Fixer configuration: `.php-cs-fixer.php`
  - Uses PSR-12 standard with modern PHP conventions
  - Excludes: `vendor/`, `lib/`, `_tmp/`, `_files/`, etc.
  - Excludes `test_with_parse_error.php` (intentional syntax errors for testing)
  - Uses short array syntax `[]` instead of `array()`
  - Prefers single quotes for strings

- PHPStan configuration: `phpstan.neon`
  - Level 0 analysis (gradually increase as code improves)
  - Uses baseline file `phpstan-baseline.neon` to track existing issues
  - Regenerate baseline: `php composer.phar analyse -- --generate-baseline`
  - Analyzes files in: blocks/, db/, pages/, render/, std/, src/

- The codebase is gradually being modernized while maintaining backward compatibility
- All modernization changes maintain PHP 8.3+ compatibility

## Architecture

### Core Entry Point (index.php)

**All requests** go through `index.php`, which:
1. Initializes profiler and configuration
2. Loads database settings (or redirects to installer)
3. Authenticates users via cookies
4. Sets language/translation
5. Routes to appropriate page handler based on `?go=` parameter

**Important:** There are NO other entry PHP pages except `index.php` and `install/install.php`.

### Page Handler System

The application uses a **PageHandler** pattern where all pages are defined as PageHandle objects in `pages/_handles.inc.php`.

**Page types:**
- `PageHandle` - Normal pages (views, lists)
- `PageHandleForm` - Form pages (editing)
- `PageHandleSubm` - Submit handlers (form processing)
- `PageHandleFunc` - Function handlers (actions like delete, create)

**Page definition example:**
```php
new PageHandle(array(
    'id' => 'taskView',
    'req' => 'pages/task_view.inc.php',
    'title' => __('View Task'),
    'valid_params' => array('tsk' => '\d*'),
    'test' => 'yes',
));
```

The `id` parameter maps to the `?go=` query parameter, and `req` points to the file containing the page function.

### Database Layer (ORM)

**DbItem and DbProjectItem** (in `db/db_item.inc.php`) provide base classes for database objects:
- `DbItem` - Base class for all database items
- `DbProjectItem` - Extended class for project-related items (tasks, projects, etc.)
- All database classes inherit from these and define fields using Field objects
- Lazy loading of data from database
- SQL is constructed dynamically based on field definitions

**Database classes** (in `/db/` directory):
- `class_task.inc.php` - Tasks
- `class_project.inc.php` - Projects
- `class_person.inc.php` - Users/People
- `class_effort.inc.php` - Time tracking efforts
- `class_comment.inc.php` - Comments
- `class_file.inc.php` - File uploads
- `class_company.inc.php` - Companies

**Database handler:**
- `db/db_mysql_class.php` or `db/db_mysqli_class.php` (configurable)
- Tables prefixed by `DB_TABLE_PREFIX` configuration

### Configuration System

**Configuration files:**
- `conf/conf.inc.php` - Main configuration (DO NOT edit directly)
- `customize.inc.php` - User customizations (override settings here)
- `_settings/db_settings.php` - Database credentials (created by installer)
- `_settings/site_settings.php` - Site-specific settings

**Configuration functions:**
- `confGet('KEY')` - Get configuration value
- `confChange('KEY', 'value')` - Change configuration value (use in customize.inc.php)

### Rendering System

**Rendering files** (in `/render/` directory):
- `render_page.inc.php` - Page scaffolding, headers, footers
- `render_form.inc.php` - Form rendering helpers
- `render_list.inc.php` - List/table rendering
- `render_block.inc.php` - Block/widget rendering
- `render_wiki.inc.php` - Wiki syntax parsing
- `render_fields.inc.php` - Field rendering

### Directory Structure

```
/blocks/          - Reusable UI blocks (login, project summary, etc.)
/conf/            - Configuration and defines
/db/              - Database classes and ORM
/install/         - Installation scripts
/js/              - JavaScript files
/lang/            - Translation files (de.inc.php, fr.inc.php, etc.)
/lib/             - Third-party libraries
/lists/           - List rendering definitions
/pages/           - Page implementations (functions)
/render/          - Rendering helper functions
/src/             - Modern PHP classes (PSR-4 autoloaded, added during modernization)
/std/             - Standard utilities, authentication, common functions
/tests/           - Unit tests
/themes/          - CSS themes
/vendor/          - Composer dependencies (git-ignored)
/_files/          - Uploaded files
/_tmp/            - Temporary files
/_settings/       - Generated settings (db credentials)
```

**Modern classes in `/src/` directory:**
- `Container.php` - Dependency injection container for gradual modernization
- `Config.php` - Service wrapper for configuration management
- `helpers.php` - Modern helper functions (container(), config())

**Using modern services:**
```php
// Get the DI container
$container = container();

// Get config service (wraps confGet/confChange)
$config = config();
$value = $config->get('KEY');
$config->set('KEY', 'value');

// Or use helper functions directly
$value = config()->get('KEY');
```

### Authentication & Rights

**Authentication** (`std/class_auth.inc.php`):
- Users authenticated via cookies
- Anonymous user support (configurable)
- HTTP auth for RSS feeds
- User activation via email links (TUID - temporary user ID)

**User rights** (defined in `conf/defines.inc.php`):
- Bitfield-based rights system
- Rights defined as constants (e.g., `RIGHT_PROJECT_EDIT`)
- User profiles (Admin, PM, Developer, Client, etc.)
- Project-specific rights via ProjectPerson relationship

**Pub Levels** (publication levels):
- Control item visibility within projects
- `PUB_LEVEL_PRIVATE`, `PUB_LEVEL_OPEN`, `PUB_LEVEL_CLIENT`, etc.
- Each user has view/edit/create/delete pub levels per project

### Language & Translation

**Translation system:**
- Translation files in `/lang/` directory (de.inc.php, en.inc.php, etc.)
- Use `__('string')` function for translations
- Language set per user or from `DEFAULT_LANGUAGE` config
- Supports: English, German, French, Polish, Spanish, Italian, Swedish, Norwegian, Russian, Chinese, etc.

### Key Patterns & Conventions

**Naming:**
- Database table prefix via `DB_TABLE_PREFIX`
- Functions named after page IDs (e.g., function `taskView()` for page 'taskView')
- Classes use CamelCase (e.g., `DbProjectItem`)
- Member variables use snake_case (e.g., `$this->pub_level`)

**Security:**
- All GET/POST parameters filtered via `filterGlobalArrays()`
- Use `get('param')` function to retrieve parameters safely
- HTML entities escaped via `asHtml()`
- SQL injection prevented via prepared statements
- Cross-site scripting prevention configured via `CLEAN_REFERRED_VARS`

**From-Handle System:**
- Used to track page history and enable "back" navigation
- `$PH->defineFromHandle()` - Define current page as a from-handle
- `$PH->showFromPage()` - Return to previous page
- MD5 hash stored in temp files per user

**Feedback Messages:**
- `new FeedbackMessage($text)` - Info message
- `new FeedbackWarning($text)` - Warning message
- `new FeedbackError($text)` - Error message
- `new FeedbackHint($text)` - Hint message
- Messages rendered in page header

**Item Change Tracking:**
- All item modifications logged to `itemchange` table
- Change highlighting for users (new/updated indicators)
- Item-person relationship tracks viewed state
- Diff rendering for wiki-style text fields

### Testing

**Unit tests** (in `/tests/` directory):
- Uses SimpleTest framework (in `/tests/simpletest/`)
- Test files: `test_*.php`
- Test suites: `testsuite_*.php`
- Run tests via HTTP with User-Agent: `streber_unit_tester`
- Tests use separate database with `test_` prefix

### Important Notes

**Customization:**
- NEVER edit `conf/conf.inc.php` directly
- Use `customize.inc.php` to override settings
- Custom page implementations can be defined via `postInitCustomize()` function
- Example: `$PH->hash['projView']->req = 'pages/custom_projView.inc.php';`

**Database Schema:**
- Item table is central - all project items reference it
- Two-table pattern: `item` table + specific table (e.g., `task`)
- `item.id` = `task.id` (shared primary key)
- Item types defined in `conf/defines.inc.php` (e.g., `ITEM_TASK = 4`)

**Profiling:**
- Enable via `confChange('USE_PROFILER', true)`
- Use `measure_start('id')` and `measure_stop('id')` to track performance
- Results rendered in page footer

**Error Handling:**
- Errors logged to `errors.log` (configurable)
- Display level controlled via `DISPLAY_ERROR_LIST` and `DISPLAY_ERROR_FULL`
- Use FirePHP for development debugging (set `USE_FIREPHP` to true)

**Clean URLs (mod_rewrite):**
- Enable via `confChange('USE_MOD_REWRITE', true)`
- PageHandles define clean URL patterns via `cleanurl` parameter
- Example: `/123` instead of `/index.php?go=taskView&tsk=123`

## Modernization & Code Quality

### Common Issues and Troubleshooting

**PHP-CS-Fixer issues:**
- If cs-check fails with exit code 4, check for files with syntax errors
- Use `->notName('filename.php')` in `.php-cs-fixer.php` to exclude problematic files
- Clear cache if changes aren't reflected: `rm -f .php-cs-fixer.cache`
- The cache file `.php-cs-fixer.cache` should be git-ignored

**PHPStan issues:**
- If baseline errors don't match current code, regenerate: `php composer.phar analyse -- --generate-baseline`
- Common causes: Code style changes (quote style), fixing actual bugs
- Baseline tracks 583 existing issues (as of current modernization)
- When fixing errors, regenerate baseline to keep it in sync

**Array syntax modernization:**
- Old style: `array('key' => 'value')`
- New style: `['key' => 'value']`
- PHP-CS-Fixer automatically converts these
- Watch for duplicate array keys after conversion

**Autoloading:**
- PSR-4 autoloading configured in `composer.json` for `Streber\` namespace
- Maps to `/src/` directory
- Legacy code still uses manual `require_once()` - this is okay during transition
- New code should use namespaced classes

**Backward compatibility:**
- All modernization maintains backward compatibility
- Old `confGet()` / `confChange()` still work alongside new `config()` service
- Gradual migration strategy - no breaking changes
- Functions and classes coexist during transition period
