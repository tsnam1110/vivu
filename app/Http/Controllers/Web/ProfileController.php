<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateProfileRequest;
use App\Models\TasteTrait;
use App\Models\User;
use App\Services\ProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(private readonly ProfileService $profileService) {}

    public function show(string $username): View
    {
        $user = User::query()
            ->where('username', $username)
            ->where('status', 'active')
            ->with('profile')
            ->firstOrFail();

        $experiences = $user->experiences()
            ->published()
            ->with(['category', 'media'])
            ->latest('published_at')
            ->paginate(12);

        return view('profile.show', compact('user', 'experiences'));
    }

    public function edit(Request $request): View
    {
        $user = $request->user('web')->load('profile');

        return view('profile.edit', [
            'user' => $user,
            'personalities' => TasteTrait::query()->active()->where('type', 'personality')->orderBy('name')->get(),
            'interests' => TasteTrait::query()->active()->where('type', 'interest')->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $this->profileService->updateProfile($request->user('web'), $request->validated());

        return redirect()->route('profile.edit')->with('success', __('messages.profile_updated'));
    }
}
