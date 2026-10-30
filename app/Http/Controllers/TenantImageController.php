<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Services\Manage\TenantService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TenantImageController extends Controller
{
    /**
     * @param TenantService $tenantService
     */
    /**
     * @param TenantService $tenantService
     */
    public function __construct(protected TenantService $tenantService) {}

    /**
     * Trả ảnh CCCD từ disk mặc định (private, local hoặc S3), chỉ cho người có quyền xem hồ sơ (không có quyền → 404 để không lộ hồ sơ tồn tại).
     *
     * @param Tenant $tenant
     * @param string $side front|back
     * @return StreamedResponse
     */
    public function show(Tenant $tenant, string $side)
    {
        abort_unless($this->tenantService->canViewIdImage(Auth::user(), $tenant), 404);

        $path = $this->tenantService->idImagePath($tenant, $side);
        abort_if($path === null, 404);

        // response() stream được cả disk local lẫn S3 (S3 không có đường dẫn local để response()->file()).
        return Storage::disk()->response($path, null, [
            'Cache-Control' => 'private, no-store'
        ]);
    }
}
