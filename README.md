# Componist CodeBlock

Laravel-Client für die **Template Archive** API: Code-Blöcke und Kategorien abrufen, Template-Vorschaubilder als URLs nutzen und Blade-Templates in `resources/views` (z. B. unter `pages`) erzeugen.

**Namespace:** `Componist\CodeBlock`

---

## Voraussetzungen

- PHP 8.2+
- Laravel 11 oder 12
- Template-Archive-Instanz mit aktivierter API und API-Schlüssel

---

## Installation

```bash
composer require componist/code-block
```

**Kein Publish nötig** – Konfiguration erfolgt über `.env`. Das Builder-JavaScript wird per Route aus dem Package ausgeliefert.

---

## Konfiguration

### Übersicht: Alle Einstellungen

| .env-Variable | Config-Schlüssel | Standard | Beschreibung |
|---------------|------------------|----------|--------------|
| `BASE_URL` | `base_url` | *siehe Config* | Basis-URL für API-Aufrufe und Preview-Bilder (ohne trailing slash). In der Package-Config: `env('BASE_URL', 'http://templatearchive.test')`. In der App empfohlen: `BASE_URL` oder `APP_URL` in `.env` setzen. |
| `TEMPLATE_ARCHIVE_API_KEY` | `api_key` | `''` | API-Schlüssel der Template-Archive-Instanz (beginnt mit `ta_`). |
| `TEMPLATE_ARCHIVE_VIEWS_PATH` | `views_path` | `pages` | Unterordner unter `resources/views`, in dem Blade-Templates erzeugt werden (z. B. `pages` → `resources/views/pages/`). |
| – | `api_cache_enabled` | `true` | API-Antworten (Kategorien, Code-Blöcke) cachen. |
| – | `api_cache_ttl_minutes` | `30` | Cache-Laufzeit in Minuten (nur wirksam wenn `api_cache_enabled` true). |
| `CODE_BLOCK_TEMPLATE_ROUTE_ENABLED` | `route.enabled` | `true` | Route zum Anzeigen gebauter Templates aktivieren. |
| `CODE_BLOCK_TEMPLATE_ROUTE_PREFIX` | `route.prefix` | *(leer = views_path)* | URL-Prefix für Template-Anzeige (z. B. `pages` oder `template`). Leer = Wert von `views_path`. |
| `CODE_BLOCK_TEMPLATE_ROUTE_NAME` | `route.name` | `code-block.template.show` | Name der Template-Anzeige-Route. |
| – | `route.middleware` | `[]` | Middleware für die Template-Anzeige-Route (z. B. `['web']`, `['auth']`). |
| `CODE_BLOCK_BUILDER_API_PREFIX` | `builder.api_prefix` | `code-block-builder` | URL-Prefix für Builder-API (Kategorien, Blöcke, Speichern) und Asset. |
| `CODE_BLOCK_BUILDER_OMIT_CDN` | `builder.omit_cdn` | `false` | `true` = Tailwind und Alpine nicht per CDN laden (wenn die Host-App sie bereits einbindet). |
| – | `builder.middleware` | `['web']` | Middleware für alle Builder-API-Routen. |
| `CODE_BLOCK_BUILDER_ROUTE_ENABLED` | `builder.route.enabled` | `true` | Eigene Builder-Seiten-Route aktivieren. |
| `CODE_BLOCK_BUILDER_ROUTE_PATH` | `builder.route.path` | `builder` | URL-Pfad der Builder-Seite (z. B. `/builder`). |
| `CODE_BLOCK_BUILDER_ROUTE_NAME` | `builder.route.name` | `code-block.builder.page` | Name der Builder-Seiten-Route. |
| – | `builder.route.middleware` | `['web']` | Middleware für die Builder-Seiten-Route. |
| `CODE_BLOCK_BUILDER_PAGE_TITLE` | `builder.page_title` | `Template Builder` | Seitentitel der Builder-Seite. |

### Kompletter .env-Block (zum Kopieren)

Alle per `.env` konfigurierbaren Optionen in einem Block. Einfach in die `.env` einfügen und anpassen. Auskommentierte Zeilen sind optional bzw. nutzen den Standard.

