<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Services\Manage\PropertyService;
use App\Validators\Manage\PropertyValidator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    protected PropertyService $propertyService;

    protected PropertyValidator $propertyValidator;

    /**
     * @param PropertyService $propertyService
     * @param PropertyValidator $propertyValidator
     */
    public function __construct(PropertyService $propertyService, PropertyValidator $propertyValidator)
    {
        $this->propertyService = $propertyService;
        $this->propertyValidator = $propertyValidator;
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
        return redirect()->route('manage.rooms.index')->with('success', $result['message']);
    }
}
