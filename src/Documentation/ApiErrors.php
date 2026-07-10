<?php

declare(strict_types=1);

namespace NetCode\Kit\Documentation;

use Attribute;

/**
 * Declares the domain error HTTP statuses an endpoint may return (e.g. 404, 409),
 * so the OpenAPI generator can document them — these come from domain exceptions
 * mapped centrally in bootstrap/app.php, which static analysis cannot trace.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class ApiErrors
{
    /** @var list<int> */
    public array $statuses;

    public function __construct(
        int ...$statuses,
    ) {
        $this->statuses = array_values($statuses);
    }
}
