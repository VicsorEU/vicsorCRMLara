<div class="bg-white border rounded-2xl shadow-soft">
    <x-ui.card class="p-4">
        <a href="{{ route('shops.create', ['section' => 'attribute']) }}" class="text-brand-600 hover:underline">+ Новый атрибут</a>

        <form method="get" class="mb-4">
            <div class="flex gap-2">
                <x-ui.input name="search" value="{{ $search }}" placeholder="Поиск по названию/слагу"/>
                <input type="hidden" name="section" value="{{ $section }}">
                <x-ui.button variant="light">Искать</x-ui.button>
            </div>
        </form>

        @include('shops.attributes._table', ['items' => $items])
    </x-ui.card>
</div>
