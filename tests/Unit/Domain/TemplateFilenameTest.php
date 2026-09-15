<?php

declare(strict_types=1);

namespace Componist\CodeBlock\Tests\Unit\Domain;

use Componist\CodeBlock\Domain\TemplateFilename;
use PHPUnit\Framework\TestCase;

class TemplateFilenameTest extends TestCase
{
    public function test_sanitize_strips_unsafe_characters(): void
    {
        $this->assertSame('hero-page', TemplateFilename::sanitize('hero-page'));
        $this->assertSame('heropage', TemplateFilename::sanitize('hero/page'));
    }

    public function test_sanitize_falls_back_to_template(): void
    {
        $this->assertSame('template', TemplateFilename::sanitize('///'));
    }
}
