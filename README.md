# net-code/laravel-kit

Small, reusable conventions for building Laravel REST APIs — the framework-level glue
that would otherwise be copy-pasted between projects. Extracted from the net-tech
portfolio API.

No service provider, no config, no magic: just a handful of classes you reach for
directly.

## Install

```bash
composer require net-code/laravel-kit
```

## What's inside

| Class | Purpose |
|---|---|
| `NetCode\Kit\Clock` / `SystemClock` | Injectable time port + system implementation. Bind `Clock` to `SystemClock` (or a fixed clock in tests). |
| `NetCode\Kit\SortDirection` | `asc` / `desc` string enum for list queries. |
| `NetCode\Kit\Http\PaginatedResponse` | Builds the `{meta, links}` half of a paginated JSON envelope from the request + page numbers. |
| `NetCode\Kit\Validation\CountryCode` | Validation rule accepting any ISO 3166-1 alpha-2 country code. |
| `NetCode\Kit\Documentation\ApiErrors` | Class attribute declaring the domain error HTTP statuses an endpoint may return, for OpenAPI generators (e.g. Scramble). |
| `NetCode\Kit\ContextServiceProvider` | Base service provider with `registerBindings()` / `bootContext()` hooks for modular-monolith bounded contexts. |

## Usage

```php
use NetCode\Kit\Clock;
use NetCode\Kit\SystemClock;

$this->app->bind(Clock::class, SystemClock::class);
```

```php
use NetCode\Kit\Http\PaginatedResponse;

return response()->json([
    'data' => $rows,
    ...PaginatedResponse::meta($request, $page, $perPage, $total),
]);
```

```php
use NetCode\Kit\Validation\CountryCode;

$request->validate(['country' => ['required', new CountryCode]]);
```

## License

MIT
