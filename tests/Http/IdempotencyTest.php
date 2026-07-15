<?php

declare(strict_types=1);

namespace NetCode\Kit\Tests\Http;

use Closure;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use NetCode\Kit\Http\Idempotency;
use NetCode\Kit\SystemClock;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class IdempotencyTest extends TestCase
{
    private function middleware(): Idempotency
    {
        return new Idempotency(new Repository(new ArrayStore), new SystemClock);
    }

    private function post(string|null $key = null): Request
    {
        $server = $key === null ? [] : ['HTTP_IDEMPOTENCY_KEY' => $key];

        return Request::create('/files', 'POST', [], [], [], $server, '{}');
    }

    /** @param Closure(Request): JsonResponse $handler */
    private function handle(Idempotency $mw, Request $request, Closure $handler): mixed
    {
        return $mw->handle($request, $handler);
    }

    #[Test]
    public function it_replays_the_first_response_for_a_repeated_key(): void
    {
        $mw = $this->middleware();

        $this->handle($mw, $this->post('k1'), fn (): JsonResponse => new JsonResponse(['file_id' => 'abc'], 201));
        $second = $this->handle($mw, $this->post('k1'), fn (): JsonResponse => new JsonResponse(['file_id' => 'DIFFERENT'], 201));

        $this->assertSame(201, $second->getStatusCode());
        $this->assertStringContainsString('abc', (string) $second->getContent());
        $this->assertStringNotContainsString('DIFFERENT', (string) $second->getContent());
        $this->assertSame('true', $second->headers->get('Idempotent-Replayed'));
    }

    #[Test]
    public function without_a_key_each_call_runs_the_handler(): void
    {
        $mw = $this->middleware();
        $calls = 0;
        $handler = function () use (&$calls): JsonResponse {
            $calls++;

            return new JsonResponse([], 201);
        };

        $this->handle($mw, $this->post(), $handler);
        $this->handle($mw, $this->post(), $handler);

        $this->assertSame(2, $calls);
    }

    #[Test]
    public function it_does_not_replay_a_failed_response(): void
    {
        $mw = $this->middleware();

        $this->handle($mw, $this->post('k2'), fn (): JsonResponse => new JsonResponse(['error' => true], 422));
        $second = $this->handle($mw, $this->post('k2'), fn (): JsonResponse => new JsonResponse(['file_id' => 'ok'], 201));

        $this->assertSame(201, $second->getStatusCode());
        $this->assertNull($second->headers->get('Idempotent-Replayed'));
    }
}
