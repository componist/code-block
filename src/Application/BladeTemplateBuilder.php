<?php

declare(strict_types=1);

namespace Componist\CodeBlock\Application;

use Componist\CodeBlock\Domain\TemplateFilename;
use Illuminate\Support\Facades\File;

class BladeTemplateBuilder
{
    public function __construct(
        protected string $viewsBasePath,
        protected string $viewsSubPath = 'pages',
    ) {}

    public function getViewsPath(): string
    {
        $path = $this->viewsBasePath;
        if ($this->viewsSubPath !== '') {
            $path .= DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $this->viewsSubPath);
        }

        return $path;
    }

    public function ensureViewsDirectoryExists(): bool
    {
        $path = $this->getViewsPath();
        if (! File::isDirectory($path)) {
            return File::makeDirectory($path, 0755, true);
        }

        return true;
    }

    /**
     * @param  array<int, array{html?: string|null, css?: string|null, js?: string|null}>  $blocks
     */
    public function createFromBlocks(array $blocks, string $filename): string
    {
        $this->ensureViewsDirectoryExists();

        $filename = TemplateFilename::sanitize($filename);
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

        $content = $this->buildBladeContent(
            implode("\n\n", $htmlParts),
            implode("\n\n", $cssParts),
            implode("\n\n", $jsParts),
        );
        File::put($filepath, $content);

        return $filepath;
    }

    /**
     * @param  array{html?: string|null, css?: string|null, js?: string|null}  $block
     */
    public function createFromBlock(array $block, string $filename): string
    {
        return $this->createFromBlocks([$block], $filename);
    }

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
