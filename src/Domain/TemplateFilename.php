<?php

declare(strict_types=1);

namespace Componist\CodeBlock\Domain;

final class TemplateFilename
{
    public static function sanitize(string $filename): string
    {
        $sanitized = preg_replace('/[^a-zA-Z0-9_-]/', '', $filename);

        return ($sanitized === null || $sanitized === '') ? 'template' : $sanitized;
    }
}
