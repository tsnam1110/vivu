<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_application_returns_a_successful_response(): void
    {
        $this->withoutVite();

        // Guest landing (kho cá nhân là mặc định khi đã đăng nhập)
        $this->get('/')->assertStatus(200);
        $this->get('/explore')->assertStatus(200);

        // Văn bản pháp lý công khai
        $this->get('/terms')->assertOk()->assertSee('Điều khoản sử dụng', false);
        $this->get('/privacy')->assertOk()->assertSee('bảo vệ dữ liệu cá nhân', false);
        $this->get('/community')->assertOk()->assertSee('Quy tắc cộng đồng', false);
        $this->get('/cookies')->assertOk()->assertSee('Cookie', false);
    }
}
