<?php

declare(strict_types=1);

namespace NetCode\Kit\Scramble;

use Dedoc\Scramble\Support\Generator\Operation;
use Dedoc\Scramble\Support\RouteInfo;

/**
 * Tags each operation from segments of its controller's namespace — e.g. with
 * `segments: [1, 2]` a `Contexts\Billing\Invoices\...` controller becomes
 * "Billing / Invoices". Optionally appends the folder right before the class
 * name (e.g. Admin / Client) unless it is an ignored group such as "Controllers".
 *
 * A Scramble operation transformer; register it via
 * `Scramble::configure()->withOperationTransformers(...)`.
 */
final class TagByNamespaceSegments
{
    /**
     * @param string $prefix only tag controllers under this namespace prefix (empty = all)
     * @param list<int> $segments zero-based namespace segment indices joined into the tag
     * @param list<string> $ignoredGroups trailing groups that are never appended
     */
    public function __construct(
        private readonly string $prefix = '',
        private readonly array $segments = [1, 2],
        private readonly string $separator = ' / ',
        private readonly bool $appendGroup = true,
        private readonly array $ignoredGroups = ['Controllers'],
    ) {}

    public function __invoke(
        Operation $operation,
        RouteInfo $routeInfo,
    ): void {
        $class = $routeInfo->className();

        if ($class === null || ! str_starts_with($class, $this->prefix)) {
            return;
        }

        $segments = explode('\\', $class);
        $parts = [];

        foreach ($this->segments as $index) {
            if (! isset($segments[$index])) {
                return;
            }

            $parts[] = $segments[$index];
        }

        if ($this->appendGroup) {
            $group = $segments[count($segments) - 2] ?? null;

            if ($group !== null && ! in_array($group, $this->ignoredGroups, true)) {
                $parts[] = $group;
            }
        }

        $operation->setTags([implode($this->separator, $parts)]);
    }
}
