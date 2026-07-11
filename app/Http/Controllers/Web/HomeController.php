<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\User;
use App\Services\ExperienceService;
use App\Services\HabitService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(
        private readonly ExperienceService $service,
        private readonly HabitService $habitService,
    ) {}

    /**
     * Trang mặc định: kho cá nhân (đã đăng nhập) hoặc landing (khách).
     */
    public function index(Request $request): View
    {
        $user = $request->user('web');

        if ($user instanceof User) {
            return $this->myVault($user);
        }

        return view('home.guest');
    }

    /**
     * Khám phá trải nghiệm công khai.
     */
    public function explore(Request $request): View
    {
        $experiences = $this->service->listPublished([
            'per_page' => 12,
            'category' => $request->query('category'),
            'q' => $request->query('q'),
            'lat' => $request->query('lat'),
            'lng' => $request->query('lng'),
            'radius_km' => $request->query('radius_km'),
            'sort' => $request->query('sort', '-published_at'),
        ]);

        $categories = Category::query()->active()->orderBy('sort_order')->get();

        return view('home.explore', compact('experiences', 'categories'));
    }

    private function myVault(User $user): View
    {
        $experiences = $user->experiences()
            ->with(['category', 'media'])
            ->latest('updated_at')
            ->paginate(12);

        $this->habitService->ensureStarterItems($user);
        $habitSummary = $this->habitService->currentMonthSummary($user);
        $habitCharts = $this->habitService->overviewCharts($user);

        return view('home.me', [
            'user' => $user->load('profile'),
            'experiences' => $experiences,
            'habitSummary' => $habitSummary,
            'habitCharts' => $habitCharts,
        ]);
    }
}
