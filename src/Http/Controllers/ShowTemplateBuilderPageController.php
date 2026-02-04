<?php

namespace Componist\CodeBlock\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\View\View;

class ShowTemplateBuilderPageController extends Controller
{
    /**
     * Zeigt die Template-Builder-Seite (Blade). Alpine.js greift per API auf Kategorien/Blöcke zu.
     */
    public function __invoke(): View
    {
        return view('code-block::builder-page');
    }
}
