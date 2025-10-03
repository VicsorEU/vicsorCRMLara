<div class="overflow-x-auto">
    <table class="min-w-full text-sm">
        <thead>
        <tr class="text-left text-slate-500">
            <th class="py-2 pr-4">Изображение</th>
            <th class="py-2 pr-4">Название</th>
            <th class="py-2 pr-4">Слаг</th>
            <th class="py-2 pr-4">Родитель</th>
            <th class="py-2 pr-4"></th>
        </tr>
        </thead>
        <tbody>
        @forelse($roots as $node)
            @include('shops.categories._row', ['node'=>$node, 'groups'=>$groups, 'level'=>0])
        @empty
            <tr>
                <td colspan="5" class="py-6 text-center text-slate-400">Пока нет категорий</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>
