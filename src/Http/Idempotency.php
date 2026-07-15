<?php

declare(strict_types=1);

namespace NetCode\Kit\Http;

use Closure;
use DateInterval;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Cache\Lock;
use Illuminate\Contracts\Cache\LockProvider;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use NetCode\Kit\Clock;
use Symfony\Component\HttpFoundation\Response;

/**
 * Replays the first successful response for a repeated `Idempotency-Key` on POST requests, and
 * locks concurrent duplicates. Framework-agnostic: the cache Repository is host-provided, so any
 * store works (a store without locks simply skips the concurrency guard).
 */
final readonly class Idempotency
{
    private const string HEADER = 'Idempotency-Key';

    private const int TTL_HOURS = 24;

    private const int LOCK_SECONDS = 10;

    public function __construct(
        private Cache $cache,
        private Clock $clock,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->header(self::HEADER);

        if (! is_string($key) || ! $request->isMethod('POST')) {
            return $next($request);
        }

        $cacheKey = 'idempotency:'.sha1(implode('|', [$key, $request->path(), $this->subject($request)]));

        $stored = $this->cache->get($cacheKey);

        if (is_array($stored)) {
            return new HttpResponse((string) $stored['body'], (int) $stored['status'], [
                'Content-Type' => (string) $stored['content_type'],
                'Idempotent-Replayed' => 'true',
            ]);
        }

        $lock = $this->lock($cacheKey);

        if ($lock !== null && ! $lock->get()) {
            return $this->inProgress();
        }

        try {
            $response = $next($request);

            if ($response->isSuccessful()) {
                $this->cache->put($cacheKey, [
                    'status' => $response->getStatusCode(),
                    'body' => $response->getContent(),
                    'content_type' => $response->headers->get('Content-Type'),
                ], $this->clock->now()->add(new DateInterval('PT'.self::TTL_HOURS.'H')));
            }

            return $response;
        } finally {
            $lock?->release();
        }
    }

    private function subject(Request $request): string
    {
        $user = $request->user();

        return $user instanceof Authenticatable ? (string) $user->getAuthIdentifier() : 'guest';
    }

    private function lock(string $cacheKey): Lock|null
    {
        $store = $this->cache->getStore();

        return $store instanceof LockProvider ? $store->lock($cacheKey.':lock', self::LOCK_SECONDS) : null;
    }

    private function inProgress(): JsonResponse
    {
        return new JsonResponse([
            'type' => 'about:blank',
            'title' => 'Request in progress',
            'status' => Response::HTTP_CONFLICT,
            'detail' => 'A request with this Idempotency-Key is already being processed.',
        ], Response::HTTP_CONFLICT, ['Content-Type' => 'application/problem+json']);
    }
}
