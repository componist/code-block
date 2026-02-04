<?php

namespace Componist\CodeBlock\Http\Controllers;

use Componist\CodeBlock\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class TemplateBuilderApiController extends Controller
{
    public function __construct(
        protected Client $client
    ) {}

    /**
     * Kategorien von der Template-Archive-API abrufen.
     */
    public function categories(): JsonResponse
    {
        try {
            $data = $this->client->getCodeCategories();
            return response()->json(['data' => $data]);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 502);
        }
    }

    /**
     * Code-Blöcke (optional nach Kategorie) von der Template-Archive-API abrufen.
     */
    public function blocks(Request $request): JsonResponse
    {
        $categoryId = $request->integer('category_id', 0) ?: null;
        try {
            $data = $this->client->getCodeBlocks($categoryId);
            $data = $this->client->withPreviewImageUrls($data);
            return response()->json(['data' => $data]);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 502);
        }
    }

    /**
     * Template aus gewählten Block-IDs bauen und in resources/views/pages speichern.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:1', 'max:255', 'regex:/^[a-z0-9_-]+$/i'],
            'block_ids' => ['required', 'array', 'min:1'],
            'block_ids.*' => ['integer', 'min:1'],
        ], [
            'name.required' => 'Bitte einen Namen angeben.',
            'name.regex' => 'Nur Buchstaben, Zahlen, Unterstriche und Bindestriche.',
            'block_ids.min' => 'Mindestens einen Block auswählen.',
        ]);

        $name = $validated['name'];
        $blockIds = $validated['block_ids'];

        try {
            $blocks = [];
            foreach ($blockIds as $id) {
                $block = $this->client->getCodeBlock($id);
                $blocks[] = [
                    'html' => $block['html'] ?? null,
                    'css' => $block['css'] ?? null,
                    'js' => $block['js'] ?? null,
                ];
            }
            $path = $this->client->createTemplateFromBlocks($blocks, $name);
            return response()->json([
                'path' => $path,
                'message' => 'Template gespeichert: ' . basename($path),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 502);
        }
    }
}
