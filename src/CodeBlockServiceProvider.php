<?php

namespace Componist\CodeBlock;

use Componist\CodeBlock\Console\ListBlocksCommand;
use Componist\CodeBlock\Console\ListCategoriesCommand;
use Componist\CodeBlock\Console\PullTemplateCommand;
use Illuminate\Support\ServiceProvider;

class CodeBlockServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/code-block.php',
            'code-block'
        );
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'code-block');
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        if ($this->app->runningInConsole()) {
            $this->commands([
                PullTemplateCommand::class,
                ListBlocksCommand::class,
                ListCategoriesCommand::class,
            ]);

            $this->publishes([
                __DIR__ . '/../config/code-block.php' => $this->app->configPath('code-block.php'),
            ], 'componist-code-block-config');
        }

        $config = config('code-block');
        $baseUrl = rtrim($config['base_url'] ?? config('app.url'), '/');
        $apiKey = $config['api_key'] ?? '';

        $this->app->singleton(ApiClient::class, function () use ($baseUrl, $apiKey) {
            return new ApiClient($baseUrl, $apiKey);
        });

        $viewsBasePath = $this->app->resourcePath('views');
        $viewsSubPath = $config['views_path'] ?? 'pages';

        $this->app->singleton(TemplateWriter::class, function () use ($viewsBasePath, $viewsSubPath) {
            return new TemplateWriter($viewsBasePath, $viewsSubPath);
        });

        $this->app->singleton(Client::class, function ($app) use ($baseUrl) {
            return new Client(
                $app->make(ApiClient::class),
                $app->make(TemplateWriter::class),
                rtrim($baseUrl, '/')
            );
        });
    }
}
