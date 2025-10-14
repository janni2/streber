# PHP Modernization Plan for Streber

## Progress Summary

**Status:** In Progress ✅
**Last Updated:** 2025-10-14
**Commits:** 5 modernization commits on master branch

### ✅ Completed

#### Phase 1: Foundation & Infrastructure
- ✅ **1.1 Composer & Autoloading** - composer.json with PSR-4, `Streber\` namespace
- ✅ **1.2 PHP Version** - Updated to PHP 8.3+ (even better than planned 7.4!)
- ✅ **1.3 Code Quality Tools** - PHPStan, PHP-CS-Fixer, PHPUnit configured
- ✅ **Quick Win** - Converted 184 files from `array()` to `[]` syntax

#### Phase 2: Dependency Injection (Partial)
- ✅ **2.1 Service Container** - `src/Container.php` with backward compatibility
- ✅ **2.2 Config Service** - `src/Config.php` wrapper for `$g_config`
- ✅ **Helper Functions** - `container()`, `service()`, `config()` helpers

### 🚧 In Progress / Next Steps

- ⏳ **Phase 2.3** - Wrap Auth class in container
- ⏳ **Phase 2.4** - Wrap PageHandler in container
- ⏳ **Phase 3** - Database layer modernization
- ⏳ **Phase 4** - Add type hints and return types

### 📊 Impact

- **Files Changed:** 184 files modernized with array syntax
- **New Architecture:** Modern DI container with 3 new classes in `src/`
- **Backward Compatible:** 100% - All changes maintain BC with existing code
- **Breaking Changes:** 0

---

## Current State Analysis

**Current PHP Version Required:** 5.0.0 (from 2004!)
**Key Issues Identified:**
- 903 uses of `global` keyword across 134 files
- Heavy reliance on global state (`$auth`, `$PH`, `$g_config`, etc.)
- No namespaces
- No type hints
- Manual SQL query building (some prepared statements exist)
- No dependency injection
- Inconsistent error handling (mix of `trigger_error` and exceptions)
- No autoloading (uses `require_once` everywhere)

## Modernization Strategy: Iterative, Non-Breaking Approach

### Phase 1: Foundation & Infrastructure (Low Risk) ✅ COMPLETED
**Goal:** Set up modern tooling without changing application behavior

#### 1.1 Composer & Autoloading ✅
- [x] Add `composer.json` with PSR-4 autoloading
- [x] Create namespace structure: `Streber\`
- [x] Add development dependencies (PHPStan, PHP-CS-Fixer, PHPUnit)
- [x] Keep existing `require_once` working alongside autoloader

#### 1.2 PHP Version Requirements ✅
- [x] Update minimum PHP version to 7.4 (or 8.0+ if feasible) - **PHP 8.3!**
- [x] Document PHP 7.4+ features we can now use
- [x] Add `.phpversion` or update documentation

#### 1.3 Code Quality Tools ✅
- [x] Add PHPStan for static analysis (start at level 0)
- [x] Add PHP-CS-Fixer for code style
- [ ] Add pre-commit hooks (optional)
- [x] Generate baseline for existing code

**Files to create:**
```
composer.json
phpstan.neon
.php-cs-fixer.php
```

### Phase 2: Dependency Injection Container (Medium Risk) 🚧 IN PROGRESS
**Goal:** Reduce global state gradually

#### 2.1 Create Service Container ✅
```php
// New file: src/Container.php
namespace Streber;

class Container {
    private static $instance;
    private $services = [];

