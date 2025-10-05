@extends('layouts.app')
@section('title','Контакты — VicsorCRM')
@section('page_title','Контакты')
@section('page_actions')
    <a href="{{ route('customers.create') }}" class="text-brand-600 hover:underline">+ Добавить</a>
@endsection

@section('content')
    <x-ui.card class="p-4" x-data="contactsTable()">

        <form @submit.prevent="load" class="mb-4">
            <div class="flex gap-2">
                <x-ui.input
                    name="search"
                    x-model="filters.search"
                    placeholder="Поиск по имени, email, телефону"
                    @input.debounce.500ms="load"
                />
                <x-ui.button variant="light" type="button" @click="load">Найти</x-ui.button>
            </div>
        </form>

        <div class="overflow-x-auto relative">
            <div x-show="loading" class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-50 z-10">
                <div class="loader">Загрузка...</div>
            </div>

            <div id="contactsTable" x-html="tableHtml"></div>
        </div>

    </x-ui.card>

    <script>
        function contactsTable() {
            return {
                filters: {
                    search: '{{ request('search') }}',
                },
                tableHtml: '',
                loading: false,

                async load(page = 1, pushState = true) {
                    this.loading = true;
                    try {
                        const params = new URLSearchParams({...this.filters, page});
                        const url = '{{ route('customers.index_ajax') }}?' + params.toString();

                        const response = await fetch(url, {
                            headers: {'X-Requested-With': 'XMLHttpRequest'}
                        });
                        const data = await response.json();

                        if (data.success) {
                            this.tableHtml = data.html;

                            this.$nextTick(() => {
                                document.querySelectorAll('#contactsTable .pagination a').forEach(link => {
                                    link.addEventListener('click', e => {
                                        e.preventDefault();
                                        const url = new URL(link.href);
                                        const pageParam = url.searchParams.get('page') || 1;
                                        this.load(pageParam);
                                        history.pushState(null, '', link.href);
                                    });
                                });
                            });

                            if (pushState) {
                                const newUrl = '{{ route('customers.index') }}?' + params.toString();
                                history.pushState(null, '', newUrl);
                            }
                        } else {
                            alert(data.message || 'Ошибка при загрузке таблицы');
                        }
                    } catch (err) {
                        alert('Ошибка AJAX: ' + err);
                    } finally {
                        this.loading = false;
                    }
                },

                init() {
                    this.load();

                    window.addEventListener('popstate', () => {
                        const params = new URLSearchParams(window.location.search);
                        this.filters.search = params.get('search') || '';
                        const page = params.get('page') || 1;
                        this.load(page, false);
                    });
                }
            }
        }
    </script>
@endsection
