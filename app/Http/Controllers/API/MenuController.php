<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Menu;
use Illuminate\Support\Str;

class MenuController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validate the incoming structure from our Vue frontend wizard
        $validated = $request->validate([
            'restaurant_id' => 'required|integer',
            'title' => 'required|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string|max:255',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.description' => 'nullable|string',
        ]);

        // 2. Generate a clean URL slug for the QR code display lookup
        $slug = Str::slug($validated['title']) . '-' . rand(1000, 9999);

        // 3. Create the parent Menu entry records
        $menu = Menu::create([
            'restaurant_id' => $validated['restaurant_id'],
            'title' => $validated['title'],
            'slug' => $slug,
            'active' => true,
        ]);

        // 4. Save each extracted row item into your dishes database table
        foreach ($validated['items'] as $item) {
            $menu->items()->create([
                'name' => $item['name'],
                'price' => $item['price'],
                'description' => $item['description'] ?? '',
            ]);
        }

        // 5. Return the success payloads back to Vue to display the QR target
        return response()->json([
            'success' => true,
            'message' => 'Digital menu structure generated successfully!',
            'slug' => $slug,
            'live_url' => url("/menu/{$slug}")
        ], 201);
    }
}