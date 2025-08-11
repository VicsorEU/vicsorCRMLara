<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'is_variable'   => ['sometimes','boolean'],

            'name'          => ['required','string','max:255'],
            'slug'          => ['required','string','max:255','unique:products,slug'],
            'sku'           => ['nullable','string','max:255','unique:products,sku'],
            'barcode'       => ['nullable','string','max:255'],

            'price_regular' => ['required','numeric','min:0'],
            'price_sale'    => ['nullable','numeric','min:0'],
            'weight'        => ['nullable','numeric','min:0'],
            'length'        => ['nullable','numeric','min:0'],
            'width'         => ['nullable','numeric','min:0'],
            'height'        => ['nullable','numeric','min:0'],

            'short_description' => ['nullable','string'],
            'description'       => ['nullable','string'],

            // изображения
            'images'               => ['array'],
            'images.*.id'          => ['required','integer'],
            'images.*.is_primary'  => ['nullable'],

            // пары атрибутов простого товара
            'attr_pairs'                 => ['array'],
            'attr_pairs.*.attribute_id'  => ['required','integer','exists:attributes,id'],
            'attr_pairs.*.value_id'      => ['required','integer','exists:attribute_values,id'],

            // вариации
            'variations'                            => ['array'],
            'variations.*.sku'                      => ['nullable','string','max:255'],
            'variations.*.barcode'                  => ['nullable','string','max:255'],
            'variations.*.price_regular'            => ['required_with:variations','numeric','min:0'],
            'variations.*.price_sale'               => ['nullable','numeric','min:0'],
            'variations.*.weight'                   => ['nullable','numeric','min:0'],
            'variations.*.length'                   => ['nullable','numeric','min:0'],
            'variations.*.width'                    => ['nullable','numeric','min:0'],
            'variations.*.height'                   => ['nullable','numeric','min:0'],
            'variations.*.description'              => ['nullable','string'],
            'variations.*.image_id'                 => ['nullable','integer','exists:product_images,id'],
            'variations.*.pairs'                    => ['array'],
            'variations.*.pairs.*.attribute_id'     => ['required','integer','exists:attributes,id'],
            'variations.*.pairs.*.value_id'         => ['required','integer','exists:attribute_values,id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_variable' => filter_var($this->input('is_variable'), FILTER_VALIDATE_BOOLEAN),
        ]);
    }
}
