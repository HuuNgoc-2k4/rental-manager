<?php

namespace App\Providers;

use App\Repositories\ContractDocumentRepository;
use App\Repositories\ContractRepository;
use App\Repositories\ContractTenantRepository;
use App\Repositories\Interfaces\ContractDocumentRepositoryInterface;
use App\Repositories\Interfaces\ContractRepositoryInterface;
use App\Repositories\Interfaces\ContractTenantRepositoryInterface;
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
        TenantRepositoryInterface::class => TenantRepository::class,
        ContractRepositoryInterface::class => ContractRepository::class,
        ContractTenantRepositoryInterface::class => ContractTenantRepository::class,
        ContractDocumentRepositoryInterface::class => ContractDocumentRepository::class
    ];

    public function register(): void
    {
        foreach ($this->repositories as $interface => $implementation) {
            $this->app->bind($interface, $implementation);
        }
    }
}
