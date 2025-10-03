<div x-data="deleteProduct()" class="bg-white border rounded-2xl shadow-soft p-6">
    <h1 class="mb-4 text-2xl font-semibold">Редактировать склад</h1>

    <form @submit.prevent="submitForm">
        @csrf
        <x-ui.button variant="light">Удалить</x-ui.button>
    </form>

    <x-ui.card class="p-6 max-w-5xl mt-6">
        @include('shops.products._form', [
            'product' => $product,
            'values'  => $values,
            'action'  => route('products.update', $product),
            'method'  => 'PUT',
        ])
    </x-ui.card>
</div>

<script>
    function deleteProduct() {
        return {
            message: '',
            type: '',
            loading: false,
            submitForm() {
                if (!confirm('Удалить продукт? Все значения тоже будут удалены.')) {
                    return;
                }

                this.loading = true;

                fetch("{{ route('products.destroy', $product) }}", {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(res => res.json())
                    .then(response => {
                        if (response.success) {
                            this.message = response.message || 'Продукт удален';
                            this.type = 'success';
                            setTimeout(() => {
                                this.message = '';
                            }, 3000);

                            window.location.href = "{{ route('shops.index',['section'=>'products']) }}";
                        } else {
                            this.message = response.message || 'Ошибка при удалении';
                            this.type = 'error';
                            setTimeout(() => {
                                this.message = '';
                            }, 3000);
                        }
                    })
                    .catch(err => {
                        this.message = 'Ошибка удаления';
                        this.type = 'error';
                        setTimeout(() => {
                            this.message = '';
                        }, 3000);
                    })
                    .finally(() => {
                        this.loading = false;
                    });
            }
        }
    }
</script>
