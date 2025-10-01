<div class="bg-white border rounded-2xl shadow-soft">
    <x-ui.card class="p-4">

        <a href="{{ route('shops.create', ['section' => 'product']) }}" class="text-brand-600 hover:underline">+ Создать</a>

        <form method="get" action="{{ route('shops.index') }}" class="mb-4">
            <div class="flex gap-2">
                <x-ui.input name="search" value="{{ $search }}" placeholder="Поиск по названию/slug"/>
                <input type="hidden" name="section" value="{{ $section }}">
                <x-ui.button variant="light">Искать</x-ui.button>
            </div>
        </form>

        @include('shops.products._table', ['items' => $items])
    </x-ui.card>
</div>
