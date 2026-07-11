<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreExperienceRequest;
use App\Http\Requests\Api\UpdateExperienceRequest;
use App\Http\Resources\ExperienceResource;
use App\Models\Experience;
use App\Services\ExperienceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ExperienceController extends Controller
{
    public function __construct(private readonly ExperienceService $service) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $paginator = $this->service->listPublished($request->all());

        return ExperienceResource::collection($paginator);
    }

    public function show(string $slug): ExperienceResource
    {
        $experience = Experience::query()
            ->with(['category', 'tags', 'media', 'user.profile'])
            ->where('slug', $slug)
            ->firstOrFail();

        $this->authorize('view', $experience);
        $this->service->incrementView($experience);

        return new ExperienceResource($experience);
    }

    public function store(StoreExperienceRequest $request): JsonResponse
    {
        $this->authorize('create', Experience::class);

        $experience = $this->service->create(
            $request->user('web'),
            $request->validated(),
            $request->input('tags'),
            $request->file('images'),
            $request->input('new_tags'),
        );

        return (new ExperienceResource($experience))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateExperienceRequest $request, Experience $experience): ExperienceResource
    {
        $experience = $this->service->update(
            $experience,
            $request->validated(),
            $request->has('tags') ? $request->input('tags') : null,
            $request->input('new_tags'),
        );

        return new ExperienceResource($experience);
    }

    public function destroy(Experience $experience): Response
    {
        $this->authorize('delete', $experience);
        $this->service->delete($experience);

        return response()->noContent();
    }
}