```env
# ---------------------------------------------------------------------------
# Componist Code-Block (Template Archive API Client)
# ---------------------------------------------------------------------------
# Basis-URL der Template-Archive-Instanz (API + Preview-Bilder). Ohne Schrägstrich am Ende.
BASE_URL=${APP_URL}

# API-Schlüssel aus dem Template-Archive-Admin (Einstellungen → API). Beginnt mit ta_
TEMPLATE_ARCHIVE_API_KEY=

# Unterordner unter resources/views für erzeugte Blade-Templates (z. B. pages)
TEMPLATE_ARCHIVE_VIEWS_PATH=pages

# Route: gebaute Templates im Browser anzeigen (GET /pages/hero-page etc.)
CODE_BLOCK_TEMPLATE_ROUTE_ENABLED=true
# CODE_BLOCK_TEMPLATE_ROUTE_PREFIX=pages
# CODE_BLOCK_TEMPLATE_ROUTE_NAME=code-block.template.show

# Builder-API und Builder-Seite
CODE_BLOCK_BUILDER_API_PREFIX=code-block-builder
CODE_BLOCK_BUILDER_ROUTE_ENABLED=true
CODE_BLOCK_BUILDER_ROUTE_PATH=builder
# CODE_BLOCK_BUILDER_ROUTE_NAME=code-block.builder.page
CODE_BLOCK_BUILDER_PAGE_TITLE=Template Builder
# Tailwind/Alpine nicht per CDN laden (true wenn die App sie schon einbindet)
# CODE_BLOCK_BUILDER_OMIT_CDN=false
```

**Hinweis:** `api_cache_enabled` und `api_cache_ttl_minutes` sowie die Middleware-Arrays werden nur in der Config-Datei gesetzt (nach `php artisan vendor:publish --tag=componist-code-block-config`).

Im Package liegt außerdem die Datei `env.code-block.example` – Inhalt in die `.env` der App kopieren und anpassen.

### Minimale .env-Einträge

```env
TEMPLATE_ARCHIVE_API_KEY=ta_ihr_48_zeichen_langer_schluessel
TEMPLATE_ARCHIVE_VIEWS_PATH=pages
```

Optional, falls die App nicht unter `APP_URL` erreichbar ist:

```env
BASE_URL=https://ihre-template-archive-instanz.de
```

### Config-Datei optional veröffentlichen

```bash
php artisan vendor:publish --tag=componist-code-block-config
```

Danach: `config/code-block.php` anpassen. Werte aus `.env` haben Vorrang, sofern sie dort gesetzt sind.

### Builder-Assets optional veröffentlichen

Falls Sie `template-builder.js` selbst ausliefern möchten (z. B. für CDN/Cache):

```bash
php artisan vendor:publish --tag=componist-code-block-assets
```

Datei landet in `public/vendor/code-block/template-builder.js`. Standardmäßig wird das Script über die Route `GET /code-block-builder/assets/template-builder.js` aus dem Package geliefert.

---

## Nutzung

### Client injizieren

```php
use Componist\CodeBlock\Client;

// Im Controller, Job, Command etc.
public function __construct(
    private Client $client
) {}
```

### Code-Blöcke

| Methode | Beschreibung |
|---------|---------------|
| `getCodeBlocks(?int $categoryId = null): array` | Alle Code-Blöcke; optional gefiltert nach `category_id`. Wirft `Illuminate\Http\Client\RequestException` bei API-Fehlern. |
| `getCodeBlock(int $id): array` | Einzelnen Code-Block nach ID. |

```php
$blocks = $client->getCodeBlocks();           // alle
$blocks = $client->getCodeBlocks(categoryId: 1);

$block = $client->getCodeBlock(7);
```

### Kategorien

| Methode | Beschreibung |
|---------|---------------|
| `getCodeCategories(): array` | Alle Code-Kategorien. |
| `getCodeCategory(int $id): array` | Eine Kategorie inkl. zugehöriger `code_blocks`. |

