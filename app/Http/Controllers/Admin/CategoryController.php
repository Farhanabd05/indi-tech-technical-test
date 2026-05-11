<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Models\Ticket;

class CategoryController extends Controller
{
    public function destroy(Category $category)
    {
        // Periksa apakah ada tiket yang terkait dengan kategori ini
        if (Ticket::where('category_id', $category->id)->exists()) {
            // Jika ada tiket yang terkait, tolak penghapusan dan arahkan kembali dengan pesan galat
            return redirect()->route('admin.categories.index')
                             ->with('error', 'Tidak dapat menghapus kategori ini karena masih ada tiket yang terkait.');
        }

        // Jika tidak ada tiket yang terkait, hapus kategori tersebut
        $category->delete();

        // Arahkan kembali ke halaman indeks dengan pesan sukses
        return redirect()->route('admin.categories.index')
                         ->with('success', 'Kategori berhasil dihapus.');
    }

    public function index()
    {
        $categories = Category::paginate(10);
        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.create'); // Tampilkan formulir untuk membuat kategori baru
    }

    public function store(StoreCategoryRequest $request)
    {
        // Data sudah tervalidasi oleh StoreCategoryRequest
        $validated = $request->validated();

        // Buat kategori baru menggunakan data yang sudah tervalidasi
        Category::create($validated);

        // Redirect kembali ke halaman index kategori dengan pesan sukses
        return redirect()->route('admin.categories.index')
                         ->with('success', 'Kategori berhasil dibuat.');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category')); // Tampilkan formulir untuk mengedit kategori yang sudah ada
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        // Data sudah tervalidasi oleh UpdateCategoryRequest
        $validated = $request->validated();

        // Perbarui kategori yang sudah ada menggunakan data yang sudah tervalidasi
        $category->update($validated);

        // Redirect kembali ke halaman index kategori dengan pesan sukses
        return redirect()->route('admin.categories.index')
                         ->with('success', 'Kategori berhasil diperbarui.');
    }
}
