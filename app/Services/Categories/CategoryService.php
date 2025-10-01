<?php

namespace App\Services\Categories;

use App\Http\Requests\Category\StoreRequest;

use App\Http\Requests\Category\UpdateRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CategoryService implements CategoryInterface
{
    /**
     * @param StoreRequest $request
     *
     * @return array
     */
    public function store(StoreRequest $request): array
    {
        $data = $request->validated();

        try {
            $imagePath = null;

            // Зберігаємо файл, якщо передано
            if ($request->hasFile('image')) {
                $imagePath = $data['image']->store('categories', 'public');
            }

            // Створюємо категорію
            $category = Category::create([
                'name'        => $data['name'],
                'slug'        => $data['slug'],
                'description' => $data['description'] ?? null,
                'parent_id'   => $data['parent_id'] ?? null,
                'image_path'  => $imagePath,
            ]);

            return [
                'success'  => true,
                'message'  => 'Категория успешно создана',
                'category' => $category,
            ];
        } catch (\Exception $e) {
            // Логування помилки
            Log::error('Ошибка создания категории: ' . $e->getMessage(), [
                'data' => $data,
            ]);

            // Видаляємо завантажений файл у разі помилки
            if (!empty($imagePath) && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }

            return [
                'success' => false,
                'message' => 'Ошибка при создании категории',
                'error'   => $e->getMessage(),
            ];
        }
    }

    /**
     * @param Category $category
     * @param Request $request
     *
     * @return array|null
     */
    public function edit(Category $category, Request $request): array
    {
        try {
            $section = $request->query('section', 'categories');

            if ($section !== 'categories') {
                return [
                    'success' => false,
                    'message' => 'Неверная секция',
                ];
            }

            $parents = Category::where('id', '!=', $category->id)
                ->orderBy('name')
                ->get(['id', 'name']);

            return [
                'success' => true,
                'section' => $section,
                'category' => $category,
                'parents' => $parents,
            ];
        } catch (\Exception $e) {
            Log::error('Ошибка получения категории для редактирования: ' . $e->getMessage(), [
                'category_id' => $category->id,
            ]);

            return [
                'success' => false,
                'message' => 'Ошибка при получении данных категории',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * @param UpdateRequest $request
     * @param Category $category
     *
     * @return Category
     */
    public function update(Category $category, UpdateRequest $request): array
    {
        $data = $request->validated();

        try {
            // Удаление изображения по флажку
            if (
                $request->boolean('remove_image')
                && $category->image_path
                && Storage::disk('public')->exists($category->image_path)
            ) {
                Storage::disk('public')->delete($category->image_path);
                $category->image_path = null;
            }

            // замена изображения
            if ($request->hasFile('image')) {
                if ($category->image_path && Storage::disk('public')->exists($category->image_path)) {
                    Storage::disk('public')->delete($category->image_path);
                }
                $category->image_path = $request->file('image')->store('categories', 'public');
            }

            $category->fill([
                'name' => $data['name'],
                'slug' => $data['slug'],
                'description' => $data['description'] ?? null,
                'parent_id' => $data['parent_id'] ?? null,
            ])->save();

            return [
                'success' => true,
                'message' => 'Категория успешно обновлена',
                'category' => $category,
            ];
        } catch (\Exception $e) {
            Log::error('Ошибка обновления категории: ' . $e->getMessage(), [
                'category_id' => $category->id,
                'data' => $data,
            ]);

            return [
                'success' => false,
                'message' => 'Ошибка при обновлении категории',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * @param Category $category
     *
     * @return array
     */
    public function destroy(Category $category): array
    {
        try {
            if ($category->image_path && Storage::disk('public')->exists($category->image_path)) {
                Storage::disk('public')->delete($category->image_path);
            }

            $category->delete();

            return [
                'success' => true,
                'message' => 'Категория успешно удалена',
            ];
        } catch (\Exception $e) {
            Log::error('Ошибка удаления категории: ' . $e->getMessage(), [
                'category_id' => $category->id,
            ]);

            return [
                'success' => false,
                'message' => 'Ошибка при удалении категории!',
            ];
        }
    }
}
