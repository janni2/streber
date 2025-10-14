# Modern PHP Architecture (src/)

This directory contains the modernized PHP code using namespaces, type hints, and modern PHP patterns.

## Structure

```
src/
├── Config.php             - Configuration service wrapper
├── Container.php          - Dependency injection container
├── helpers.php            - Global helper functions
└── README.md             - This file
```

## Container System

The `Container` class provides a simple dependency injection container that supports:

1. **Service Registration**: Register services directly or via factories
2. **Lazy Loading**: Services are only instantiated when needed
3. **Singleton Pattern**: Services can be configured as singletons
4. **Backward Compatibility**: Falls back to global variables for legacy code

### Usage

```php
// Get container instance
$container = Container::getInstance();

// Or use the helper function
$container = container();

// Register a service
$container->set('my_service', new MyService());

// Register a factory (lazy loading)
$container->setFactory('database', function($container) {
    return new Database($container->get('config'));
}, true); // true = singleton

// Get a service
$service = $container->get('my_service');

// Or use the helper function
$service = service('my_service');
```

### Backward Compatibility

The container automatically falls back to global variables for legacy code:

- `service('auth')` → `$GLOBALS['auth']`
- `service('page_handler')` → `$GLOBALS['PH']`
- `service('config')` → `$GLOBALS['g_config']`

This allows gradual migration from global variables to dependency injection.

## Migration Strategy

1. **New code**: Use the container for all dependencies
2. **Existing code**: Can continue using globals while we migrate
3. **Gradual migration**: Replace globals one service at a time

### Example Migration

**Before (using globals):**
```php
function myFunction() {
    global $auth;
    if ($auth->cur_user->id) {
        // ...
    }
}
```

**After (using container):**
```php
function myFunction() {
    $auth = service('auth');
    if ($auth->cur_user->id) {
        // ...
    }
}
```

## PHP 8+ Features Used

- `declare(strict_types=1)` - Strict type checking
- Type hints for parameters and return values
- Nullable types (`?Container`)
- Property type declarations

## Config Service

The `Config` class provides object-oriented access to configuration:

```php
// Get config instance
$config = config();

// Get specific value
$appName = config('APP_NAME');
$appName = config('APP_NAME', 'Default Name'); // with default

// Set value
config()->set('MY_KEY', 'my_value');

// Check if key exists
if (config()->has('SOME_KEY')) {
    // ...
}

// Magic property access
$appName = config()->APP_NAME;
config()->APP_NAME = 'New Name';
```

**Backward Compatibility:** The Config service automatically syncs with the global `$g_config` array, so both old and new code continue to work.

**Migration Example:**
```php
// Before:
global $g_config;
$name = $g_config['APP_NAME'];

// After:
$name = config('APP_NAME');
```

## Future Additions

- Database connection wrapper
- Logger service
- Event dispatcher
- Router (modern alternative to PageHandler)
