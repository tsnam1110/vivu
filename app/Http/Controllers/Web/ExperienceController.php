<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Enums\ExperienceStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreExperienceRequest;
use App\Http\Requests\Api\UpdateExperienceRequest;
use App\Models\Category;
use App\Models\Experience;
use App\Models\Tag;
use App\Services\CommentService;
use App\Services\ExperienceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExperienceController extends Controller
{
    public function __construct(
        private readonly ExperienceService $service,
        private readonly CommentService $commentService,
    ) {}

    public function show(string $slug): View
    {
        $experience = Experience::query()
            ->with(['category', 'tags', 'media', 'user.profile'])
            ->where('slug', $slug)
            ->firstOrFail();

        $this->authorize('view', $experience);

        if ($experience->isPublished()) {
            $this->service->incrementView($experience);
        }

        $comments = $this->commentService->listForExperience($experience, 20);

        return view('experiences.show', compact('experience', 'comments'));
    }

    public function create(): View
    {
        $this->authorize('create', Experience::class);

        return view('experiences.create', [
            'categories' => Category::query()->active()->orderBy('sort_order')->get(),
            'tags' => Tag::query()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreExperienceRequest $request): RedirectResponse
    {
        $this->authorize('create', Experience::class);

        $data = $request->validated();
        if (! isset($data['status'])) {
            $data['status'] = ExperienceStatus::Published->value;
        }

        $experience = $this->service->create(
            $request->user('web'),
            $data,
            $request->input('tags'),
            $request->file('images'),
        );

        return redirect()->route('experiences.show', $experience->slug)
            ->with('success', __('messages.experience_created'));
    }

    public function edit(Experience $experience): View
    {
        $this->authorize('update', $experience);

        return view('experiences.edit', [
            'experience' => $experience->load(['tags', 'media']),
            'categories' => Category::query()->active()->orderBy('sort_order')->get(),
            'tags' => Tag::query()->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateExperienceRequest $request, Experience $experience): RedirectResponse
    {
        $experience = $this->service->update(
            $experience,
            $request->validated(),
            $request->has('tags') ? $request->input('tags') : null,
        );

        return redirect()->route('experiences.show', $experience->slug)
            ->with('success', __('messages.experience_updated'));
    }

    public function destroy(Experience $experience): RedirectResponse
    {
        $this->authorize('delete', $experience);
        $this->service->delete($experience);

        return redirect()->route('home')->with('success', __('messages.experience_deleted'));
    }
}
