<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Room;
use App\Services\Manage\RoomService;
use App\Validators\Manage\RoomValidator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    protected RoomService $roomService;

    protected RoomValidator $roomValidator;

    /**
     * @param RoomService $roomService
     * @param RoomValidator $roomValidator
     */
    public function __construct(RoomService $roomService, RoomValidator $roomValidator)
    {
        $this->roomService = $roomService;
        $this->roomValidator = $roomValidator;
    }

    /**
     * @param Request $request
     * @param Property $property
     * @return RedirectResponse
     */
    public function store(Request $request, Property $property)
    {
        $data = $request->only([
            'name', 'floor', 'area', 'max_occupants', 'base_rent', 'note',
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
        $data = $request->only(['name', 'floor', 'area', 'max_occupants', 'base_rent', 'note']);

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

        // 2. Phản hồi
        return back()->with($result['status'] ? 'success' : 'error', $result['message']);
    }
}