    // Backward compatible: still allow globals
    public function get(string $id) {
        if (isset($this->services[$id])) {
            return $this->services[$id];
        }

        // Fallback to globals for BC
        if ($id === 'auth' && isset($GLOBALS['auth'])) {
            return $GLOBALS['auth'];
        }
        // ... more fallbacks
    }
}
```

#### 2.2 Wrap Global Objects 🚧
- [ ] `Auth` class → keep global `$auth` but also register in container
- [ ] `PageHandler` class → keep global `$PH` but also register in container
- [x] `Config` class → replace global `$g_config` array ✅

**Migration approach:** Dual mode - support both global and container access

### Phase 3: Database Layer Modernization (Medium-High Risk)
**Goal:** Add modern database abstraction without breaking existing code

#### 3.1 Add PDO Wrapper
- [ ] Create `Streber\Database\Connection` class wrapping PDO
- [ ] Make existing `DB_Mysql` class use new wrapper internally
- [ ] **Keep existing class interfaces unchanged**

#### 3.2 Query Builder (Optional)
- [ ] Add lightweight query builder for new code
- [ ] Old code continues using string queries
- [ ] Gradual migration file-by-file

### Phase 4: Type Hints & Return Types (Low-Medium Risk)
**Goal:** Add modern PHP type safety

#### 4.1 Add Type Hints Gradually
Start with new methods, then add to existing:

```php
// Before:
function getById($id) { ... }

