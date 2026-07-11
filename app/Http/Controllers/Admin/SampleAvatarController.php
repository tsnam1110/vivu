<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSampleAvatarRequest;
use App\Http\Requests\Admin\UpdateSampleAvatarRequest;
use App\Http\Resources\SampleAvatarResource;
use App\Models\SampleAvatar;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SampleAvatarController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return SampleAvatarResource::collection(
            SampleAvatar::query()->orderBy('sort_order')->orderBy('id')->get()
        );
    }

    public function store(StoreSampleAvatarRequest $request): JsonResponse
    {
        $data = $request->validated();
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $sample = SampleAvatar::query()->create($data);

        return (new SampleAvatarResource($sample))->response()->setStatusCode(201);
    }

    public function update(UpdateSampleAvatarRequest $request, SampleAvatar $sampleAvatar): SampleAvatarResource
    {
        $sampleAvatar->update($request->validated());

        return new SampleAvatarResource($sampleAvatar->fresh());
    }

    public function destroy(SampleAvatar $sampleAvatar): Response
    {
        if ($sampleAvatar->users()->exists()) {
            throw ValidationException::withMessages([
                'sample' => ['Không xoá được: còn user đang dùng avatar mẫu này. Hãy tắt is_active.'],
            ]);
        }

        $sampleAvatar->delete();

        return response()->noContent();
    }
}
