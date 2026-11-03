{{--
    Toàn văn hợp đồng thuê phòng — dùng chung cho trang xem (admin / hộ thuê), PDF (mPDF) và Word (.doc).
    Chỉ dùng selector class đơn (mPDF hỗ trợ kém selector lồng; trong app class thắng preflight của Tailwind).
    Kích thước theo em, gốc là $fontSize.

    @param \App\Models\ContractDocument $document
    @param string $fontFamily  'freeserif' cho PDF, tên font thật cho Word / trình duyệt
    @param string $fontSize    '13pt' khi in, nhỏ hơn khi xem trên màn hình
--}}
@php
    $blank = '…………………………';
    $date = fn ($value) => $value ? \Illuminate\Support\Carbon::parse($value)->format('d/m/Y') : $blank;
    $text = fn ($value) => filled($value) ? $value : $blank;
    $vnd = fn ($amount) => number_format((int) $amount, 0, ',', '.');
    $signed = $document->signed_date;
    $parties = [
        ['BÊN CHO THUÊ (sau đây gọi là Bên A)', $document->landlord ?? []],
        ['BÊN THUÊ (sau đây gọi là Bên B)', $document->tenant ?? []]
    ];
    $members = $document->members ?? [];
    $services = $document->services ?? [];
    $area = $document->room_area ? rtrim(rtrim(number_format((float) $document->room_area, 2, ',', '.'), '0'), ',') : null;
    // Điều khoản bổ sung chỉ chèn khi có, các điều sau tự đánh số lại.
    $article = 8;