// After (non-breaking):
function getById(int $id): ?self { ... }
```

#### 4.2 Use Tools
- [ ] Use PHPStan/Psalm to infer types
- [ ] Use IDE refactoring tools
- [ ] Add `declare(strict_types=1)` to new files only

**Priority order:**
1. Return types (safer)
2. Parameter types  (can break if incorrect)
3. Property types (PHP 7.4+)

### Phase 5: Namespace Migration (Medium Risk)
**Goal:** Organize code with namespaces

#### 5.1 Namespace Strategy
```
Streber\
├── Database\
│   ├── Entity\      (old: db/class_*.php)
│   ├── Repository\
│   └── Query\
├── Page\            (old: pages/*.php)
├── Render\          (old: render/*.php)
├── Auth\            (old: std/class_auth.php)
└── Config\
```

#### 5.2 Migration Approach
- [ ] Create namespaced classes alongside old ones
- [ ] Use `class_alias()` for backward compatibility
```php
namespace Streber\Database\Entity;
class Task extends DbProjectItem { ... }

// BC: old code can still use Task
class_alias('Streber\Database\Entity\Task', 'Task');
```

### Phase 6: Replace Global Functions (Low Risk)
**Goal:** Move to static methods or services

#### 6.1 Common Functions
```php
// Old: std/common.inc.php
function get($key) { ... }

// New: src/Helpers/Request.php
namespace Streber\Helpers;
class Request {
    public static function get(string $key): ?string { ... }
}

// BC wrapper:
function get($key) {
    return \Streber\Helpers\Request::get($key);
}
```

### Phase 7: Modern Routing (High Risk - Optional)
**Goal:** Replace PageHandler with modern router

#### 7.1 Keep PageHandler, Add Alternative
- [ ] Add Symfony/FastRoute as optional router
- [ ] New pages can use new router
- [ ] Old pages still work with PageHandler
- [ ] Gradual migration

### Phase 8: Testing Infrastructure
**Goal:** Add tests without changing existing code

#### 8.1 PHPUnit Setup
- [ ] Replace SimpleTest with PHPUnit
- [ ] Create test helpers/fixtures
- [ ] Add integration tests for critical flows

#### 8.2 Test Coverage
- [ ] Start with high-value classes (Auth, Task, Project)
- [ ] Add tests before refactoring

## Priority: Quick Wins

### Immediate Actions (< 1 day each)
1. ✅ **Add composer.json** - No code changes needed
2. ✅ **Add PHPStan level 0** - Just analysis, no fixes required
3. ⏳ **Add return type `:void`** - Safe, non-breaking
4. ✅ **Replace `array()` with `[]`** - Modern syntax, same behavior
5. ✅ **Add `declare(strict_types=1)` to new files** - Only affects new code (done in src/)

### High-Value, Low-Risk Changes
```php
// 1. Replace string concatenation in SQL (already partially done)
// Old:
$query = "SELECT * FROM {$prefix}task WHERE id = $id";

// New:
$query = "SELECT * FROM {$prefix}task WHERE id = ?";
$stmt->execute([$id]);

// 2. Add type hints to getById methods (return types first!)
public static function getById(int $id): ?self

// 3. Use ?? instead of isset() + ternary
// Old:
$value = isset($arr['key']) ? $arr['key'] : 'default';

// New:
$value = $arr['key'] ?? 'default';

// 4. Use foreach instead of while + fetch
// Already done in most places, standardize remainder
```

## File-by-File Migration Order

### Phase 1 Files (Highest Priority)
1. `db/db_item.inc.php` - Base classes used everywhere
2. `std/class_auth.inc.php` - Authentication used on every request
3. `std/class_pagehandler.inc.php` - Request routing
4. `conf/conf.inc.php` - Configuration

### Phase 2 Files
5. `db/class_task.inc.php` - Most complex entity
6. `db/class_project.inc.php`
7. `db/class_person.inc.php`

### Phase 3 Files (Lower Priority)
8. Page files (pages/*.php)
9. Render files (render/*.php)
10. List files (lists/*.php)

## Compatibility Guarantees

### What Won't Break
- ✅ Existing URLs and routing
- ✅ Database schema
- ✅ Template/theme system
- ✅ Language files
- ✅ File uploads and storage
- ✅ User sessions and cookies
- ✅ customize.inc.php configuration

### What Might Need Updates
- ⚠️ Custom code in customize.inc.php (if it extends classes)
- ⚠️ Custom page handlers
- ⚠️ PHP extensions (might need updates for PHP 7.4+)

## Success Metrics

### Code Quality
- [ ] PHPStan level 0 → 5 (eventually 8)
- [ ] 0% global variables → < 10% → 0%
- [ ] 0% type hints → 50% → 90%
- [ ] PSR-12 code style compliance

### Performance
- [ ] Autoloading reduces initial require_once overhead
- [ ] Prepared statements prevent SQL injection
- [ ] Opcode cache works better with modern PHP

### Maintainability
- [ ] New developers can understand code structure
- [ ] IDE autocomplete works (thanks to type hints)
- [ ] Static analysis catches bugs before runtime
- [ ] Tests provide safety net for refactoring

## Tools & Commands

### Setup
```bash
# Install Composer
curl -sS https://getcomposer.org/installer | php

# Install dependencies
php composer.phar install

# Run static analysis
vendor/bin/phpstan analyze

# Run code style fixer
vendor/bin/php-cs-fixer fix --dry-run

# Run tests (once migrated from SimpleTest)
vendor/bin/phpunit
```

### Automated Refactoring
```bash
# Replace array() with []
vendor/bin/php-cs-fixer fix --rules=array_syntax

# Add declare(strict_types=1)
vendor/bin/php-cs-fixer fix --rules=declare_strict_types

# Fix code style
vendor/bin/php-cs-fixer fix
```

## Risk Mitigation

### Each Change Should Have:
1. **Backward compatibility layer** (class_alias, function wrappers)
2. **Tests** (add before refactoring if possible)
3. **Rollback plan** (git tag before changes)
4. **Documentation** (update CLAUDE.md with new patterns)

### Testing Strategy
1. Unit tests for new code
2. Integration tests for critical flows
3. Manual testing of key features after each phase
4. Keep old SimpleTest suite running during migration

## Timeline Estimate

- **Phase 1:** 1-2 days
- **Phase 2:** 3-5 days
- **Phase 3:** 5-7 days
- **Phase 4:** 2-3 weeks (gradual, file by file)
- **Phase 5:** 2-3 weeks (gradual, with BC layer)
- **Phase 6:** 1-2 weeks
- **Phase 7:** 2-4 weeks (optional)
- **Phase 8:** Ongoing

**Total:** 2-3 months for full modernization (can be done incrementally)

## Next Steps

1. Review this plan with stakeholders
2. Set up git branch: `feature/modernization`
3. Start with Phase 1.1 (Composer setup)
4. Create PR with just composer.json + PHPStan
5. Iterate from there

---

**Note:** This plan prioritizes **gradual, non-breaking changes** over a big rewrite. The application should remain functional after each step.
