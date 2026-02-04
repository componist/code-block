<?php

namespace Componist\CodeBlock;

use Illuminate\Http\Client\RequestException;

class Client
{
    public function __construct(
        protected ApiClient $apiClient,
        protected TemplateWriter $templateWriter,
        protected string $baseUrl,
    ) {
    }

    /**
     * Get all code blocks (optionally filtered by category_id).
     *
     * @return array<int, array<string, mixed>>
     * @throws RequestException
     */
    public function getCodeBlocks(?int $categoryId = null): array
    {
        $response = $this->apiClient->getCodeBlocks($categoryId);
        return $response['data'] ?? [];
    }

    /**
     * Get a single code block by ID.
     *
     * @return array<string, mixed>
     * @throws RequestException
     */
    public function getCodeBlock(int $id): array
    {
        return $this->apiClient->getCodeBlock($id);
    }

    /**
     * Get all code categories.
     *
     * @return array<int, array<string, mixed>>
     * @throws RequestException
     */
    public function getCodeCategories(): array
    {
        $response = $this->apiClient->getCodeCategories();
        return $response['data'] ?? [];
    }

    /**
     * Get a single code category with its code blocks.
     *
     * @return array<string, mixed>
     * @throws RequestException
     */
    public function getCodeCategory(int $id): array
    {
        return $this->apiClient->getCodeCategory($id);
    }

    /**
     * Build full URL for a template/code block preview image.
     * API returns relative paths like "code-blocks/abc.jpg" (under storage).
     *
     * @param  string|null  $previewImagePath  Relative path from API (e.g. "code-blocks/hero.jpg")
     * @return string|null Full URL or null if no path
     */
    public function getPreviewImageUrl(?string $previewImagePath): ?string
    {
        if ($previewImagePath === null || $previewImagePath === '') {
            return null;
        }
        $base = rtrim($this->baseUrl, '/');
        $path = ltrim(str_replace('public/', '', $previewImagePath), '/');
        return $base . '/storage/' . $path;
    }

    /**
     * Add preview_image_url to each block (in place).
     *
     * @param  array<int, array<string, mixed>>  $blocks
     * @return array<int, array<string, mixed>>
     */
    public function withPreviewImageUrls(array $blocks): array
    {
        foreach ($blocks as $i => $block) {
            $path = $block['preview_image'] ?? null;
            $blocks[$i]['preview_image_url'] = $this->getPreviewImageUrl($path);
        }
        return $blocks;
    }

    /**
     * Create a Blade template from one or more code blocks and save to resources/views/pages.
     *
     * @param  array<int, array{html?: string|null, css?: string|null, js?: string|null}>  $blocks
     * @param  string  $filename  Filename without extension (e.g. "hero-page")
     * @return string Full path to the created file
     */
    public function createTemplateFromBlocks(array $blocks, string $filename): string
    {
        return $this->templateWriter->createFromBlocks($blocks, $filename);
    }

    /**
     * Fetch a code block from the API and create a Blade template in resources/views/pages.
     *
     * @param  int  $codeBlockId  Code block ID from Template Archive API
     * @param  string  $filename  Filename without extension (e.g. "hero-page")
     * @return string Full path to the created file
     * @throws RequestException
     */
    public function createTemplateFromBlockId(int $codeBlockId, string $filename): string
    {
        $block = $this->apiClient->getCodeBlock($codeBlockId);
        return $this->templateWriter->createFromBlock([
            'html' => $block['html'] ?? null,
            'css' => $block['css'] ?? null,
            'js' => $block['js'] ?? null,
        ], $filename);
    }

    /**
     * Get the TemplateWriter instance (e.g. to configure views path).
     */
    public function getTemplateWriter(): TemplateWriter
    {
        return $this->templateWriter;
    }

    /**
     * Get the ApiClient instance.
     */
    public function getApiClient(): ApiClient
    {
        return $this->apiClient;
    }
}
