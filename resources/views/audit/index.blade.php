@extends('layouts.app')
@section('title','Аудит — VicsorCRM')
@section('page_title','Журнал действий')

@section('content')
    <x-ui.card class="p-4" x-data="auditTable()">

        <form @submit.prevent="load" class="mb-4">
            <div class="grid md:grid-cols-4 gap-2">

                <x-ui.input
                    name="search"
                    x-model="filters.search"
                    placeholder="Поиск (описание/ID/модель)"
                    @input.debounce.500ms="load"
                />

                <select name="model" x-model="filters.model" @change="load" class="rounded-xl border px-3 py-2">
                    <option value="">Все модели</option>
                    @foreach($models as $m)
                        <option value="{{ $m }}">{{ class_basename($m) }}</option>
                    @endforeach
                </select>

                <select name="event" x-model="filters.event" @change="load" class="rounded-xl border px-3 py-2">
                    <option value="">Все события</option>
                    @foreach(['created','updated','deleted','custom'] as $e)
                        <option value="{{ $e }}">{{ $e }}</option>
                    @endforeach
                </select>

                <x-ui.button variant="light" type="button" @click="load">Фильтр</x-ui-button>
            </div>
        </form>

        <div class="overflow-x-auto relative">
            <div x-show="loading" class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-50 z-10">
                <div class="loader">Загрузка...</div>
            </div>

            <div id="auditTable" x-html="tableHtml"></div>
        </div>

        </x-ui-card>

        <script>
            function auditTable() {
                return {
                    filters: {
                        search: '{{ request('search') }}',
                        model: '{{ request('model') }}',
                        event: '{{ request('event') }}',
                    },
                    tableHtml: '',
                    loading: false,

                    async load(page = 1, pushState = true) {
                        this.loading = true;
                        try {
                            const params = new URLSearchParams({...this.filters, page});
                            const url = '{{ route('audit.index_ajax') }}?' + params.toString();

                            const response = await fetch(url, {
                                headers: {'X-Requested-With': 'XMLHttpRequest'}
                            });
                            const data = await response.json();

                            if (data.success) {
                                this.tableHtml = data.html;

                                // Dynamic pagination
                                this.$nextTick(() => {
                                    document.querySelectorAll('#auditTable .pagination a').forEach(link => {
                                        link.addEventListener('click', e => {
                                            e.preventDefault();
                                            const url = new URL(link.href);
                                            const pageParam = url.searchParams.get('page') || 1;
                                            this.load(pageParam);
                                            history.pushState(null, '', link.href);
                                        });
                                    });
                                });

                                // Refresh browser URL when loading via AJAX
                                if(pushState) {
                                    const newUrl = '{{ route('audit.index') }}?' + params.toString();
                                    history.pushState(null, '', newUrl);
                                }

                            } else {
                                alert(data.message || 'Ошибка при загрузке таблицы');
                            }
                        } catch(err) {
                            alert('Ошибка AJAX: ' + err);
                        } finally {
                            this.loading = false;
                        }
                    },

                    init() {
                        // Loading the table when opening the page
                        this.load();

                        // Processing back/forward buttons
                        window.addEventListener('popstate', () => {
                            const params = new URLSearchParams(window.location.search);
                            this.filters.search = params.get('search') || '';
                            this.filters.model  = params.get('model') || '';
                            this.filters.event  = params.get('event') || '';
                            const page = params.get('page') || 1;
                            this.load(page, false); // Let's not mess things up again
                        });
                    }
                }
            }
        </script>
@endsection
