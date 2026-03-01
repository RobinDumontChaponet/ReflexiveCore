# ReflexiveCore

$$ {{∀ x ∈ X : x R x}} $$

Shared building blocks for the Reflexive PHP packages.

This package contains:

- `Reflexive\Core\Database`: a lazy `PDO` wrapper that opens the connection on first real use.
- `Reflexive\Core\Condition` and `Reflexive\Core\ConditionGroup`: abstract condition primitives used by higher-level packages.
- `Reflexive\Core\Comparator` and `Reflexive\Core\Operator`: enums for SQL comparisons and boolean chaining.
- `Reflexive\Core\Strings::quote()`: a small helper that backticks SQL identifiers.

## Requirements

- PHP `^8.4`

## Installation

```bash
composer require reflexive/core
```

## Lazy database connections

`Database` extends `PDO`, but it does not call the parent constructor until you actually use the connection.
That means you can pass a configured database object around without opening the socket immediately.

```php
use Reflexive\Core\Database;

$db = new Database(
	'mysql:host=127.0.0.1;dbname=app;charset=utf8mb4',
	'user',
	'secret',
);

// No connection yet.
$stmt = $db->prepare('SELECT 1');
$stmt->execute();
```

The class also exposes `Database::once()` to keep one lazy instance per DSN:

```php
$db = Database::once('sqlite:/tmp/app.sqlite');
```

Default PDO options applied on connect:

- `PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ`
- `PDO::ATTR_EMULATE_PREPARES => false`
- `PDO::ATTR_PERSISTENT => false`
- `PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION`

## Conditions and operators

The abstract condition API is intentionally small and is meant to be subclassed by query layers.

```php
use Reflexive\Core\Comparator;
use Reflexive\Core\Operator;
```

Available comparators:

- `EQUAL`
- `NOTEQUAL`
- `GREATER`
- `LESS`
- `GREATEROREQUAL`
- `LESSOREQUAL`
- `IN`
- `BETWEEN`
- `LIKE`
- `NULL`
- `NOTNULL`

Available boolean operators:

- `AND`
- `OR`

`ConditionGroup` enforces explicit chaining: once a condition has been added, the next one must be preceded by `and()` or `or()`.

## Identifier quoting

`Strings::quote()` wraps bare identifiers in backticks while leaving SQL functions and already-quoted names alone.

```php
use Reflexive\Core\Strings;

Strings::quote('users.email'); // `users`.`email`
Strings::quote('COUNT(*)');    // COUNT(*)
```

## Scope

This package is mostly infrastructure. On its own it does not build SQL or hydrate models; those features live in:

- `reflexive/query`
- `reflexive/model`
