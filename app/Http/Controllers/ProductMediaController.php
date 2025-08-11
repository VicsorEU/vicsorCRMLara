<?php

namespace App\Http\Controllers;

use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\File;

class ProductMediaController extends Controller
{
    public function upload(Request $request)
    {
        $data = $request->validate([
            'file' => ['required', File::image()->max('5mb')],
        ]);

        $path = $data['file']->store('products/'.date('Y/m'), 'public');

        $img = ProductImage::create([
            'path'          => $path,
            'is_primary'    => false,
            'sort_order'    => 0,
            'session_token' => $request->session()->token(),
        ]);

        return response()->json([
            'id'  => $img->id,
            'url' => asset('storage/'.$img->path),
        ]);
    }

    public function destroy(ProductImage $image)
    {
        Storage::disk('public')->delete($image->path);
        $image->delete();
        return response()->noContent();
    }
}
