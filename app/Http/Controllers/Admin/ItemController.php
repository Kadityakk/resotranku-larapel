<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreItemRequest;
use App\Http\Requests\Admin\UpdateItemRequest;
use App\Models\Category;
use App\Models\Item;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ItemController extends Controller
{
    public function index(): View
    {
        return view('admin.items.index', [
            'items' => Item::with('category')->latest()->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.items.create', [
            'categories' => Category::orderBy('cat_name')->get(),
        ]);
    }

    public function store(StoreItemRequest $request): RedirectResponse
    {
        Item::create([
            ...$request->validated(),
            'img' => $this->storeImage($request),
        ]);

        return redirect()
            ->route('admin.items.index')
            ->with('success', 'Menu berhasil ditambahkan.');
    }

    public function show(Item $item): View
    {
        return view('admin.items.show', [
            'item' => $item->load('category'),
        ]);
    }

    public function edit(Item $item): View
    {
        return view('admin.items.edit', [
            'item' => $item,
            'categories' => Category::orderBy('cat_name')->get(),
        ]);
    }

    public function update(UpdateItemRequest $request, Item $item): RedirectResponse
    {
        $attributes = $request->validated();
        $newImage = $this->storeImage($request);

        if ($newImage !== null) {
            $this->deleteImage($item->img);
            $attributes['img'] = $newImage;
        } else {
            unset($attributes['img']);
        }

        $item->update($attributes);

        return redirect()
            ->route('admin.items.index')
            ->with('success', 'Menu berhasil diperbarui.');
    }

    /**
     * Quick availability switch used straight from the menu table, for when an
     * item sells out mid-service.
     */
    public function toggleActive(Item $item): RedirectResponse
    {
        $item->update(['is_active' => ! $item->is_active]);

        return back()->with('success', 'Menu "'.$item->name.'" sekarang '.($item->is_active ? 'tersedia' : 'tidak tersedia').'.');
    }

    /**
     * Menus that already appear on an order are archived instead of erased so
     * historical order totals keep their item names.
     */
    public function destroy(Item $item): RedirectResponse
    {
        if ($item->orderItems()->exists()) {
            $item->update(['is_active' => false]);
            $item->delete();

            return redirect()
                ->route('admin.items.index')
                ->with('success', 'Menu diarsipkan karena sudah pernah dipesan, sehingga riwayat pesanan tetap utuh.');
        }

        $this->deleteImage($item->img);
        $item->forceDelete();

        return redirect()
            ->route('admin.items.index')
            ->with('success', 'Menu berhasil dihapus.');
    }

    /**
     * Uploads are stored under a generated name so a client-supplied filename
     * never reaches the filesystem.
     */
    private function storeImage(StoreItemRequest $request): ?string
    {
        if (! $request->hasFile('img')) {
            return null;
        }

        return $request->file('img')->storeAs(
            'items',
            Str::uuid()->toString().'.'.$request->file('img')->extension(),
            'public',
        );
    }

    private function deleteImage(?string $path): void
    {
        if (filled($path) && ! Str::startsWith($path, ['http://', 'https://'])) {
            Storage::disk('public')->delete($path);
        }
    }
}