@endphp
<style>
    .cd-root { font-family: {!! $fontFamily !!}; font-size: {{ $fontSize }}; line-height: 1.5; color: #000; }
    .cd-center { text-align: center; margin: 0; }
    .cd-nation { text-align: center; font-weight: bold; margin: 0; }
    .cd-motto { text-align: center; font-weight: bold; margin: 0; text-decoration: underline; }
    .cd-title { text-align: center; font-weight: bold; font-size: 1.25em; margin: 1.2em 0 0; }
    .cd-code { text-align: center; font-style: italic; margin: 0 0 1em; }
    .cd-basis { font-style: italic; margin: 0 0 0.2em; text-align: justify; }
    .cd-p { margin: 0 0 0.35em; text-align: justify; }
    .cd-party { font-weight: bold; margin: 0.8em 0 0.2em; }
    .cd-article { font-weight: bold; margin: 0.9em 0 0.3em; }
    .cd-table { width: 100%; border-collapse: collapse; margin: 0.3em 0 0.6em; }
    /* mPDF không cho bảng thừa hưởng cỡ chữ của khối cha → khai báo lại ở từng ô */
    .cd-th { font-size: {{ $fontSize }}; border: 1px solid #000; padding: 0.25em 0.4em; font-weight: bold; text-align: center; }
    .cd-td { font-size: {{ $fontSize }}; border: 1px solid #000; padding: 0.25em 0.4em; vertical-align: top; }
    .cd-td-num { font-size: {{ $fontSize }}; border: 1px solid #000; padding: 0.25em 0.4em; text-align: right; white-space: nowrap; }
    .cd-sign { width: 100%; border-collapse: collapse; margin-top: 1.5em; }
    .cd-sign-cell { font-size: {{ $fontSize }}; width: 50%; text-align: center; vertical-align: top; padding: 0; }
    .cd-sign-space { height: 6em; }
    .cd-scroll { overflow-x: auto; }
</style>

<div class="cd-root">
    <p class="cd-nation">CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM</p>
    <p class="cd-motto">Độc lập - Tự do - Hạnh phúc</p>

    <p class="cd-title">HỢP ĐỒNG THUÊ PHÒNG Ở</p>
    <p class="cd-code">Số: {{ $document->code }}</p>

    <p class="cd-basis">- Căn cứ Bộ luật Dân sự số 91/2015/QH13 ngày 24 tháng 11 năm 2015;</p>
    <p class="cd-basis">- Căn cứ Luật Nhà ở số 27/2023/QH15 ngày 27 tháng 11 năm 2023;</p>
    <p class="cd-basis">- Căn cứ nhu cầu và khả năng thực tế của các bên.</p>

    <p class="cd-p" style="margin-top: 0.8em;">
        Hôm nay, ngày {{ $signed->format('d') }} tháng {{ $signed->format('m') }} năm {{ $signed->format('Y') }},
        tại {{ $text($document->signed_place) }}, chúng tôi gồm:
    </p>

    @foreach($parties as [$heading, $party])
        <p class="cd-party">{{ $heading }}:</p>
        <p class="cd-p">Ông/Bà: <b>{{ mb_strtoupper($text($party['full_name'] ?? null)) }}</b> &nbsp;&nbsp; Sinh ngày: {{ $date($party['date_of_birth'] ?? null) }}</p>
        <p class="cd-p">CCCD/CMND số: {{ $text($party['id_number'] ?? null) }} &nbsp;&nbsp; Cấp ngày: {{ $date($party['id_issued_date'] ?? null) }} &nbsp;&nbsp; Nơi cấp: {{ $text($party['id_issued_place'] ?? null) }}</p>
        <p class="cd-p">Địa chỉ thường trú: {{ $text($party['permanent_address'] ?? null) }}</p>
        <p class="cd-p">Điện thoại: {{ $text($party['phone'] ?? null) }}</p>
    @endforeach

    <p class="cd-p" style="margin-top: 0.5em;">Những người cùng ở thuộc Bên B:@if(empty($members)) không có.@endif</p>
    @if(!empty($members))
        <div class="cd-scroll">
            <table class="cd-table">
                <tr>
                    <td class="cd-th">STT</td>
                    <td class="cd-th">Họ và tên</td>
                    <td class="cd-th">Ngày sinh</td>
                    <td class="cd-th">CCCD/CMND</td>
                    <td class="cd-th">Điện thoại</td>
                </tr>
                @foreach($members as $index => $member)
                    <tr>
                        <td class="cd-td" style="text-align: center;">{{ $index + 1 }}</td>
                        <td class="cd-td">{{ $member['full_name'] ?? '' }}</td>
                        <td class="cd-td">{{ !empty($member['date_of_birth']) ? $date($member['date_of_birth']) : '' }}</td>
                        <td class="cd-td">{{ $member['id_number'] ?? '' }}</td>
                        <td class="cd-td">{{ $member['phone'] ?? '' }}</td>
                    </tr>
                @endforeach
            </table>
        </div>
    @endif

    <p class="cd-p">Sau khi bàn bạc, hai bên thống nhất ký kết hợp đồng thuê phòng ở với các điều khoản sau:</p>

    <p class="cd-article">Điều 1. Đối tượng của hợp đồng</p>
    <p class="cd-p">1.1. Bên A đồng ý cho Bên B thuê phòng số <b>{{ $document->room_name }}</b> tại địa chỉ {{ $document->property_address }}{{ $area ? ', diện tích sử dụng khoảng '.$area.' m²' : '' }}, để ở.</p>
    <p class="cd-p">1.2. Số người ở tối đa: {{ $document->max_occupants }} người. Bên B không được cho thêm người ở khi chưa được Bên A đồng ý.</p>
    @php $items = $document->equipment_items ?? []; @endphp
    @if($items)
        <p class="cd-p">1.3. Trang thiết bị bàn giao kèm phòng, theo hiện trạng ghi dưới đây{{ filled($document->equipment) ? '; ghi chú thêm: '.$document->equipment : '' }}:</p>
        <div class="cd-scroll">
            <table class="cd-table">
                <tr>
                    <td class="cd-th">STT</td>
                    <td class="cd-th">Tên thiết bị</td>
                    <td class="cd-th">Số lượng</td>
                    <td class="cd-th">Hiện trạng</td>
                    <td class="cd-th">Ghi chú</td>
                </tr>
                @foreach($items as $index => $item)
                    <tr>
                        <td class="cd-td" style="text-align: center;">{{ $index + 1 }}</td>
                        <td class="cd-td">{{ $item['name'] ?? '' }}</td>
                        <td class="cd-td" style="text-align: center;">{{ $item['quantity'] ?? 1 }}</td>
                        <td class="cd-td">{{ \App\Enums\Property\FurnitureCondition::tryFrom($item['condition'] ?? '')?->label() }}</td>
                        <td class="cd-td">{{ $item['note'] ?? '' }}</td>
                    </tr>
                @endforeach
            </table>
        </div>
    @else
        <p class="cd-p">1.3. Trang thiết bị bàn giao kèm phòng: {{ filled($document->equipment) ? $document->equipment : 'theo hiện trạng thực tế khi bàn giao, có biên bản kèm theo (nếu có).' }}</p>
    @endif

    <p class="cd-article">Điều 2. Thời hạn thuê</p>
    <p class="cd-p">
        2.1. Thời hạn thuê tính từ ngày {{ $date($document->start_date) }}
        {{ $document->end_date ? 'đến ngày '.$date($document->end_date).'.' : '; hợp đồng không xác định thời hạn, chấm dứt theo Điều 7.' }}
    </p>
    <p class="cd-p">2.2. Khi hết hạn, nếu Bên B có nhu cầu thuê tiếp thì báo cho Bên A trước ít nhất 30 ngày để hai bên thỏa thuận gia hạn.</p>

    <p class="cd-article">Điều 3. Giá thuê, tiền đặt cọc và phương thức thanh toán</p>
    <p class="cd-p">3.1. Giá thuê phòng: <b>{{ $vnd($document->rent) }} đồng/tháng</b> (Bằng chữ: {{ \App\Models\ContractDocument::amountInWords((int) $document->rent) }}). Giá thuê chưa gồm chi phí dịch vụ tại Điều 4 và được giữ nguyên trong thời hạn hợp đồng, trừ khi hai bên có thỏa thuận khác bằng văn bản.</p>
    <p class="cd-p">3.2. Tiền đặt cọc: <b>{{ $vnd($document->deposit) }} đồng</b> (Bằng chữ: {{ \App\Models\ContractDocument::amountInWords((int) $document->deposit) }}), Bên B giao cho Bên A khi ký hợp đồng. Tiền đặt cọc không dùng để trừ vào tiền thuê hằng tháng.</p>
    <p class="cd-p">3.3. Kỳ thanh toán: hằng tháng. Bên B thanh toán tiền thuê và chi phí dịch vụ của tháng trước từ ngày 01 đến hết ngày {{ str_pad((string) $document->payment_day, 2, '0', STR_PAD_LEFT) }} của tháng.</p>
    <p class="cd-p">3.4. Phương thức thanh toán: {{ $text($document->payment_method) }}.</p>

    <p class="cd-article">Điều 4. Chi phí dịch vụ</p>
    <p class="cd-p">4.1. Ngoài tiền thuê, Bên B thanh toán các chi phí sau:</p>
    <div class="cd-scroll">
        <table class="cd-table">
            <tr>
                <td class="cd-th">STT</td>
                <td class="cd-th">Khoản chi phí</td>
                <td class="cd-th">Đơn giá (đồng)</td>
                <td class="cd-th">Đơn vị tính</td>
            </tr>
            <tr>
                <td class="cd-td" style="text-align: center;">1</td>
                <td class="cd-td">Điện sinh hoạt</td>
                <td class="cd-td-num">{{ $vnd($document->electric_price) }}</td>
                <td class="cd-td">kWh, theo chỉ số công tơ</td>
            </tr>
            <tr>
                <td class="cd-td" style="text-align: center;">2</td>
                <td class="cd-td">Nước sinh hoạt</td>
                <td class="cd-td-num">{{ $vnd($document->water_price) }}</td>
                <td class="cd-td">m³, theo chỉ số đồng hồ</td>
            </tr>
            @foreach($services as $index => $service)
                <tr>
                    <td class="cd-td" style="text-align: center;">{{ $index + 3 }}</td>
                    <td class="cd-td">{{ $service['name'] ?? '' }}</td>
                    <td class="cd-td-num">{{ $vnd($service['amount'] ?? 0) }}</td>
                    <td class="cd-td">{{ $service['unit'] ?? '' }}</td>
                </tr>
            @endforeach
        </table>
    </div>
    <p class="cd-p">4.2. Chỉ số điện, nước được chốt vào cuối mỗi tháng, có ảnh chụp đồng hồ làm căn cứ; Bên B được xem và đối chiếu trước khi thanh toán.</p>
    <p class="cd-p">4.3. Khi Nhà nước điều chỉnh giá điện, nước, Bên A được điều chỉnh đơn giá tương ứng và phải thông báo cho Bên B trước kỳ thanh toán áp dụng.</p>

    <p class="cd-article">Điều 5. Quyền và nghĩa vụ của Bên A</p>
    <p class="cd-p">5.1. Bàn giao phòng và trang thiết bị cho Bên B đúng thời hạn, đúng hiện trạng đã thỏa thuận.</p>
    <p class="cd-p">5.2. Bảo đảm cho Bên B sử dụng ổn định phòng trong thời hạn thuê; sửa chữa kịp thời các hư hỏng về kết cấu, điện, nước không do lỗi của Bên B.</p>
    <p class="cd-p">5.3. Đăng ký hoặc hỗ trợ Bên B và những người cùng ở đăng ký tạm trú theo quy định của pháp luật về cư trú.</p>
    <p class="cd-p">5.4. Thu tiền thuê và chi phí dịch vụ đúng thỏa thuận; mỗi kỳ có hóa đơn ghi rõ từng khoản.</p>
    <p class="cd-p">5.5. Hoàn trả tiền đặt cọc cho Bên B theo Điều 8.</p>
    <p class="cd-p">5.6. Được đơn phương chấm dứt hợp đồng trong các trường hợp nêu tại khoản 7.3 Điều 7.</p>

    <p class="cd-article">Điều 6. Quyền và nghĩa vụ của Bên B</p>
    <p class="cd-p">6.1. Sử dụng phòng đúng mục đích để ở; giữ gìn phòng và trang thiết bị, bồi thường nếu làm hư hỏng, mất mát.</p>
    <p class="cd-p">6.2. Thanh toán đầy đủ, đúng hạn tiền thuê và chi phí dịch vụ.</p>
    <p class="cd-p">6.3. Chấp hành nội quy khu nhà, quy định về phòng cháy chữa cháy, an ninh trật tự và vệ sinh môi trường; không tàng trữ, sử dụng chất cấm, chất dễ cháy nổ.</p>
    <p class="cd-p">6.4. Cung cấp đầy đủ, trung thực thông tin cá nhân của Bên B và những người cùng ở để đăng ký tạm trú.</p>
    <p class="cd-p">6.5. Không tự ý sửa chữa, cải tạo phòng; không cho thuê lại hoặc chuyển nhượng hợp đồng khi chưa có sự đồng ý bằng văn bản của Bên A.</p>
    <p class="cd-p">6.6. Khi chấm dứt hợp đồng, trả lại phòng và trang thiết bị theo hiện trạng lúc nhận (trừ hao mòn tự nhiên).</p>

    <p class="cd-article">Điều 7. Chấm dứt hợp đồng</p>
    <p class="cd-p">7.1. Hợp đồng chấm dứt khi hết thời hạn mà không gia hạn, hoặc khi hai bên thỏa thuận chấm dứt.</p>
    <p class="cd-p">7.2. Bên muốn chấm dứt hợp đồng trước thời hạn phải thông báo cho bên kia trước ít nhất 30 ngày.</p>
    <p class="cd-p">7.3. Bên A được đơn phương chấm dứt hợp đồng khi Bên B: không trả tiền thuê từ 03 tháng trở lên mà không có lý do chính đáng; sử dụng phòng sai mục đích; tự ý cải tạo, cho thuê lại phòng; hoặc vi phạm nghiêm trọng quy định về an ninh trật tự, phòng cháy chữa cháy.</p>
    <p class="cd-p">7.4. Bên B tự ý chấm dứt hợp đồng trước thời hạn mà không thông báo theo khoản 7.2 thì không được hoàn trả tiền đặt cọc. Bên A đơn phương chấm dứt trái với khoản 7.3 thì phải hoàn trả tiền đặt cọc và bồi thường cho Bên B một khoản bằng tiền đặt cọc.</p>

    <p class="cd-article">Điều 8. Hoàn trả tiền đặt cọc</p>
    <p class="cd-p">Khi hợp đồng chấm dứt đúng quy định, trong vòng 07 ngày kể từ ngày bàn giao lại phòng, Bên A hoàn trả tiền đặt cọc cho Bên B sau khi khấu trừ tiền thuê, chi phí dịch vụ còn nợ và chi phí khắc phục hư hỏng (nếu có) đã được hai bên xác nhận.</p>

    @if(filled($document->extra_terms))
        <p class="cd-article">Điều {{ ++$article }}. Thỏa thuận khác</p>
        @foreach(preg_split('/\R/', trim($document->extra_terms)) as $line)
            @if(trim($line) !== '')
                <p class="cd-p">{{ $line }}</p>
            @endif
        @endforeach
    @endif

    <p class="cd-article">Điều {{ ++$article }}. Giải quyết tranh chấp</p>
    <p class="cd-p">Tranh chấp phát sinh được giải quyết trước hết bằng thương lượng, hòa giải. Không thương lượng được thì một trong hai bên có quyền yêu cầu Tòa án nhân dân có thẩm quyền giải quyết.</p>

    <p class="cd-article">Điều {{ ++$article }}. Điều khoản chung</p>
    <p class="cd-p">Hai bên cam kết thực hiện đúng các điều khoản đã thỏa thuận. Mọi sửa đổi, bổ sung phải được hai bên đồng ý bằng văn bản. Hợp đồng có hiệu lực từ ngày ký, được lập thành 02 bản có giá trị pháp lý như nhau, mỗi bên giữ 01 bản.</p>
    @if($document->approved_at)
        <p class="cd-p"><i>Bên B đã xác nhận đồng ý nội dung hợp đồng trên hệ thống quản lý nhà cho thuê lúc {{ $document->approved_at->format('H:i \n\g\à\y d/m/Y') }}.</i></p>
    @endif

    <table class="cd-sign">
        <tr>
            <td class="cd-sign-cell"><b>BÊN A</b><br><i>(Ký, ghi rõ họ tên)</i></td>
            <td class="cd-sign-cell"><b>BÊN B</b><br><i>(Ký, ghi rõ họ tên)</i></td>
        </tr>
        <tr>
            <td class="cd-sign-cell cd-sign-space"></td>
            <td class="cd-sign-cell cd-sign-space"></td>
        </tr>
        <tr>
            <td class="cd-sign-cell"><b>{{ $document->landlord['full_name'] ?? '' }}</b></td>
            <td class="cd-sign-cell"><b>{{ $document->tenant['full_name'] ?? '' }}</b></td>
        </tr>
    </table>
</div>
