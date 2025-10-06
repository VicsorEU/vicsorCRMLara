@props(['node','groups','level'=>0])

@php
    /** @var \App\Models\Category $node */
    $children    = $groups->get($node->id, collect());
    $hasChildren = $children->isNotEmpty();
    $indentPx    = $level * 16; // отступ слева в пикселях
@endphp

<tr
    data-id="{{ $node->id }}"
    data-parent="{{ $node->parent_id ?? 0 }}"
    data-level="{{ $level }}"
    class="{{ $level > 0 ? 'hidden' : '' }}"
>
    <td class="py-2 pr-4">
        @if($hasChildren)
            <button type="button"
                    class="toggle-btn mr-2 rounded border px-1.5 text-xs align-middle"
                    data-toggle="{{ $node->id }}"
                    aria-expanded="false"
                    title="Развернуть">
                <span>+</span>
            </button>
        @else
            <span class="inline-block w-4 mr-2 align-middle"></span>
        @endif

        <span class="inline-block align-middle" style="margin-left: {{ $indentPx }}px">
       @if($node->image_url)
                <img src="{{ $node->image_url }}" alt="" class="h-10 w-10 object-cover rounded-lg border">
            @else
                <div class="h-10 w-10 rounded-lg bg-slate-100 border"></div>
            @endif
    </span>
    </td>
    <td class="py-2 pr-4 font-medium">{{ $node->name }}</td>
    <td class="py-2 pr-4 text-slate-500">{{ $node->slug }}</td>
    <td class="py-2 pr-4">{{ optional($node->parent)->name ?: '—' }}</td>
    <td class="py-2 text-right">
        <a href="{{ route('shops.category.edit', ['section' => 'categories', 'category' => $node]) }}"
           class="edit-btn text-slate-500 hover:text-slate-800">
            Изм.
        </a>
    </td>
</tr>

@foreach($children as $child)
    @include('shops.categories._row', ['node'=>$child, 'groups'=>$groups, 'level'=>$level+1])
@endforeach
