<?php

namespace Componist\CodeBlock\Console;

use Illuminate\Console\Command;
use Componist\CodeBlock\Client;

class PullTemplateCommand extends Command
{
    protected $signature = 'code-block:pull
                            {block_id : Code block ID from Template Archive API}
                            {filename : Blade filename without extension (e.g. hero-page)}';

    protected $description = 'Pull a code block from Template Archive API and create a Blade template in resources/views/pages';

    public function handle(Client $client): int
    {
        $blockId = (int) $this->argument('block_id');
        $filename = (string) $this->argument('filename');

        if ($blockId < 1) {
            $this->error('block_id must be a positive integer.');
            return self::FAILURE;
        }

        $this->info("Pulling code block {$blockId} from Template Archive...");

        try {
            $path = $client->createTemplateFromBlockId($blockId, $filename);
            $this->info("Template created: {$path}");
            return self::SUCCESS;
        } catch (\Illuminate\Http\Client\RequestException $e) {
            $this->error('API request failed: ' . $e->getMessage());
            if ($e->response) {
                $this->line($e->response->body());
            }
            return self::FAILURE;
        }
    }
}
