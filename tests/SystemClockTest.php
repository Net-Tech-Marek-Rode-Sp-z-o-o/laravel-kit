<?php

declare(strict_types=1);

namespace NetCode\Kit\Tests;

use DateTimeImmutable;
use NetCode\Kit\Clock;
use NetCode\Kit\SystemClock;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class SystemClockTest extends TestCase
{
    #[Test]
    public function it_is_a_clock_that_returns_the_current_moment(): void
    {
        $clock = new SystemClock;

        $this->assertInstanceOf(Clock::class, $clock);
        $this->assertInstanceOf(DateTimeImmutable::class, $clock->now());
    }
}
