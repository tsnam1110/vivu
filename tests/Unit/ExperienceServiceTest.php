<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\ExperienceService;
use PHPUnit\Framework\TestCase;

class ExperienceServiceTest extends TestCase
{
    public function test_haversine_same_point_is_zero(): void
    {
        $service = new ExperienceService;
        $this->assertEqualsWithDelta(0.0, $service->haversineKm(16.0, 108.0, 16.0, 108.0), 0.0001);
    }

    public function test_haversine_known_distance_roughly(): void
    {
        $service = new ExperienceService;
        // approx 1 degree latitude ~ 111 km
        $km = $service->haversineKm(16.0, 108.0, 17.0, 108.0);
        $this->assertGreaterThan(100, $km);
        $this->assertLessThan(120, $km);
    }
}
