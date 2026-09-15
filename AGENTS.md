# AGENTS – Componist Code Block

## Zweck

Laravel-Client für die Template-Archive-API: Code-Blöcke/Kategorien abrufen, Builder-UI, Blade-Templates unter `resources/views` erzeugen.

## Grenzen & Abhängigkeiten

- Gehört rein: HTTP-Client, Builder-Routes, Blade-Generierung, Caching
- Gehört nicht: Template-Archive-Server selbst
- Status: **registriert** in `bootstrap/providers.php`; Companion `componist/code-block-tracking` im Monorepo

## Struktur

```
src/Domain/TemplateFilename.php
src/Application/BladeTemplateBuilder.php
src/Infrastructure/CodeBlockApiClient.php
src/Client.php, ApiClient.php, TemplateWriter.php (Legacy-Aliase)
config/code-block.php
resources/js/ (Builder-Assets)
```

## Einbindung

- Provider: `Componist\CodeBlock\CodeBlockServiceProvider` (nach Freigabe)
- Config: `.env`-gesteuert (`TEMPLATE_ARCHIVE_API_KEY`, `BASE_URL`, …)
- Routes: Builder + Template-Show (Middleware konfigurierbar)

## Konventionen

- API-Key nur über `.env`, nie committen
- Cache-TTL konfigurierbar

## Tests

Bei Registrierung: Feature-Tests für SSRF/API-Key-Handling und Route-Middleware.

## Security

- Externe URLs validieren; API-Key nicht loggen
- Builder-API, Builder-Seite und Template-Show: Middleware `web` + `auth`
- Builder- und Template-Route default **aus** (`CODE_BLOCK_BUILDER_ROUTE_ENABLED=false`, `CODE_BLOCK_TEMPLATE_ROUTE_ENABLED=false`)
- Skill `security-audit` vor öffentlicher Builder-Route

## Do / Don’t

- Do: README für alle `.env`-Keys pflegen
- Don’t: Package ohne API-Key und Tests aktiv registrieren
