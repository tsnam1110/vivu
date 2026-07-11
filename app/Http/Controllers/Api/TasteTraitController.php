<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TasteTraitResource;
use App\Models\TasteTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TasteTraitController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = TasteTrait::query()->active()->orderBy('name');

        if ($request->filled('type')) {
            $query->where('type', $request->string('type'));
        }

        return TasteTraitResource::collection($query->get());
    }
}
