<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MatchResource;
use App\Services\MatchService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MatchController extends Controller
{
    public function __construct(private readonly MatchService $service) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $matches = $this->service->findMatches(
            $request->user('web'),
            (int) $request->integer('limit', 20),
            $request->string('trait')->toString() ?: null,
        );

        return MatchResource::collection(collect($matches));
    }
}
