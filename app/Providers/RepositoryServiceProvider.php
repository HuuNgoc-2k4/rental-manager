<?php

namespace App\Providers;

use App\Repositories\Interfaces\PropertyRepositoryInterface;
use App\Repositories\Interfaces\RoomMeterRepositoryInterface;
use App\Repositories\Interfaces\RoomRepositoryInterface;
use App\Repositories\Interfaces\TenantRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\PropertyRepository;
use App\Repositories\RoomMeterRepository;
use App\Repositories\RoomRepository;
use App\Repositories\TenantRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Danh sách binding Interface → Implementation
     */
    protected array $repositories = [
        UserRepositoryInterface::class => UserRepository::class,
        PropertyRepositoryInterface::class => PropertyRepository::class,
        RoomRepositoryInterface::class => RoomRepository::class,
        RoomMeterRepositoryInterface::class => RoomMeterRepository::class,
        TenantRepositoryInterface::class => TenantRepository::class
    ];

    public function register(): void
    {
        foreach ($this->repositories as $interface => $implementation) {
            $this->app->bind($interface, $implementation);
        }
    }
}
