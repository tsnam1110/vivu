<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\MatchService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MatchController extends Controller
{
    public function __construct(private readonly MatchService $service) {}

    public function __invoke(Request $request): View
    {
        $matches = $this->service->findMatches($request->user('web'));

        return view('matches.index', compact('matches'));
    }
}
