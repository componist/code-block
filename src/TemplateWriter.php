<?php

namespace Componist\CodeBlock;

use Illuminate\Support\Facades\File;

class TemplateWriter
{
    public function __construct(
        protected string $viewsBasePath,
        protected string $viewsSubPath = 'pages',
    ) {}

    /**
     * Get the full directory path for views (e.g. resources/views/pages).
     */
    public function getViewsPath(): string
    {
        $path = $this->viewsBasePath;
        if ($this->viewsSubPath !== '') {
            $path .= DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $this->viewsSubPath);
        }

        return $path;
    }

    /**
     * Ensure the views directory exists.
     */
    public function ensureViewsDirectoryExists(): bool
    {
        $path = $this->getViewsPath();
        if (! File::isDirectory($path)) {
            return File::makeDirectory($path, 0755, true);
        }

        return true;
    }

    /**
     * Create a Blade template from one or more code blocks and save to resources/views/pages.
     *
     * @param  array<int, array{html?: string|null, css?: string|null, js?: string|null}>  $blocks  Array of blocks (each with html, css, js)
     * @param  string  $filename  Filename without extension (e.g. "hero-page")
     * @return string Full path to the created file
     */
    public function createFromBlocks(array $blocks, string $filename): string
    {
        $this->ensureViewsDirectoryExists();

        $filename = preg_replace('/[^a-zA-Z0-9_-]/', '', $filename) ?: 'template';
        $filepath = $this->getViewsPath().DIRECTORY_SEPARATOR.$filename.'.blade.php';

        $htmlParts = [];
        $cssParts = [];
        $jsParts = [];

        foreach ($blocks as $block) {
            $html = $block['html'] ?? '';
            $css = $block['css'] ?? '';
            $js = $block['js'] ?? '';

            if ($html !== '' && $html !== null) {
                $htmlParts[] = trim($html);
            }
            if ($css !== '' && $css !== null) {
                $cssParts[] = trim($css);
            }
            if ($js !== '' && $js !== null) {
                $jsParts[] = trim($js);
            }
        }

        $combinedHtml = implode("\n\n", $htmlParts);
        $combinedCss = implode("\n\n", $cssParts);
        $combinedJs = implode("\n\n", $jsParts);

        $content = $this->buildBladeContent($combinedHtml, $combinedCss, $combinedJs);
        File::put($filepath, $content);

        return $filepath;
    }

    /**
     * Create a Blade template from a single code block.
     *
     * @param  array{html?: string|null, css?: string|null, js?: string|null}  $block
     * @return string Full path to the created file
     */
    public function createFromBlock(array $block, string $filename): string
    {
        return $this->createFromBlocks([$block], $filename);
    }

    /**
     * Build Blade file content (HTML layout with optional CSS/JS).
     * User HTML/CSS/JS is wrapped in @verbatim so Blade does not interpret {{ }} or @.
     */
    protected function buildBladeContent(string $html, string $css, string $js): string
    {
        $lines = [
            '<!DOCTYPE html>',
            '<html lang="{{ str_replace(\'_\', \'-\', app()->getLocale()) }}">',
            '<head>',
            '    <meta charset="UTF-8">',
            '    <meta name="viewport" content="width=device-width, initial-scale=1.0">',
            '    <title>@yield(\'title\', config(\'app.name\'))</title>',
            '    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>',
        ];

        if ($css !== '') {
            $lines[] = '    <style>';
            $lines[] = '@verbatim';
            foreach (explode("\n", $css) as $line) {
                $lines[] = '    '.$line;
            }
            $lines[] = '@endverbatim';
            $lines[] = '    </style>';
        }

        $lines[] = '</head>';
        $lines[] = '<body>';
        $lines[] = '@verbatim';
        foreach (explode("\n", $html) as $line) {
            $lines[] = $line;
        }
        $lines[] = '@endverbatim';

        if ($js !== '') {
            $lines[] = '    <script>';
            $lines[] = '@verbatim';
            foreach (explode("\n", $js) as $line) {
                $lines[] = '    '.$line;
            }
            $lines[] = '@endverbatim';
            $lines[] = '    </script>';
        }

        $lines[] = '</body>';
        $lines[] = '</html>';

        return implode("\n", $lines);
    }
}
