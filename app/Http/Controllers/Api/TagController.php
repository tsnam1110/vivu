<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TagResource;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TagController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Tag::query()->approved()->orderByDesc('usage_count');

        if ($request->filled('category')) {
            $category = Category::query()->where('slug', $request->string('category'))->first();
            if ($category) {
                $query->where(function ($q) use ($category) {
                    $q->where('category_id', $category->id)->orWhereNull('category_id');
                });
            }
        }

        if ($request->filled('category_id')) {
            $id = $request->integer('category_id');
            $query->where(function ($q) use ($id) {
                $q->where('category_id', $id)->orWhereNull('category_id');
            });
        }

        return TagResource::collection($query->limit(100)->get());
    }
}
