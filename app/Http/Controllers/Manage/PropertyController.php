<?php

namespace App\Http\Controllers\Manage;

use App\Enums\Property\RoomStatus;
use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Services\Manage\PropertyService;
use App\Services\Manage\RoomService;
use App\Services\Manage\TenantService;
use App\Validators\Manage\PropertyValidator;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    protected PropertyService $propertyService;

    protected RoomService $roomService;

    protected PropertyValidator $propertyValidator;

    protected TenantService $tenantService;

    /**
     * @param PropertyService $propertyService
     * @param RoomService $roomService
     * @param PropertyValidator $propertyValidator
     * @param TenantService $tenantService
     */
    public function __construct(PropertyService $propertyService, RoomService $roomService, PropertyValidator $propertyValidator, TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
        $this->propertyService = $propertyService;
        $this->roomService = $roomService;
        $this->propertyValidator = $propertyValidator;
    }

    /**
     * @return View
     */
    public function index()
    {
        $properties = $this->propertyService->getList();

        return view('manage.properties.index', compact('properties'));
    }

    /**
     * Sơ đồ phòng của một khu.
     *
     * @param Property $property
     * @return View
     */
    public function show(Property $property)
    {
        $rooms = $this->roomService->getForMap($property);
        $floors = $rooms->groupBy(fn ($room) => $room->floor ?? 'none')->sortKeys();
        $statuses = RoomStatus::cases();
        $availableTenants = $this->tenantService->getAvailable();

        return view('manage.properties.show', compact('property', 'rooms', 'floors', 'statuses', 'availableTenants'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request)
    {
        $data = $request->only(['name', 'address', 'note']);

        // 1. Validate dữ liệu
        $validator = $this->propertyValidator->saveValidator($data);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // 2. Gọi Service xử lý
        $result = $this->propertyService->store($data);

        // 3. Phản hồi
        return back()->with($result['status'] ? 'success' : 'error', $result['message']);
    }

    /**
     * @param Request $request
     * @param Property $property
     * @return RedirectResponse
     */
    public function update(Request $request, Property $property)
    {
        $data = $request->only(['name', 'address', 'note']);

        // 1. Validate dữ liệu
        $validator = $this->propertyValidator->saveValidator($data);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // 2. Gọi Service xử lý
        $result = $this->propertyService->update($property, $data);

        // 3. Phản hồi
        return back()->with($result['status'] ? 'success' : 'error', $result['message']);
    }

    /**
     * @param Property $property
     * @return RedirectResponse
     */
    public function destroy(Property $property)
    {
        // 1. Gọi Service xử lý
        $result = $this->propertyService->delete($property);

        if (!$result['status']) {
            return back()->with('error', $result['message']);
        }

        // 2. Phản hồi
        return redirect()->route('manage.properties.index')->with('success', $result['message']);
    }
}
