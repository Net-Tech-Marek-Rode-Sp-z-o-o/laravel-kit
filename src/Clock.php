<?php

declare(strict_types=1);

namespace NetCode\Kit;

use DateTimeImmutable;

interface Clock
{
    public function now(): DateTimeImmutable;
}
