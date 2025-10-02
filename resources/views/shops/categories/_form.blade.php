@props(['category','parents','action','method'=>'POST'])

<form id="categoryForm" method="post" action="{{ $action }}" enctype="multipart/form-data"
      x-data="catForm({{ json_encode(['currentUrl' => $category->image_url]) }})"
      data-mode="{{ $category->exists ? 'edit' : 'create' }}"
      class="space-y-6">
    @csrf
    @if(in_array(strtoupper($method), ['PUT','PATCH','DELETE']))
        @method($method)
    @endif

    <div class="grid md:grid-cols-2 gap-6">
        <div class="space-y-4">
            <div>
                <x-ui.label>Название *</x-ui.label>
                <x-ui.input name="name" value="{{ old('name', $category->name) }}" required/>
            </div>
            <div>
                <x-ui.label>Слаг *</x-ui.label>
                <x-ui.input name="slug" value="{{ old('slug', $category->slug) }}" placeholder="naprimer-tak" required/>
                <div class="text-xs text-slate-500 mt-1">Только латиница, цифры и дефисы.</div>
            </div>
            <div>
                <x-ui.label>Родитель</x-ui.label>
                <select name="parent_id" class="w-full rounded-xl border px-3 py-2">
                    <option value="">—</option>
                    @foreach($parents as $p)
                        <option value="{{ $p->id }}" @selected(old('parent_id', $category->parent_id)==$p->id)>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="space-y-4">
            <div>
                <x-ui.label>Изображение</x-ui.label>

                <div class="flex items-start gap-4">
                    <template x-if="previewUrl">
                        <img :src="previewUrl" class="h-28 w-28 object-cover rounded-xl border" alt="preview">
                    </template>
                    <template x-if="!previewUrl">
                        <img x-show="currentUrl" :src="currentUrl" class="h-28 w-28 object-cover rounded-xl border" alt="current">
                    </template>
                    <div class="flex-1 space-y-2">
                        <input type="file" name="image" accept="image/*"
                               @change="onFileChange($event)"
                               class="block w-full text-sm file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border file:bg-white">
                        <div class="flex gap-2">
                            <button type="button" class="px-3 py-1.5 rounded-lg border"
                                    @click="clearFile()">Очистить выбор</button>
                            @if($category->image_url)
                                <label class="inline-flex items-center gap-2 text-sm">
                                    <input type="checkbox" name="remove_image" value="1" @change="removeCurrent($event)"
                                           class="rounded border">
                                    <span>Удалить текущее</span>
                                </label>
                            @endif
                        </div>
                        <div class="text-xs text-slate-500">PNG/JPG/WEBP, до 3 МБ.</div>
                    </div>
                </div>
            </div>

            <div>
                <x-ui.label>Описание</x-ui.label>
                <textarea name="description" rows="5" class="w-full rounded-xl border px-3 py-2">{{ old('description', $category->description) }}</textarea>
            </div>
        </div>
    </div>

    <div class="flex gap-2">
        <x-ui.button type="button" id="categoryBtnForm">Сохранить</x-ui.button>
        <a href="{{ route('shops.index', ['section' => 'category']) }}" class="px-4 py-2 rounded-xl border">Отмена</a>
    </div>
</form>

<script>
    function catForm(initial){
        return {
            currentUrl: initial.currentUrl || null,
            previewUrl: null,
            onFileChange(e){
                const f = e.target.files?.[0];
                if(!f){ this.previewUrl=null; return; }
                this.previewUrl = URL.createObjectURL(f);
            },
            clearFile(){
                const input = document.querySelector('input[name="image"]');
                if(input){ input.value=''; }
                this.previewUrl = null;
            },
            removeCurrent(ev){
                if(ev.target.checked){
                    this.currentUrl = null;
                }
            }
        }
    }

    $(function(){
        const $form = $('#categoryForm');
        const $btn = $('#categoryBtnForm');

        $btn.on('click', function(e){
            e.preventDefault();

            let formData = new FormData($form[0]);

            $.ajax({
                url: $form.attr('action'),
                method: $form.attr('method') || 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                beforeSend: function(){
                    $btn.prop('disabled', true).addClass('opacity-50');
                },
                success: function(response){
                    if(response.success){
                        alert(response.message || 'Успіх');

                        if($form.data('mode') === 'create' && response.category){
                            let link = "{{ route('shops.category.edit', ['section' => 'categories', 'category' => 'category_id']) }}";
                            link = link.replace('category_id', response.category.id);
                            window.location.href = link;
                            return;
                        }

                        if($form.data('mode') === 'edit' && response.category){
                            for(const [key,val] of Object.entries(response.category)){
                                $form.find(`[name="${key}"]`).val(val);
                            }
                        }

                    } else {
                        alert(response.message || 'Помилка');
                    }
                },
                error: function(xhr){
                    let msg = 'Помилка AJAX';
                    if(xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    alert(msg);
                },
                complete: function(){
                    $btn.prop('disabled', false).removeClass('opacity-50');
                }
            });
        });
    });
</script>
