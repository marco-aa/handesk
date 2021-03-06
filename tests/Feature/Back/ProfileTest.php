<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ProfileTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @var User
     */
    protected $user;

    protected function setUp()
    {
        parent::setUp();

        $this->actingAs($this->user = factory(User::class)->create());
    }

    /** @test */
    public function can_see_profile()
    {
        $response = $this->get('profile');

        $response->assertStatus(Response::HTTP_OK);
        $response->assertSee($this->user->name);
    }

    /** @test */
    public function can_update_profile()
    {
        $name = 'Sexy boy';
        $response = $this->put('profile', ['name' => $name, 'locale' => 'en']);

        $response->assertStatus(Response::HTTP_FOUND);
        $this->assertEquals('Sexy boy', $this->user->fresh()->name);
    }

    /** @test */
    public function can_update_password()
    {
        $response = $this->post('password', [
            'old' => 'secret',
            'password' => 'hello',
            'password_confirmation' => 'hello',
        ]);

        $response->assertStatus(Response::HTTP_FOUND);
        $this->assertTrue(Hash::check('hello', $this->user->fresh()->password));
    }

    /** @test */
    public function can_not_update_password_if_old_is_wrong()
    {
        $response = $this->post('password', [
            'old' => 'wrong_old_password',
            'password' => 'hello',
            'password_confirmation' => 'hello',
        ]);

        $response->assertStatus(Response::HTTP_FOUND);
        $this->assertTrue(Hash::check('secret', $this->user->fresh()->password));
    }

    /** @test */
    public function can_not_update_password_if_new_not_confirmed()
    {
        $response = $this->post('password', [
            'old' => 'secret',
            'password' => 'hello',
            'password_confirmation' => 'wrong_confirmation',
        ]);

        $response->assertStatus(Response::HTTP_FOUND);
        $this->assertTrue(Hash::check('secret', $this->user->fresh()->password));
    }

    /**
     * @test
     * @dataProvider toggableNotifications
     */
    public function can_toggle_notification($notification)
    {
        $response = $this->put(route('profile.update'), [
            'name' => 'Sexy boy',
            'locale' => 'en',
            $notification => true
        ]);

        $response->assertStatus(Response::HTTP_FOUND);
        $this->assertTrue(!!$this->user->fresh()->settings->{$notification});
    }

    public function toggableNotifications()
    {
        return [
            ['new_ticket_notification'],
            ['ticket_assigned_notification'],
            ['ticket_updated_notification'],
            ['new_lead_notification'],
            ['lead_assigned_notification'],
            ['daily_tasks_notification']
        ];
    }
}