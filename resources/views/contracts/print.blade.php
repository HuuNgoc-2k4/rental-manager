{{--
    Trang in đứng riêng (không layout, không Tailwind) cho mPDF và Word.
    Word mở HTML đặt đuôi .doc: namespace + @page để mở ở chế độ Print Layout, khổ A4.

    @param \App\Models\ContractDocument $document
    @param string $fontFamily
    @param bool $forWord
--}}
<!DOCTYPE html>
<html lang="vi" @if($forWord) xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns="http://www.w3.org/TR/REC-html40" @endif>
<head>
    <meta charset="UTF-8">
    <title>Hợp đồng thuê phòng {{ $document->code }}</title>
    @if($forWord)
        <!--[if gte mso 9]><xml><w:WordDocument><w:View>Print</w:View><w:Zoom>100</w:Zoom></w:WordDocument></xml><![endif]-->
        <style>
            @page WordSection { size: 21cm 29.7cm; margin: 2cm 2cm 2cm 3cm; }
            div.WordSection { page: WordSection; }
        </style>
    @endif
</head>
<body>
    <div class="WordSection">
        @include('contracts.document', ['document' => $document, 'fontFamily' => $fontFamily, 'fontSize' => '13pt'])
    </div>
</body>
</html>
