@if (session('status'))
    <div class="mb-4 rounded-xl border bg-emerald-50 text-emerald-800 px-4 py-3 text-sm">
        {{ session('status') }}
    </div>
@endif
@if ($errors->any())
    <div class="mb-4 rounded-xl border bg-red-50 text-red-800 px-4 py-3 text-sm">
        <ul class="list-disc ml-5">
            @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
@endif
