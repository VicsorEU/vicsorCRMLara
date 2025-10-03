@extends('layouts.app')
@section('title','Компании — VicsorCRM')
@section('page_title','Компании')
@section('page_actions')
    <a href="{{ route('companies.create') }}" class="text-blue-600 hover:underline">+ Создать</a>
@endsection

@section('content')
    <div class="p-4 border rounded-xl shadow" x-data="companiesTable()" x-init="init()">

        <form @submit.prevent="load" class="mb-4 flex gap-2">
            <input type="text" name="search" x-model="filters.search"
                   placeholder="Поиск по названию, email, телефону"
                   class="border rounded-xl px-3 py-2 w-full"
                   @input.debounce.500ms="load"/>
            <button type="button" class="px-4 py-2 border rounded-xl bg-gray-100"
                    @click="load">Найти</button>
        </form>

        <div class="overflow-x-auto relative">
            <template x-if="loading">
                <div class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-50 z-10">
                    <div class="loader">Загрузка...</div>
                </div>
            </template>
            <div id="companiesTable" x-html="tableHtml" @click="handlePagination($event)"></div>
        </div>
    </div>

    <script>
        function companiesTable() {
            return {
                filters: { search: '{{ request('search') }}' },
                tableHtml: '',
                loading: false,

                async load(page = 1, pushState = true) {
                    this.loading = true;
                    try {
                        const params = new URLSearchParams({ ...this.filters, page });
                        const url = '{{ route('company.index_ajax') }}?' + params.toString();

                        const response = await fetch(url, {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        });
                        const data = await response.json();

                        if (data.success) {
                            this.tableHtml = data.html;

                            if(pushState){
                                history.pushState(null, '', '{{ route('companies.index') }}?' + params.toString());
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

                handlePagination(event) {
                    const link = event.target.closest('a');
                    if (!link) return;

                    if (!link.closest('.pagination')) return;

                    event.preventDefault();
                    const url = new URL(link.href);
                    const pageParam = url.searchParams.get('page') || 1;
                    this.load(pageParam);
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
