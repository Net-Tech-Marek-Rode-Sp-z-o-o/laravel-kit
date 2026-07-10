<?php

declare(strict_types=1);

namespace NetCode\Kit\Tests\Http;

use Illuminate\Http\Request;
use NetCode\Kit\Http\PaginatedResponse;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PaginatedResponseTest extends TestCase
{
    #[Test]
    public function it_builds_meta_and_links_for_a_middle_page(): void
    {
        $request = Request::create('http://example.test/users', 'GET', ['page' => 2]);

        $payload = PaginatedResponse::meta($request, page: 2, perPage: 10, total: 45);

        $this->assertSame([
            'page' => 2,
            'per_page' => 10,
            'total' => 45,
            'last_page' => 5,
        ], $payload['meta']);

        $this->assertStringContainsString('page=1', (string) $payload['links']['prev']);
        $this->assertStringContainsString('page=3', (string) $payload['links']['next']);
        $this->assertStringContainsString('page=5', (string) $payload['links']['last']);
    }

    #[Test]
    public function it_has_no_prev_on_the_first_page_and_no_next_on_the_last(): void
    {
        $request = Request::create('http://example.test/users', 'GET');

        $first = PaginatedResponse::meta($request, page: 1, perPage: 10, total: 5);
        $this->assertNull($first['links']['prev']);
        $this->assertNull($first['links']['next']);
        $this->assertSame(1, $first['meta']['last_page']);
    }
}
