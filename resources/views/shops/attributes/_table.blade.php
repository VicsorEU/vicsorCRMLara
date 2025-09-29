<div class="overflow-x-auto">
    <table class="min-w-full text-sm">
        <thead>
        <tr class="text-left text-slate-500">
            <th class="py-2 pr-4">Название</th>
            <th class="py-2 pr-4">Слаг</th>
            <th class="py-2 pr-4">Значений</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        @foreach($items as $it)
            <tr class="border-t">
                <td class="py-2 pr-4 font-medium">{{ $it->name }}</td>
                <td class="py-2 pr-4 text-slate-500">{{ $it->slug }}</td>
                <td class="py-2 pr-4">{{ $it->values_count }}</td>
                <td class="py-2 text-right">
                    <a href="{{ route('shops.attribute.edit', ['section' => 'attributes', 'attribute' => $it]) }}" class="text-slate-500 hover:text-slate-800">Изм.</a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $items->links() }}</div>
