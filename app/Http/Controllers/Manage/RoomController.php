<?php

namespace App\Http\Controllers\Manage;

use App\Enums\Property\RoomStatus;
use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Room;
use App\Services\Manage\PropertyService;
use App\Services\Manage\RoomService;
use App\Services\Manage\TenantService;
use App\Validators\Manage\RoomValidator;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    protected RoomService $roomService;

    protected RoomValidator $roomValidator;

    protected PropertyService $propertyService;

    protected TenantService $tenantService;

    /**
     * @param RoomService $roomService
     * @param RoomValidator $roomValidator
     * @param PropertyService $propertyService
     * @param TenantService $tenantService
     */
    public function __construct(RoomService $roomService, RoomValidator $roomValidator, PropertyService $propertyService, TenantService $tenantService)
    {
        $this->roomService = $roomService;
        $this->roomValidator = $roomValidator;
        $this->propertyService = $propertyService;
        $this->tenantService = $tenantService;
    }

    /**
     * Danh sách mọi phòng, gom theo khu.
     *
     * @return View
     */
    public function index()
    {
        $properties = $this->propertyService->getList();
        $roomsByProperty = $this->roomService->getOverview()->groupBy('property_id');
        $statuses = RoomStatus::cases();

        return view('manage.rooms.index', compact('properties', 'roomsByProperty', 'statuses'));
    }

    /**
     * Chi tiết phòng: thông tin, nội thất, hộ đang thuê, hợp đồng, đồng hồ.
     *
     * @param Room $room
     * @return View
     */
    public function show(Room $room)
    {
        $room = $this->roomService->getDetail($room);
        $availableTenants = $this->tenantService->getAvailable();

        return view('manage.rooms.show', compact('room', 'availableTenants'));
    }

    /**
     * @param Request $request
     * @param Property $property
     * @return RedirectResponse
     */
    public function store(Request $request, Property $property)
    {
        $data = $request->only([
            'name', 'room_type', 'area', 'base_rent', 'note',
            'electric_integer_digits', 'electric_decimal_digits', 'electric_initial_reading',
            'water_integer_digits', 'water_decimal_digits', 'water_initial_reading'
        ]);

        // 1. Validate dữ liệu
        $validator = $this->roomValidator->storeValidator($data, $property);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // 2. Gọi Service xử lý
        $result = $this->roomService->store($property, $data);

        // 3. Phản hồi
        return back()->with($result['status'] ? 'success' : 'error', $result['message']);
    }

    /**
     * @param Request $request
     * @param Room $room
     * @return RedirectResponse
     */
    public function update(Request $request, Room $room)
    {
        $data = $request->only([
            'name', 'room_type', 'area', 'base_rent', 'note',
            'electric_integer_digits', 'electric_decimal_digits', 'electric_initial_reading',
            'water_integer_digits', 'water_decimal_digits', 'water_initial_reading'
        ]);

        // 1. Validate dữ liệu
        $validator = $this->roomValidator->updateValidator($data, $room);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // 2. Gọi Service xử lý
        $result = $this->roomService->update($room, $data);

        // 3. Phản hồi
        return back()->with($result['status'] ? 'success' : 'error', $result['message']);
    }

    /**
     * @param Request $request
     * @param Room $room
     * @return RedirectResponse
     */
    public function updateFurniture(Request $request, Room $room)
    {
        $data = $request->only(['furniture']);

        // 1. Validate dữ liệu
        $validator = $this->roomValidator->furnitureValidator($data);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // 2. Gọi Service xử lý
        $result = $this->roomService->updateFurniture($room, $data['furniture'] ?? []);

        // 3. Phản hồi
        return back()->with($result['status'] ? 'success' : 'error', $result['message']);
    }

    /**
     * @param Request $request
     * @param Room $room
     * @return RedirectResponse
     */
    public function updateStatus(Request $request, Room $room)
    {
        $data = $request->only(['status']);

        // 1. Validate dữ liệu
        $validator = $this->roomValidator->statusValidator($data);
        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        // 2. Gọi Service xử lý
        $result = $this->roomService->updateStatus($room, $data['status']);

        // 3. Phản hồi
        return back()->with($result['status'] ? 'success' : 'error', $result['message']);
    }

    /**
     * @param Request $request
     * @param Room $room
     * @return RedirectResponse
     */
    public function replaceMeter(Request $request, Room $room)
    {
        $data = $request->only(['type', 'integer_digits', 'decimal_digits', 'initial_reading', 'replaced_at']);

        // 1. Validate dữ liệu
        $validator = $this->roomValidator->replaceMeterValidator($data);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // 2. Gọi Service xử lý
        $result = $this->roomService->replaceMeter($room, $data);

        // 3. Phản hồi
        return back()->with($result['status'] ? 'success' : 'error', $result['message']);
    }

    /**
     * @param Room $room
     * @return RedirectResponse
     */
    public function destroy(Room $room)
    {
        // 1. Gọi Service xử lý
        $result = $this->roomService->delete($room);

        if (!$result['status']) {
            return back()->with('error', $result['message']);
        }

        // 2. Phản hồi: trang chi tiết phòng không còn nên về danh sách
        return redirect()->route('manage.rooms.index')->with('success', $result['message']);
    }
}
