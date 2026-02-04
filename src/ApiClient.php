<?php

namespace Componist\CodeBlock;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ApiClient
{
    protected const CACHE_KEY_PREFIX = 'componist.code_block.api';

    public function __construct(
        protected string $baseUrl,
        protected string $apiKey,
    ) {
    }

    protected function cacheEnabled(): bool
    {
        return (bool) config('code-block.api_cache_enabled', true);
    }

    protected function cacheTtlMinutes(): int
    {
        return (int) (config('code-block.api_cache_ttl_minutes', 0) ?: 0);
    }

    /**
     * Get from cache or execute callback and store. Logs cache hit when app.debug is true.
     *
     * @param  callable(): array  $callback
     * @return array
     */
    protected function getCached(string $key, int $ttlMinutes, callable $callback): array
    {
        $ttlSeconds = $ttlMinutes * 60;

        if (config('app.debug') && $ttlMinutes > 0 && Cache::has($key)) {
            Log::debug('Code Block API cache hit', ['key' => $key]);
        }

        return Cache::remember($key, $ttlSeconds, $callback);
    }

    protected function client(): PendingRequest
    {
        $url = rtrim($this->baseUrl, '/');

        return Http::baseUrl($url)
            ->withHeaders([
                'X-API-Key' => $this->apiKey,
                'Accept' => 'application/json',
            ])
            ->timeout(30);
    }

    /**
     * Get all code blocks (optionally filtered by category_id).
     *
     * @return array{data: array<int, array<string, mixed>>}
     * @throws RequestException
     */
    public function getCodeBlocks(?int $categoryId = null): array
    {
        $key = self::CACHE_KEY_PREFIX . '.blocks.' . ($categoryId ?? 'all');
        $ttl = $this->cacheTtlMinutes();

        if ($this->cacheEnabled() && $ttl > 0) {
            return $this->getCached($key, $ttl, function () use ($categoryId) {
                $query = $categoryId !== null ? ['category_id' => $categoryId] : [];
                $response = $this->client()->get('/api/code-blocks', $query)->throw();
                return $response->json();
            });
        }

        $query = $categoryId !== null ? ['category_id' => $categoryId] : [];
        $response = $this->client()->get('/api/code-blocks', $query)->throw();
        return $response->json();
    }

    /**
     * Get a single code block by ID.
     *
     * @return array<string, mixed>
     * @throws RequestException
     */
    public function getCodeBlock(int $id): array
    {
        $key = self::CACHE_KEY_PREFIX . '.block.' . $id;
        $ttl = $this->cacheTtlMinutes();

        if ($this->cacheEnabled() && $ttl > 0) {
            return $this->getCached($key, $ttl, function () use ($id) {
                $response = $this->client()->get("/api/code-blocks/{$id}")->throw();
                return $response->json();
            });
        }

        $response = $this->client()->get("/api/code-blocks/{$id}")->throw();
        return $response->json();
    }

    /**
     * Get all code categories.
     *
     * @return array{data: array<int, array<string, mixed>>}
     * @throws RequestException
     */
    public function getCodeCategories(): array
    {
        $key = self::CACHE_KEY_PREFIX . '.categories';
        $ttl = $this->cacheTtlMinutes();

        if ($this->cacheEnabled() && $ttl > 0) {
            return $this->getCached($key, $ttl, function () {
                $response = $this->client()->get('/api/code-categories')->throw();
                return $response->json();
            });
        }

        $response = $this->client()->get('/api/code-categories')->throw();
        return $response->json();
    }

    /**
     * Get a single code category with its code blocks.
     *
     * @return array<string, mixed>
     * @throws RequestException
     */
    public function getCodeCategory(int $id): array
    {
        $key = self::CACHE_KEY_PREFIX . '.category.' . $id;
        $ttl = $this->cacheTtlMinutes();

        if ($this->cacheEnabled() && $ttl > 0) {
            return $this->getCached($key, $ttl, function () use ($id) {
                $response = $this->client()->get("/api/code-categories/{$id}")->throw();
                return $response->json();
            });
        }

        $response = $this->client()->get("/api/code-categories/{$id}")->throw();
        return $response->json();
    }
}
