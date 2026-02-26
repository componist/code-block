<?php

namespace Componist\CodeBlock\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\View;

class ShowTemplateController extends Controller
{
    /**
     * Zeigt ein per code-block:pull erzeugtes Blade-Template an.
     */
    public function __invoke(string $name)
    {
        $name = preg_replace('/[^a-z0-9_-]/', '', $name) ?: null;
        if ($name === null) {
            abort(404, 'Template nicht gefunden');
        }

        $viewsPath = str_replace('/', '.', config('code-block.views_path', 'pages'));
        $viewName = $viewsPath.'.'.$name;

        if (! View::exists($viewName)) {
            abort(404, 'Template nicht gefunden');
        }

        return view($viewName, [
            'title' => ucfirst(str_replace(['-', '_'], ' ', $name)),
        ]);
    }
}
