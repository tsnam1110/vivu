<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Experience;
use App\Services\CommentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class CommentController extends Controller
{
    public function __construct(private readonly CommentService $service) {}

    public function index(Request $request, Experience $experience): AnonymousResourceCollection
    {
        $perPage = (int) $request->integer('per_page', 15);

        return CommentResource::collection(
            $this->service->listForExperience($experience, $perPage)
        );
    }

    public function store(StoreCommentRequest $request, Experience $experience): JsonResponse
    {
        $this->authorize('create', Comment::class);

        $comment = $this->service->create(
            $experience,
            $request->user('web'),
            $request->validated(),
        );

        return (new CommentResource($comment))
            ->response()
            ->setStatusCode(201);
    }

    public function destroy(Comment $comment): Response
    {
        $this->authorize('delete', $comment);
        $this->service->delete($comment);

        return response()->noContent();
    }
}
