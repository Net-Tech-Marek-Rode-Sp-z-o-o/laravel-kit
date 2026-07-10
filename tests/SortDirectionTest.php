<?php

declare(strict_types=1);

namespace NetCode\Kit\Tests;

use NetCode\Kit\SortDirection;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class SortDirectionTest extends TestCase
{
    #[Test]
    public function it_maps_to_the_expected_string_values(): void
    {
        $this->assertSame('asc', SortDirection::Asc->value);
        $this->assertSame('desc', SortDirection::Desc->value);
        $this->assertSame(SortDirection::Desc, SortDirection::from('desc'));
        $this->assertNull(SortDirection::tryFrom('sideways'));
    }
}
