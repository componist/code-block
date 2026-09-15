<?php

declare(strict_types=1);

namespace Componist\CodeBlock\Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class CodeBlockFeatureTest extends TestCase
{
    public function test_artisan_commands_are_registered(): void
    {
        $this->artisan('code-block:categories --help')
            ->assertSuccessful();

        $this->artisan('code-block:blocks --help')
            ->assertSuccessful();
    }

    public function test_builder_page_is_not_registered_by_default(): void
    {
        $this->assertFalse(Route::has('code-block.builder.page'));
    }

    public function test_template_show_is_not_registered_by_default(): void
    {
        $this->assertFalse(Route::has('code-block.template.show'));
    }

    public function test_builder_api_requires_authentication(): void
    {
        $this->get(route('code-block.builder.categories'))
            ->assertRedirect();
    }
}
