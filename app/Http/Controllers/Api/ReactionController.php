<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\ReactionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreReactionRequest;
use App\Models\Experience;
use App\Services\ReactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ReactionController extends Controller
{
    public function __construct(private readonly ReactionService $service) {}

    public function store(StoreReactionRequest $request, Experience $experience): JsonResponse
    {
        $type = ReactionType::from($request->validated('type'));
        $data = $this->service->react($experience, $request->user('web'), $type);

        return response()->json(['data' => $data]);
    }

    public function destroy(Experience $experience): JsonResponse|Response
    {
        $user = request()->user('web');
        if (! $user) {
            abort(401);
        }

        $data = $this->service->remove($experience, $user);

        return response()->json(['data' => $data]);
    }
}
