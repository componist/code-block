<?php

namespace Componist\CodeBlock\Console;

use Componist\CodeBlock\Client;
use Illuminate\Console\Command;

class ListBlocksCommand extends Command
{
    protected $signature = 'code-block:blocks
                            {--category= : Filter by category ID}
                            {--with-urls : Add preview_image_url to each block}';

    protected $description = 'List code blocks from Template Archive API (optionally with preview image URLs)';

    public function handle(Client $client): int
    {
        $categoryId = $this->option('category') !== null
            ? (int) $this->option('category')
            : null;
        $withUrls = (bool) $this->option('with-urls');

        try {
            $blocks = $client->getCodeBlocks($categoryId);
            if ($withUrls) {
                $blocks = $client->withPreviewImageUrls($blocks);
            }
        } catch (\Illuminate\Http\Client\RequestException $e) {
            $this->error('API request failed: '.$e->getMessage());

            return self::FAILURE;
        }

        if (empty($blocks)) {
            $this->info('No code blocks found.');

            return self::SUCCESS;
        }

        $rows = [];
        foreach ($blocks as $block) {
            $rows[] = [
                $block['id'] ?? '',
                $block['title'] ?? '',
                $block['code_categorie']['name'] ?? '',
                $withUrls ? ($block['preview_image_url'] ?? '') : ($block['preview_image'] ?? ''),
            ];
        }

        $this->table(
            ['ID', 'Title', 'Category', $withUrls ? 'Preview URL' : 'Preview path'],
            $rows
        );

        return self::SUCCESS;
    }
}
