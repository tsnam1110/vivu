<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAvatarFrameRequest;
use App\Http\Requests\Admin\UpdateAvatarFrameRequest;
use App\Http\Resources\AvatarFrameResource;
use App\Models\AvatarFrame;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AvatarFrameController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return AvatarFrameResource::collection(
            AvatarFrame::query()->orderBy('sort_order')->orderBy('id')->get()
        );
    }

    public function store(StoreAvatarFrameRequest $request): JsonResponse
    {
        $data = $request->validated();
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $frame = AvatarFrame::query()->create($data);

        return (new AvatarFrameResource($frame))->response()->setStatusCode(201);
    }

    public function update(UpdateAvatarFrameRequest $request, AvatarFrame $avatarFrame): AvatarFrameResource
    {
        $avatarFrame->update($request->validated());

        return new AvatarFrameResource($avatarFrame->fresh());
    }

    public function destroy(AvatarFrame $avatarFrame): Response
    {
        if ($avatarFrame->users()->exists()) {
            throw ValidationException::withMessages([
                'frame' => ['Không xoá được: còn user đang dùng khung này. Hãy tắt is_active thay vì xoá.'],
            ]);
        }

        $avatarFrame->delete();

        return response()->noContent();
    }
}
