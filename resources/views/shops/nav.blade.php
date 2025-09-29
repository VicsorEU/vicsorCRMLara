<div class="space-y-6" x-data="generalSettings()">
    <div class="bg-white border rounded-2xl shadow-soft">
        <div class="px-5 py-3 border-b font-medium">Поднастройки</div>
        <div class="p-5">
            <nav class="flex flex-wrap gap-2">
                <a href="{{ route('shops.index') }}"
                   class="px-3 py-1.5 rounded-lg border {{ $section === 'products' ? 'bg-brand-50 border-brand-200 text-brand-700' : 'hover:bg-slate-50' }}">
                    Товары
                </a>
                <a href="{{ route('shops.index', ['section' => 'attributes']) }}"
                   class="px-3 py-1.5 rounded-lg border {{ $section === 'attributes' ? 'bg-brand-50 border-brand-200 text-brand-700' : 'hover:bg-slate-50' }}">
                    Атрибуты
                </a>
                <a href="{{ route('shops.index', ['section' => 'warehouses']) }}"
                   class="px-3 py-1.5 rounded-lg border {{ $section === 'warehouses' ? 'bg-brand-50 border-brand-200 text-brand-700' : 'hover:bg-slate-50' }}">
                    Склады
                </a>
                <a href="{{ route('shops.index', ['section' => 'categories']) }}"
                   class="px-3 py-1.5 rounded-lg border {{ $section === 'categories' ? 'bg-brand-50 border-brand-200 text-brand-700' : 'hover:bg-slate-50' }}">
                    Категории
                </a>
            </nav>
        </div>
    </div>
</div>
