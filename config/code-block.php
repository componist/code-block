<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Template Archive API Base URL
    |--------------------------------------------------------------------------
    |
    | Base URL for API calls and preview image URLs. Fixed from app.url, not configurable.
    | No trailing slash.
    |
    */
    'base_url' => env('BASE_URL', 'http://templatearchive.test'),

    /*
    |--------------------------------------------------------------------------
    | API Endpoints
    |--------------------------------------------------------------------------
    |
    | Paths for all Template Archive API endpoints (relative to api root).
    | Use {id} as placeholder for resource ID where applicable.
    |
    */
    'endpoints' => [
        'code_blocks' => '/api/code-blocks',
        'code_block' => '/api/code-blocks/{id}',
        'code_categories' => '/api/code-categories',
        'code_category' => '/api/code-categories/{id}',
    ],

    /*
    |--------------------------------------------------------------------------
    | API Key
    |--------------------------------------------------------------------------
    |
    | Your Template Archive API key (starts with ta_). Get it from the admin
    | panel under Settings → API.
    |
    */
    'api_key' => env('TEMPLATE_ARCHIVE_API_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | API Response Cache
    |--------------------------------------------------------------------------
    |
    | Cache API responses (categories, code blocks) to avoid repeated requests.
    | Set api_cache_enabled to false to disable caching. When enabled,
    | api_cache_ttl_minutes defines how long responses are cached.
    |
    */
    'api_cache_enabled' => true,
    'api_cache_ttl_minutes' => 30,

    /*
    |--------------------------------------------------------------------------
    | Views path for generated templates
    |--------------------------------------------------------------------------
    |
    | Directory under resources/views where Blade templates will be written
    | (e.g. "pages" → resources/views/pages/).
    |
    */
    'views_path' => env('TEMPLATE_ARCHIVE_VIEWS_PATH', 'pages'),

    /*
    |--------------------------------------------------------------------------
    | Template-Route (Anzeige gebauter Templates)
    |--------------------------------------------------------------------------
    |
    | Route zum Anzeigen von per code-block:pull erzeugten Blade-Templates.
    | Prefix entspricht standardmäßig dem views_path (TEMPLATE_ARCHIVE_VIEWS_PATH),
    | z. B. bei "pages" → GET /pages/hero-page rendert pages.hero-page.
    | Mit CODE_BLOCK_TEMPLATE_ROUTE_PREFIX kann ein eigener Prefix gesetzt werden.
    |
    */
    'route' => [
        'enabled' => env('CODE_BLOCK_TEMPLATE_ROUTE_ENABLED', true),
        'prefix' => env('CODE_BLOCK_TEMPLATE_ROUTE_PREFIX'), // null = views_path (z. B. "pages")
        'name' => env('CODE_BLOCK_TEMPLATE_ROUTE_NAME', 'code-block.template.show'),
        'middleware' => [], // z. B. ['web'] oder ['auth']
    ],

    /*
    |--------------------------------------------------------------------------
    | Template-Builder API (Alpine.js)
    |--------------------------------------------------------------------------
    |
    | API für den Template-Builder: Kategorien/Blöcke abrufen, Template
    | speichern (nach resources/views/pages). Keine eigene Route/Blade-Seite;
    | der Builder wird per Alpine.js eingebunden (Tailwind CDN).
    |
    */
    'builder' => [
        'api_prefix' => env('CODE_BLOCK_BUILDER_API_PREFIX', 'code-block-builder'),
        'middleware' => ['web'], // z. B. ['auth'] für geschützten Zugriff

        // Eigene Route + Blade-Seite für den Builder (Blade lädt die Seite, Alpine greift per API auf Kategorien/Blöcke zu)
        'route' => [
            'enabled' => env('CODE_BLOCK_BUILDER_ROUTE_ENABLED', true),
            'path' => env('CODE_BLOCK_BUILDER_ROUTE_PATH', 'builder'),
            'name' => env('CODE_BLOCK_BUILDER_ROUTE_NAME', 'code-block.builder.page'),
            'middleware' => ['web'],
        ],
    ],

];
