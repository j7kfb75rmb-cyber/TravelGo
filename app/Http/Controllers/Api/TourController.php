<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TourResource;
use App\Models\Tour;
use Illuminate\Http\Request;

class TourController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'search'         => ['nullable', 'string', 'max:100'],
            'destination_id' => ['nullable', 'integer'],
            'category'       => ['nullable', 'string'],
            'min_price'      => ['nullable', 'numeric', 'min:0'],
            'max_price'      => ['nullable', 'numeric', 'min:0'],
            'sort'           => ['nullable', 'in:price,-price,start_date,-start_date'],
            'per_page'       => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $query = Tour::query()
            ->with(['destination', 'categories'])
            ->withAvg('reviews', 'rating')
            ->where('is_active', true);

        if ($s = $request->search) {
            $query->where(function ($q) use ($s) {
                $q->where('title', 'like', "%{$s}%")
                  ->orWhereHas('destination', fn ($d) => $d
                      ->where('city', 'like', "%{$s}%")
                      ->orWhere('country', 'like', "%{$s}%"));
            });
        }

        if ($request->filled('destination_id')) {
            $query->where('destination_id', $request->destination_id);
        }

        if ($request->filled('category')) {
            $query->whereHas('categories', fn ($c) => $c->where('slug', $request->category));
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        if ($request->filled('sort')) {
            $desc = str_starts_with($request->sort, '-');
            $query->orderBy(ltrim($request->sort, '-'), $desc ? 'desc' : 'asc');
        } else {
            $query->latest();
        }

        return TourResource::collection($query->paginate($request->integer('per_page', 10)));
    }

    public function show(Tour $tour)
    {
        abort_unless($tour->is_active, 404);

        $tour->load(['destination', 'categories'])->loadAvg('reviews', 'rating');

        return new TourResource($tour);
    }
}