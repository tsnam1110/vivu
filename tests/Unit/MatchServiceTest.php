<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\MatchService;
use PHPUnit\Framework\TestCase;

class MatchServiceTest extends TestCase
{
    public function test_jaccard_identical_sets(): void
    {
        $service = new MatchService;
        $this->assertSame(1.0, $service->jaccard(['a', 'b'], ['b', 'a']));
    }

    public function test_jaccard_disjoint_sets(): void
    {
        $service = new MatchService;
        $this->assertSame(0.0, $service->jaccard(['a'], ['b']));
    }

    public function test_jaccard_empty(): void
    {
        $service = new MatchService;
        $this->assertSame(0.0, $service->jaccard([], []));
    }

    public function test_city_bonus_increases_score(): void
    {
        $service = new MatchService;
        $without = $service->score(['a'], ['a'], ['b'], ['b'], 'Da Nang', 'Ha Noi');
        $with = $service->score(['a'], ['a'], ['b'], ['b'], 'Da Nang', 'da nang');
        $this->assertGreaterThan($without, $with);
    }
}
