<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class DashboardBroadcastTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_post_and_display_broadcast_alert(): void
    {
        Cache::forget('qms_active_broadcast');

        $user = User::factory()->create([
            'first_name' => 'Dr. Elena',
            'last_name' => 'Vasiliev',
        ]);

        $response = $this->actingAs($user)->post('/dashboard/broadcast', [
            'message' => 'Emergency power drill scheduled for 15:00 in Ward B.',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertTrue(Cache::has('qms_active_broadcast'));

        $cached = Cache::get('qms_active_broadcast');
        $this->assertEquals('Emergency power drill scheduled for 15:00 in Ward B.', $cached['message']);
        $this->assertEquals('Dr. Elena Vasiliev', $cached['sender']);

        // Check that it renders on the dashboard
        $dashResponse = $this->get('/dashboard');
        $dashResponse->assertStatus(200);
        $dashResponse->assertSee('Emergency power drill scheduled for 15:00 in Ward B.');
        $dashResponse->assertSee('Dr. Elena Vasiliev');
    }

    public function test_can_dismiss_broadcast_alert(): void
    {
        Cache::put('qms_active_broadcast', [
            'message' => 'Test alert to be dismissed',
            'sender' => 'Admin Station',
            'sent_at' => '12:00',
        ]);

        $response = $this->delete('/dashboard/broadcast');
        $response->assertRedirect(route('dashboard'));

        $this->assertFalse(Cache::has('qms_active_broadcast'));

        $dashResponse = $this->get('/dashboard');
        $dashResponse->assertDontSee('Test alert to be dismissed');
    }
}
