<?php

declare(strict_types=1);

namespace NetCode\Kit\Tests\Validation;

use NetCode\Kit\Validation\CountryCode;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CountryCodeTest extends TestCase
{
    #[Test]
    public function it_accepts_a_valid_iso_alpha_2_code_case_insensitively(): void
    {
        $this->assertNull($this->failureFor('PL'));
        $this->assertNull($this->failureFor('de'));
    }

    #[Test]
    public function it_rejects_unknown_or_non_string_values(): void
    {
        $this->assertNotNull($this->failureFor('ZZ'));
        $this->assertNotNull($this->failureFor('Poland'));
        $this->assertNotNull($this->failureFor(42));
    }

    private function failureFor(mixed $value): string|null
    {
        $message = null;

        new CountryCode()->validate('country', $value, function (string $error) use (&$message): void {
            $message = $error;
        });

        return $message;
    }
}
