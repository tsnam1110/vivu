<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateProfileRequest;
use App\Http\Requests\Web\EnablePremiumAvatarRequest;
use App\Http\Requests\Web\UpdateAccountProfileRequest;
use App\Http\Requests\Web\UpdatePasswordRequest;
use App\Models\AvatarFrame;
use App\Models\SampleAvatar;
use App\Models\TasteTrait;
use App\Models\User;
use App\Services\ProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(private readonly ProfileService $profileService) {}

    /**
     * Trang Profile của chính mình (auth).
     */
    public function me(Request $request): View
    {
        $user = $request->user('web')->load(['profile', 'avatarFrame', 'sampleAvatar']);
        $stats = [
            'experiences' => $user->experiences()->count(),
            'published' => $user->experiences()->published()->count(),
        ];

        $frames = AvatarFrame::cachedActive();

        return view('profile.me', [
            'user' => $user,
            'stats' => $stats,
            'freeFrames' => $frames->where('is_premium', false)->values(),
            'premiumFrames' => $frames->where('is_premium', true)->values(),
            'sampleAvatars' => SampleAvatar::cachedActive(),
        ]);
    }

    public function updateAccount(UpdateAccountProfileRequest $request): RedirectResponse
    {
        $this->profileService->updateAccount($request->user('web'), $request->validated());

        return redirect()
            ->route('profile.me')
            ->with('success', __('messages.account_profile_updated'));
    }

    public function enablePremiumAvatar(EnablePremiumAvatarRequest $request): RedirectResponse
    {
        $this->profileService->enablePremiumDemo($request->user('web'), 30);

        return redirect()
            ->route('profile.me')
            ->with('success', __('messages.premium_avatar_enabled'));
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $this->profileService->updatePassword(
            $request->user('web'),
            $request->validated('password'),
        );

        return redirect()
            ->route('profile.me')
            ->with('success', __('messages.password_updated'));
    }

    public function show(string $username): View
    {
        $user = User::query()
            ->where('username', $username)
            ->where('status', 'active')
            ->with(['profile', 'avatarFrame', 'sampleAvatar'])
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
