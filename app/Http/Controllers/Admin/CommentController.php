<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\CommentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateCommentStatusRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Services\CommentService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CommentController extends Controller
{
    public function __construct(private readonly CommentService $service) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Comment::query()->with(['user', 'experience'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        return CommentResource::collection(
            $query->paginate(min((int) $request->integer('per_page', 15), 50))
        );
    }

    public function update(UpdateCommentStatusRequest $request, Comment $comment): CommentResource
    {
        $comment = $this->service->updateStatus(
            $comment,
            CommentStatus::from($request->validated('status')),
        );

        if ($comment->rating !== null) {
            $this->service->recalculateRating($comment->experience);
        }

        return new CommentResource($comment);
    }
}
