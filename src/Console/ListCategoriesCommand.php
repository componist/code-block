<?php

namespace Componist\CodeBlock\Console;

use Componist\CodeBlock\Client;
use Illuminate\Console\Command;

class ListCategoriesCommand extends Command
{
    protected $signature = 'code-block:categories';

    protected $description = 'List code categories from Template Archive API';

    public function handle(Client $client): int
    {
        try {
            $categories = $client->getCodeCategories();
        } catch (\Illuminate\Http\Client\RequestException $e) {
            $this->error('API request failed: '.$e->getMessage());

            return self::FAILURE;
        }

        if (empty($categories)) {
            $this->info('No categories found.');

            return self::SUCCESS;
        }

        $rows = array_map(fn ($c) => [
            $c['id'] ?? '',
            $c['name'] ?? '',
            $c['description'] ?? '',
        ], $categories);

        $this->table(['ID', 'Name', 'Description'], $rows);

        return self::SUCCESS;
    }
}
