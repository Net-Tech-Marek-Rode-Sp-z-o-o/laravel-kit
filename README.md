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
| `NetCode\Kit\Documentation\ApiTag` | Class attribute overriding an endpoint's OpenAPI tag (group), e.g. `#[ApiTag('Admin', 'Billing')]`. |
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

## Scramble (optional)

If you use [Scramble](https://scramble.dedoc.co) for OpenAPI generation, the
`NetCode\Kit\Scramble\*` operation transformers are available (install
`dedoc/scramble`, and `spatie/laravel-data` for the query-parameter bridge):

| Transformer | Purpose |
|---|---|
| `TagByNamespace` | Tags each operation by its controller: an explicit `#[ApiTag]` wins, otherwise a resolver you supply computes the tag from the controller's namespace segments. |
| `DocumentErrorResponses` | Documents RFC 9457 problem+json errors from middleware, write methods, and `#[ApiErrors(...)]`. Scope with a namespace prefix. |
| `DocumentDataQueryParameters` | Documents GET query params for spatie `Data` request objects, which Scramble does not see natively. |

`TagByNamespace` is convention-first (no annotation needed), with `#[ApiTag]` as a
per-controller override. The resolver receives the split namespace and returns the tag —
here, audience-first (`Admin / Billing`, `Client / Projects`), falling back to the context
name when a controller has no audience subfolder:

```php
use Dedoc\Scramble\Scramble;
use NetCode\Kit\Scramble\DocumentDataQueryParameters;
use NetCode\Kit\Scramble\DocumentErrorResponses;
use NetCode\Kit\Scramble\TagByNamespace;

Scramble::configure()->withOperationTransformers([
    new TagByNamespace('Contexts\\', static function (array $segments): ?string {
        $context = $segments[1] ?? null;

        if ($context === null) {
            return null;
        }

        $at = array_search('Controllers', $segments, true);
        $audience = is_int($at) && isset($segments[$at + 2]) ? $segments[$at + 1] : null;

        return $audience === null ? $context : $audience.' / '.$context;
    }),
    new DocumentErrorResponses(namespacePrefix: 'Contexts\\'),
    $this->app->make(DocumentDataQueryParameters::class),
]);
```

Any controller can opt out of the convention: `#[ApiTag('Reports')]` forces its group.

## License

MIT
