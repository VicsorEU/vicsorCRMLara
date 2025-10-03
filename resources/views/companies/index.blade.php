@extends('layouts.app')
@section('title','Компании — VicsorCRM')
@section('page_title','Компании')
@section('page_actions')
    <a href="{{ route('companies.create') }}" class="text-brand-600 hover:underline">+ Создать</a>
@endsection

@section('content')
    <x-ui.card class="p-4" x-data="companiesTable()" x-init="init()">

        {{-- Фільтр --}}
        <form @submit.prevent="load" class="mb-4">
            <div class="flex gap-2">
                <x-ui.input
                    name="search"
                    x-model="filters.search"
                    placeholder="Поиск по названию, email, телефону"
                    @input.debounce.500ms="load"
                />
                <x-ui.button variant="light" type="button" @click="load">Найти</x-ui-button>
            </div>
        </form>

        {{-- Таблиця --}}
        <div class="overflow-x-auto relative">
            <template x-if="loading">
                <div class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-50 z-10">
                    <div class="loader">Загрузка...</div>
                </div>
            </template>
            <div id="companiesTable" x-html="tableHtml"></div>
        </div>
    </x-ui.card>

    <script>
        function companiesTable() {
            return {
                filters: { search: '{{ request('search') }}' },
                tableHtml: '',
                loading: false,

                async load(page = 1) {
                    this.loading = true;
                    try {
                        const params = new URLSearchParams({...this.filters, page});
                        const response = await fetch('{{ route('company.index_ajax') }}?' + params.toString(), {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        });
                        const data = await response.json();

                        if (data.success) {
                            this.tableHtml = data.html;

                            this.$nextTick(() => {
                                // Прив’язка пагінації тільки в межах цього компоненту
                                this.$root.querySelectorAll('#companiesTable .pagination a').forEach(link => {
                                    link.addEventListener('click', e => {
                                        e.preventDefault();
                                        const url = new URL(link.href);
                                        const pageParam = url.searchParams.get('page') || 1;
                                        this.load(pageParam);
                                        history.pushState(null, '', link.href);
                                    });
                                });
                            });
                        }
                    } catch (err) {
                        alert('Ошибка AJAX: ' + err);
                    } finally {
                        this.loading = false;
                    }
                },

                init() {
                    if (document.getElementById('companiesTable')) {
                        this.load();
                    }
                }
            }
        }
    </script>
@endsection