```php
$categories = $client->getCodeCategories();
$category = $client->getCodeCategory(1);
```

### Preview-Bilder

Die API liefert bei Code-Blöcken `preview_image` als relativen Pfad (z. B. `code-blocks/abc.jpg`). Der Client baut daraus die volle URL.

| Methode | Beschreibung |
|---------|---------------|
| `getPreviewImageUrl(?string $previewImagePath): ?string` | Aus relativem Pfad wird `{BASE_URL}/storage/{path}` (ohne `public/` im Pfad). |
| `withPreviewImageUrls(array $blocks): array` | Fügt jedem Block im Array das Feld `preview_image_url` hinzu (in place). |

```php
$url = $client->getPreviewImageUrl($block['preview_image']);

$blocks = $client->getCodeBlocks(1);
$blocks = $client->withPreviewImageUrls($blocks);
// Jeder Eintrag hat nun zusätzlich 'preview_image_url'
```

### Blade-Templates erzeugen

| Methode | Beschreibung |
|---------|---------------|
| `createTemplateFromBlockId(int $codeBlockId, string $filename): string` | Holt den Block von der API und schreibt eine Blade-Datei. Gibt den vollen Dateipfad zurück. |
| `createTemplateFromBlocks(array $blocks, string $filename): string` | Erzeugt eine Blade-Datei aus mehreren Blöcken. Jeder Block: `['html' => ?, 'css' => ?, 'js' => ?]`. Gibt den vollen Dateipfad zurück. |

Dateiname: Nur Zeichen `a-zA-Z0-9_-` werden übernommen; ungültige Zeichen werden entfernt. Leerer Name wird zu `template`.

```php
// Ein Block von der API → Blade
$path = $client->createTemplateFromBlockId(7, 'hero-page');
// → z. B. .../resources/views/pages/hero-page.blade.php

// Mehrere Blöcke (z. B. aus API geholt)
$path = $client->createTemplateFromBlocks([
    ['html' => '...', 'css' => '...', 'js' => '...'],
    ['html' => '...', 'css' => null, 'js' => null],
], 'landing-page');
```

### TemplateWriter und ApiClient

| Methode | Beschreibung |
|---------|---------------|
| `getTemplateWriter(): TemplateWriter` | Instanz für Views-Pfad und Verzeichnis-Erstellung. |
| `getApiClient(): ApiClient` | Low-Level API-Client. |

```php
$writer = $client->getTemplateWriter();
$writer->getViewsPath();           // z. B. .../resources/views/pages
$writer->ensureViewsDirectoryExists();
```

---

## Artisan-Befehle

| Befehl | Beschreibung |
|--------|---------------|
| `php artisan code-block:categories` | Listet alle Kategorien (ID, Name, Description) aus der Template-Archive-API. |
| `php artisan code-block:blocks` | Listet alle Code-Blöcke (ID, Title, Category, Preview-Pfad). |
| `php artisan code-block:blocks --category=1` | Nur Blöcke der Kategorie mit ID 1. |
| `php artisan code-block:blocks --with-urls` | Zusätzlich Spalte „Preview URL“ (volle URLs). |
| `php artisan code-block:pull {block_id} {filename}` | Holt Block von der API und erzeugt `resources/views/{views_path}/{filename}.blade.php`. |

Beispiele:

```bash
php artisan code-block:categories
php artisan code-block:blocks --category=1 --with-urls
php artisan code-block:pull 7 hero-page
```

---

## Routen

### Template-Anzeige (gebaute Templates im Browser)

Aktivierung: `CODE_BLOCK_TEMPLATE_ROUTE_ENABLED=true` (Standard).

- **URL:** `GET /{prefix}/{name}`  
  - `prefix` = `CODE_BLOCK_TEMPLATE_ROUTE_PREFIX` oder, wenn leer, `TEMPLATE_ARCHIVE_VIEWS_PATH` (z. B. `pages`).  
  - `name` = Dateiname ohne `.blade.php` (nur `a-z0-9_-`).
