<?php

use Componist\CodeBlock\Http\Controllers\ShowTemplateBuilderPageController;
use Componist\CodeBlock\Http\Controllers\ShowTemplateController;
use Componist\CodeBlock\Http\Controllers\TemplateBuilderApiController;
use Componist\CodeBlockTracking\Http\Controllers\CodeBlockBuilderScriptController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Template-Route (Anzeige gebauter Templates)
|--------------------------------------------------------------------------
*/
$templateRouteConfig = config('code-block.route', []);
if (! empty($templateRouteConfig['enabled'])) {
    $prefix = $templateRouteConfig['prefix'] ?? config('code-block.views_path', 'pages');
    $routeName = $templateRouteConfig['name'] ?? 'code-block.template.show';
    $middleware = $templateRouteConfig['middleware'] ?? [];

    Route::middleware($middleware)
        ->get($prefix.'/{name}', ShowTemplateController::class)
        ->name($routeName)
        ->where('name', '[a-z0-9_-]+');
}

/*
|--------------------------------------------------------------------------
| Template-Builder API + Builder-Seite
|--------------------------------------------------------------------------
*/
$builderConfig = config('code-block.builder', []);
$apiPrefix = $builderConfig['api_prefix'] ?? 'code-block-builder';
$apiMiddleware = $builderConfig['middleware'] ?? ['web'];

Route::middleware($apiMiddleware)
    ->get('code-block-builder.js', CodeBlockBuilderScriptController::class)
    ->name('code-block.builder.script');

Route::middleware($apiMiddleware)
    ->prefix($apiPrefix)
    ->name('code-block.builder.')
    ->group(function () {
        Route::get('categories', [TemplateBuilderApiController::class, 'categories'])->name('categories');
        Route::get('blocks', [TemplateBuilderApiController::class, 'blocks'])->name('blocks');
        Route::post('templates', [TemplateBuilderApiController::class, 'store'])->name('store');
    });

$builderRouteConfig = $builderConfig['route'] ?? [];
if (! empty($builderRouteConfig['enabled'])) {
    $path = $builderRouteConfig['path'] ?? 'builder';
    $routeName = $builderRouteConfig['name'] ?? 'code-block.builder.page';
    $routeMiddleware = $builderRouteConfig['middleware'] ?? ['web'];

    Route::middleware($routeMiddleware)
        ->get($path, ShowTemplateBuilderPageController::class)
        ->name($routeName);
}
