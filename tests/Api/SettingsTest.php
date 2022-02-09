<?php

namespace EscolaLms\MailerLite\Tests\Api;

use EscolaLms\Auth\Database\Seeders\AuthPermissionSeeder;
use EscolaLms\Auth\Events\AccountBlocked;
use EscolaLms\Auth\Events\AccountConfirmed;
use EscolaLms\Auth\Models\User;
use EscolaLms\Core\Tests\ApiTestTrait;
use EscolaLms\Core\Tests\CreatesUsers;
use EscolaLms\MailerLite\Enum\PackageStatusEnum;
use EscolaLms\MailerLite\Providers\SettingsServiceProvider;
use EscolaLms\MailerLite\Services\Contracts\MailerLiteServiceContract;
use EscolaLms\MailerLite\Tests\TestCase;
use EscolaLms\Settings\Database\Seeders\PermissionTableSeeder;
use EscolaLms\Settings\Facades\AdministrableConfig;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Mockery\MockInterface;

class SettingsTest extends TestCase
{
    use CreatesUsers, ApiTestTrait, WithoutMiddleware;

    protected function setUp(): void
    {
        parent::setUp();

        if (!class_exists(\EscolaLms\Auth\EscolaLmsAuthServiceProvider::class)) {
            $this->markTestSkipped('Auth package not installed');
        }

        if (!class_exists(\EscolaLms\Settings\EscolaLmsSettingsServiceProvider::class)) {
            $this->markTestSkipped('Settings package not installed');
        }

        $this->seed(PermissionTableSeeder::class);
        $this->seed(AuthPermissionSeeder::class);
        Config::set('escola_settings.use_database', true);
        $this->user = config('auth.providers.users.model')::factory()->create();
        $this->user->guard_name = 'api';
        $this->user->assignRole('admin');
    }

    protected function tearDown(): void
    {
        \EscolaLms\Settings\Models\Config::truncate();
        User::query()->delete();
    }

    public function testAdministrableConfigApi(): void
    {
        $configKey = SettingsServiceProvider::CONFIG_KEY;

        $this->response = $this->actingAs($this->user, 'api')->postJson(
            '/api/admin/config',
            [
                'config' => [
                    [
                        'key' => "{$configKey}.package_status",
                        'value' => PackageStatusEnum::DISABLED,
                    ],
                    [
                        'key' => "{$configKey}.api_key",
                        'value' => 'new_api_key',
                    ],
                ]
            ]
        )->assertOk();

        $this->response = $this->actingAs($this->user, 'api')->getJson(
            '/api/admin/config'
        )->assertOk();

        $this->response->assertJsonFragment([
            $configKey => [
                'package_status' => [
                    'full_key' => "$configKey.package_status",
                    'key' => 'package_status',
                    'rules' => [
                        'required',
                        'string',
                        'in:' . implode(',', PackageStatusEnum::getValues())
                    ],
                    'public' => false,
                    'value' => PackageStatusEnum::DISABLED,
                    'readonly' => false,
                ],
                'api_key' => [
                    'full_key' => "$configKey.api_key",
                    'key' => 'api_key',
                    'rules' => [
                        'required',
                        'string'
                    ],
                    'public' => false,
                    'value' => 'new_api_key',
                    'readonly' => false,
                ],
            ],
        ]);

        $this->response = $this->getJson(
            '/api/config'
        );

        $this->response->assertJsonMissing([
            'package_status' => PackageStatusEnum::DISABLED,
            'api_key' => 'new_api_key',
        ]);
    }

    public function testAddUserAsSubscriberAfterConfirmingEmail(): void
    {
        Event::fake(AccountConfirmed::class);
        Notification::fake();

        $this->setPackageStatus(PackageStatusEnum::DISABLED);

        $student1 = $this->makeStudent([
            'email_verified_at' => null
        ]);

        $this->mock(MailerLiteServiceContract::class, function (MockInterface $mock) {
            $mock->shouldReceive('addSubscriberToGroup')->never();
        });

        $this->response = $this->actingAs($this->user, 'api')->patchJson('/api/admin/users/' . $student1->getKey(), [
            'email_verified' => true,
        ])->assertOk();

        $this->setPackageStatus(PackageStatusEnum::ENABLED);

        $student2 = $this->makeStudent([
            'email_verified_at' => null
        ]);

        $this->mock(MailerLiteServiceContract::class, function (MockInterface $mock) {
            $mock->shouldReceive('addSubscriberToGroup')->once();
        });

        $this->response = $this->actingAs($this->user, 'api')->patchJson('/api/admin/users/' . $student2->getKey(), [
            'email_verified' => true,
        ])->assertOk();
    }

    public function testDeleteSubscriberAfterBlockingAccount(): void
    {
        Event::fake(AccountBlocked::class);
        Notification::fake();

        $this->setPackageStatus(PackageStatusEnum::DISABLED);

        $student1 = $this->makeStudent([
            'is_active' => true,
        ]);

        $this->mock(MailerLiteServiceContract::class, function (MockInterface $mock) {
            $mock->shouldReceive('deleteSubscriber')->never();
        });

        $this->response = $this->actingAs($this->user, 'api')->putJson('/api/admin/users/' . $student1->getKey(), [
            'first_name' => $student1->first_name,
            'last_name' => $student1->last_name,
            'is_active' => false,
        ])->assertOk();

        $this->setPackageStatus(PackageStatusEnum::ENABLED);

        $student2 = $this->makeStudent([
            'is_active' => true,
        ]);

        $this->mock(MailerLiteServiceContract::class, function (MockInterface $mock) {
            $mock->shouldReceive('deleteSubscriber')->once();
        });

        $this->response = $this->actingAs($this->user, 'api')->putJson('/api/admin/users/' . $student2->getKey(), [
            'first_name' => $student2->first_name,
            'last_name' => $student2->last_name,
            'is_active' => false,
        ])->assertOk();
    }

    private function setPackageStatus($packageStatus): void
    {
        Config::set(SettingsServiceProvider::CONFIG_KEY . '.package_status', $packageStatus);
        Config::set('escola_settings.use_database', true);
        AdministrableConfig::storeConfig();
        $this->refreshApplication();
    }
}
