<?php

namespace App\Http\Controllers\Manage;

use App\Exceptions\BusinessException;
use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\ContractDocument;
use App\Services\Manage\ContractDocumentService;
use App\Validators\Manage\ContractDocumentValidator;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class ContractDocumentController extends Controller
{
    private const FIELDS = [
        'code', 'signed_date', 'signed_place', 'start_date', 'end_date', 'landlord', 'tenant', 'members', 'property_address',
        'room_name', 'room_area', 'max_occupants', 'equipment', 'rent', 'deposit', 'electric_price', 'water_price', 'services',
        'payment_day', 'payment_method', 'extra_terms'
    ];

    /**
     * @param ContractDocumentService $documentService
     * @param ContractDocumentValidator $documentValidator
     */
    public function __construct(
        protected ContractDocumentService $documentService,
        protected ContractDocumentValidator $documentValidator
    ) {}

    /**
     * Lập văn bản tự động rồi mở trang sửa.
     *
     * @param Contract $contract
     * @return RedirectResponse
     */
    public function store(Contract $contract)
    {
        // 1. Gọi Service xử lý
        $result = $this->documentService->generate($contract, Auth::user());

        // 2. Phản hồi
        if (!$result['status']) {
            return back()->with('error', $result['message']);
        }

        return redirect()->route('manage.contracts.documents.edit', $result['document'])->with('success', $result['message']);
    }

    /**
     * @param ContractDocument $document
     * @return View
     */
    public function show(ContractDocument $document)
    {
        return view('manage.contracts.show', compact('document'));
    }

    /**
     * @param ContractDocument $document
     * @return View|RedirectResponse
     */
    public function edit(ContractDocument $document)
    {
        if ($document->isApproved()) {
            return redirect()->route('manage.contracts.documents.show', $document)->with('error', 'Hợp đồng đã được chủ hộ duyệt, không sửa được nữa.');
        }

        return view('manage.contracts.edit', compact('document'));
    }

    /**
     * @param Request $request
     * @param ContractDocument $document
     * @return RedirectResponse
     */
    public function update(Request $request, ContractDocument $document)
    {
        $data = $request->only(self::FIELDS);

        // 1. Validate dữ liệu
        $validator = $this->documentValidator->updateValidator($data);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // 2. Gọi Service xử lý
        $result = $this->documentService->update($document, $data);

        // 3. Phản hồi
        if (!$result['status']) {
            return back()->with('error', $result['message'])->withInput();
        }

        return redirect()->route('manage.contracts.documents.show', $document)->with('success', $result['message']);
    }

    /**
     * @param ContractDocument $document
     * @return RedirectResponse
     */
    public function refresh(ContractDocument $document)
    {
        // 1. Gọi Service xử lý và phản hồi
        return $this->respond($this->documentService->refreshFromHousehold($document));
    }

    /**
     * @param ContractDocument $document
     * @return RedirectResponse
     */
    public function submit(ContractDocument $document)
    {
        // 1. Gọi Service xử lý và phản hồi
        return $this->respond($this->documentService->submit($document));
    }

    /**
     * @param ContractDocument $document
     * @return Response|RedirectResponse
     */
    public function pdf(ContractDocument $document)
    {
        try {
            $file = $this->documentService->exportPdf($document);
        } catch (BusinessException $e) {
            return back()->with('error', $e->getMessage());
        }

        return response($file['content'], 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$file['file_name'].'"'
        ]);
    }

    /**
     * @param ContractDocument $document
     * @return Response|RedirectResponse
     */
    public function word(ContractDocument $document)
    {
        try {
            $file = $this->documentService->exportWord($document);
        } catch (BusinessException $e) {
            return back()->with('error', $e->getMessage());
        }

        return response($file['content'], 200, [
            'Content-Type' => 'application/msword; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="'.$file['file_name'].'"'
        ]);
    }

    /**
     * @param array $result
     * @return RedirectResponse
     */
    private function respond(array $result): RedirectResponse
    {
        return back()->with($result['status'] ? 'success' : 'error', $result['message']);
    }
}
