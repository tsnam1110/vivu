<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\ExperienceStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateExperienceStatusRequest;
use App\Http\Resources\ExperienceResource;
use App\Models\Experience;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ExperienceController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Experience::query()
            ->with(['category', 'tags', 'user', 'media'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('q')) {
            $q = $request->string('q');
            $query->where(function ($builder) use ($q) {
                $builder->where('title', 'like', "%{$q}%")
                    ->orWhere('place_name', 'like', "%{$q}%");
            });
        }

        return ExperienceResource::collection(
            $query->paginate(min((int) $request->integer('per_page', 15), 50))
        );
    }

    public function update(UpdateExperienceStatusRequest $request, Experience $experience): ExperienceResource
    {
        $status = ExperienceStatus::from($request->validated('status'));
        $payload = ['status' => $status];
        if ($status === ExperienceStatus::Published && ! $experience->published_at) {
            $payload['published_at'] = now();
        }
        $experience->update($payload);

        return new ExperienceResource($experience->fresh(['category', 'tags', 'user', 'media']));
    }
}
