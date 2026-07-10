<?php

declare(strict_types=1);

namespace NetCode\Kit\Http;

use Illuminate\Http\Request;

final class PaginatedResponse
{
    /** @return array{meta: array<string, int>, links: array<string, string|null>} */
    public static function meta(Request $request, int $page, int $perPage, int $total): array
    {
        $lastPage = (int) max(1, (int) ceil($total / max(1, $perPage)));

        return [
            'meta' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => $lastPage,
            ],
            'links' => [
                'self' => $request->fullUrlWithQuery(['page' => $page]),
                'first' => $request->fullUrlWithQuery(['page' => 1]),
                'last' => $request->fullUrlWithQuery(['page' => $lastPage]),
                'prev' => $page > 1 ? $request->fullUrlWithQuery(['page' => $page - 1]) : null,
                'next' => $page < $lastPage ? $request->fullUrlWithQuery(['page' => $page + 1]) : null,
            ],
        ];
    }
}
