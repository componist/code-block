<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>componist Code-Block Builder – {{ config('app.name') }}</title>

    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    <script>
        window.CODE_BLOCK_BUILDER_API = {
            categoriesUrl: @json(route('code-block.builder.categories')),
            blocksUrl: @json(route('code-block.builder.blocks')),
            saveUrl: @json(route('code-block.builder.store')),
        };
    </script>

    <script type="module" src="{{ route('code-block.builder.script') }}"></script>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="min-h-screen bg-gray-100 antialiased">

    <div class="py-6">
        <div id="code-block-builder" x-data="templateBuilder()" x-init="init()" class="flex min-h-[400px] gap-4 p-4">
            {{-- Code-Blöcke (links) --}}
            <div
                class="w-72 shrink-0 max-h-[calc(100vh-8rem)] overflow-y-auto rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <h3 class="mb-4 text-lg font-semibold text-gray-900">
                    Code-Blöcke
                    <span class="text-sm font-normal text-gray-500" x-show="blocks.length"
                        x-text="'(' + blocks.length + ')'"></span>
                </h3>
                <template x-if="blocksLoading">
                    <p class="text-gray-500">Lade…</p>
                </template>
                <div class="space-y-3" x-show="!blocksLoading && selectedCategoryId">
                    <template x-for="block in blocks" :key="block.id">
                        <div role="button" tabindex="0" draggable="true" @click="addBlock(block)"
                            @dragstart="dragStartBlock($event, block)" @keydown.enter="addBlock(block)"
                            class="cursor-grab active:cursor-grabbing w-full overflow-hidden rounded-lg border border-gray-200 text-left transition-shadow hover:shadow-md">
                            <template x-if="block.preview_image_url">
                                <div class="w-full">
                                    <img :src="block.preview_image_url" :alt="block.title"
                                        class="h-auto w-full object-cover" />
                                </div>
                            </template>
                            <template x-if="!block.preview_image_url">
                                <div class="flex h-28 w-full items-center justify-center bg-gray-100 text-gray-500">Kein
                                    Vorschaubild</div>
                            </template>
                            <div class="bg-gray-50 p-3">
                                <p class="text-sm font-medium text-gray-900" x-text="block.title"></p>
                            </div>
                        </div>
                    </template>
                </div>
                <p class="text-gray-500" x-show="!blocksLoading && !selectedCategoryId">Bitte Kategorie wählen.</p>
            </div>

            {{-- Template-Zone (Mitte) --}}
            <div class="min-w-0 flex-1 overflow-y-auto rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Template</h3>
                    <button x-show="templateBlocks.length > 0" @click="openSaveModal()" type="button"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                        Template speichern
                    </button>
                </div>
                <div class="min-h-[320px] rounded-lg border-2 border-dashed border-gray-300 p-4 transition-colors"
                    :class="{
                        'flex items-center justify-center': templateBlocks.length ===
                            0,
                        'border-indigo-400 bg-indigo-50/50': dragOverTemplate
                    }"
                    @dragover.prevent="dragOverTemplate = true" @dragleave="dragOverTemplate = false"
                    @drop.prevent="handleDrop($event); dragOverTemplate = false">
                    <template x-if="templateBlocks.length === 0">
                        <p class="text-center text-gray-500">Blöcke per <strong>Klick</strong> oder <strong>Drag &amp;
                                Drop</strong> hierher ziehen.</p>
                    </template>
                    <div class="space-y-4" x-show="templateBlocks.length > 0">
                        <template x-for="(block, index) in templateBlocks" :key="index">
                            <div class="cursor-grab active:cursor-grabbing overflow-hidden rounded-lg border border-gray-200 bg-gray-50 transition-shadow hover:shadow-sm"
                                :class="{ 'ring-2 ring-indigo-400': dragOverIndex === index }" draggable="true"
                                @dragstart="dragStartReorder($event, index)" @dragover.prevent="dragOverIndex = index"
                                @dragleave="dragOverIndex = null"
                                @drop.prevent="handleDrop($event, index); dragOverIndex = null">
                                <template x-if="block.preview_image_url">
                                    <div class="w-full overflow-hidden">
                                        <img :src="block.preview_image_url" :alt="block.title"
                                            class="h-auto w-full object-cover" />
                                    </div>
                                </template>
                                <template x-if="!block.preview_image_url">
                                    <div class="h-16 w-full bg-gray-200"></div>
                                </template>
                                <div class="flex items-center justify-between gap-2 p-3">
                                    <span class="min-w-0 flex-1 truncate text-sm font-medium text-gray-900"
                                        x-text="block.title || 'Block'"></span>
                                    <div class="flex shrink-0 gap-1">
                                        <button type="button" x-show="index > 0" @click.stop="moveUp(index)"
                                            class="rounded p-1 text-gray-500 hover:bg-gray-200"
                                            title="Nach oben">↑</button>
                                        <button type="button" x-show="index < templateBlocks.length - 1"
                                            @click.stop="moveDown(index)"
                                            class="rounded p-1 text-gray-500 hover:bg-gray-200"
                                            title="Nach unten">↓</button>
                                        <button type="button" @click.stop="removeBlock(index)"
                                            class="rounded p-1 text-red-600 hover:bg-red-50"
                                            title="Entfernen">✕</button>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            {{-- Kategorien (rechts) --}}
            <div class="w-56 shrink-0 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <h3 class="mb-4 text-lg font-semibold text-gray-900">Kategorien</h3>
                <template x-if="loading">
                    <p class="text-gray-500">Lade…</p>
                </template>
                <div class="space-y-2" x-show="!loading">
                    <template x-for="cat in categories" :key="cat.id">
                        <button type="button" @click="selectCategory(cat.id)"
                            class="w-full rounded-lg px-4 py-3 text-left text-sm transition-colors"
                            :class="selectedCategoryId === cat.id ? 'bg-indigo-600 text-white' :
                                'bg-gray-100 text-gray-800 hover:bg-gray-200'"
                            x-text="cat.name"></button>
                    </template>
                </div>
            </div>

            {{-- Save-Modal --}}
            <div x-show="showSaveModal" x-cloak x-transition
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
                @keydown.escape.window="closeSaveModal()">
                <div class="mx-4 w-full max-w-md rounded-xl bg-white p-6 shadow-xl" @click.stop>
                    <h3 class="mb-4 text-lg font-semibold text-gray-900">Template speichern</h3>
                    <p class="mb-2 text-sm text-gray-600">Dateiname (ohne .blade.php): <code
                            class="rounded bg-gray-100 px-1" x-text="(templateName || '').trim() || '…'"></code></p>
                    <input type="text" x-model="templateName" placeholder="z. B. hero-page"
                        class="mb-4 w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                        @keydown.enter="save()" />
                    <p class="mb-4 text-sm text-red-600" x-show="saveError" x-text="saveError"></p>
                    <div class="flex justify-end gap-3">
                        <button type="button" @click="closeSaveModal()"
                            class="rounded-lg bg-gray-200 px-4 py-2 text-gray-800 hover:bg-gray-300">Abbrechen</button>
                        <button type="button" @click="save()" :disabled="saving"
                            class="rounded-lg bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700 disabled:opacity-50">
                            <span x-text="saving ? 'Speichern…' : 'Speichern'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>

</html>