- **Beispiel:** `GET /pages/hero-page` rendert die View `pages.hero-page`.
- **Route-Name:** `code-block.template.show`  
  - Beispiel: `route('code-block.template.show', 'hero-page')`.

Die View erhält `['title' => ...]` (aus dem Dateinamen abgeleitet). Existiert die View nicht, wird 404 zurückgegeben.

**Deaktivieren:** `CODE_BLOCK_TEMPLATE_ROUTE_ENABLED=false`.  
**Middleware:** In `config/code-block.php` unter `route.middleware` setzen (z. B. `['web']`, `['auth']`).

---

### Template-Builder (Seite + API)

#### Builder-Seiten-Route

Aktivierung: `CODE_BLOCK_BUILDER_ROUTE_ENABLED=true` (Standard).

- **URL:** `GET /builder` (oder Wert von `CODE_BLOCK_BUILDER_ROUTE_PATH`).
- **Route-Name:** `code-block.builder.page`  
  - Beispiel: `route('code-block.builder.page')`.
- **Inhalt:** Blade-View `code-block::builder-page` mit Komponente `<x-code-block::builder />`, Tailwind v4 und Alpine.js (sofern nicht `omit_cdn`), plus `template-builder.js` von der Builder-Asset-Route.

**Deaktivieren:** `CODE_BLOCK_BUILDER_ROUTE_ENABLED=false`. Die Builder-API-Routen bleiben davon unberührt.

#### Builder nur als Komponente (ohne eigene Route)

Auf einer beliebigen Route/Seite: `<x-code-block::builder />` einbinden. Im Layout CSRF-Meta-Tag setzen: `<meta name="csrf-token" content="{{ csrf_token() }}">`. Die API-URLs werden von der Komponente per Route-Namen gesetzt.

#### Builder-API (für Alpine.js / Frontend)

Alle unter dem Prefix `CODE_BLOCK_BUILDER_API_PREFIX` (Standard: `code-block-builder`), Middleware: `builder.middleware` (Standard: `['web']`).

| Methode | URL | Beschreibung |
|---------|-----|--------------|
| GET | `/{api_prefix}/categories` | Kategorien von der Template-Archive-API. |
| GET | `/{api_prefix}/blocks?category_id={id}` | Code-Blöcke (mit `preview_image_url`). |
| POST | `/{api_prefix}/templates` | Template speichern. Body: `{ "name": "hero-page", "block_ids": [1, 2, 3] }`. |
| GET | `/{api_prefix}/assets/template-builder.js` | Liefert `template-builder.js` aus dem Package. |

**POST /templates – Validierung:**

- `name`: erforderlich, 1–255 Zeichen, nur `a-z0-9_-` (Regex: `^[a-z0-9_-]+$`).
- `block_ids`: Array, mindestens ein Element; jedes Element Integer ≥ 1.

Antwort bei Erfolg: `{ "path": "...", "message": "..." }`. Bei Fehler: 502 mit `{ "message": "..." }`.

---

## Erzeugte Blade-Dateien

- **Speicherort:** `resources/views/{views_path}/{filename}.blade.php` (z. B. `resources/views/pages/hero-page.blade.php`).
- **Inhalt:** Eigenständiges HTML-Dokument mit DOCTYPE, `<head>`, Tailwind v4 (CDN), optional `<style>` und `<script>`. HTML/CSS/JS der Blöcke steht in `@verbatim`, damit `{{ }}` und `@` in den Blöcken nicht von Blade interpretiert werden.
- **Title:** `@yield('title', config('app.name'))`.

---

## API-Cache

- **Aktivierung:** `config('code-block.api_cache_enabled')` (Standard: `true`).
- **TTL:** `config('code-block.api_cache_ttl_minutes')` (Standard: `30`). Bei 0 oder wenn Cache deaktiviert ist, wird nicht gecacht.
- Gecacht werden: `getCodeBlocks` (pro Kategorie und „all“), `getCodeBlock`, `getCodeCategories`, `getCodeCategory`.  
- Cache-Key-Präfix: `componist.code_block.api`. Bei `app.debug` true wird ein Cache-Hit ins Log geschrieben.

---

## Lizenz

MIT
