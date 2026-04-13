<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hợp Đồng Thuê Nhà - {{ $contract->id }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 13px;
            line-height: 1.6;
            color: #1a1a1a;
            background: #fff;
        }
        .page { padding: 25mm 20mm 20mm 25mm; }

        /* Header Quốc Huy */
        .header { text-align: center; margin-bottom: 20px; }
        .header .country { font-size: 13px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
        .header .slogan { font-size: 13px; font-weight: bold; }
        .header .slogan-underline {
            border-bottom: 1px solid #000;
            display: inline-block;
            padding-bottom: 2px;
        }
        .header .title {
            font-size: 20px;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 20px;
            letter-spacing: 2px;
        }
        .header .subtitle {
            font-size: 13px;
            font-style: italic;
            margin-top: 4px;
        }
        .header .contract-no { font-size: 13px; margin-top: 6px; }

        /* Phần nội dung */
        .section { margin: 16px 0; }
        .section-title {
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
            background: #f0f0f0;
            padding: 5px 10px;
            border-left: 4px solid #2c5282;
            margin-bottom: 10px;
        }
        .party-block { margin: 10px 0 10px 15px; }
        .party-name { font-weight: bold; font-size: 13px; text-transform: uppercase; }
        .info-row { display: flex; margin: 4px 0; }
        .info-label { min-width: 160px; font-weight: bold; }
        .info-value { flex: 1; }

        /* Điều khoản */
        .article { margin: 12px 0; }
        .article-title { font-weight: bold; text-decoration: underline; margin-bottom: 6px; }
        .article-content { margin-left: 15px; }
        .article-content p { margin: 4px 0; }
        .highlight-price {
            font-size: 16px;
            font-weight: bold;
            color: #1a365d;
        }

        /* Ký tên */
        .signature-section {
            margin-top: 40px;
            display: table;
            width: 100%;
        }
        .sig-col {
            display: table-cell;
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 0 10px;
        }
        .sig-title { font-weight: bold; margin-bottom: 4px; }
        .sig-note { font-size: 11px; font-style: italic; color: #555; margin-bottom: 70px; }
        .sig-name { font-weight: bold; border-top: 1px solid #333; padding-top: 6px; display: inline-block; min-width: 150px; margin-top: 5px; }

        .divider { border: none; border-top: 1px solid #ccc; margin: 16px 0; }
        .text-center { text-align: center; }
        .mt-10 { margin-top: 10px; }
        .bold { font-weight: bold; }
        .italic { font-style: italic; }
    </style>
</head>
<body>
<div class="page">

    <!-- Quốc Huy -->
    <div class="header">
        <div class="country">Cộng Hòa Xã Hội Chủ Nghĩa Việt Nam</div>
        <div class="slogan"><span class="slogan-underline">Độc lập - Tự do - Hạnh phúc</span></div>
        <div class="slogan-underline" style="display:block; text-align:center; width: 180px; margin: 4px auto 0 auto; border: none;">
            <span style="font-size:11px; font-style:italic;">
                {{ $contract->room->house->address ?? '' }}, ngày {{ \Carbon\Carbon::parse($contract->created_at)->format('d') }} tháng {{ \Carbon\Carbon::parse($contract->created_at)->format('m') }} năm {{ \Carbon\Carbon::parse($contract->created_at)->format('Y') }}
            </span>
        </div>
        <div class="title">Hợp Đồng Thuê Nhà</div>
        <div class="subtitle">Số: HĐ-{{ str_pad($contract->id, 4, '0', STR_PAD_LEFT) }}/{{ \Carbon\Carbon::parse($contract->start_date)->format('Y') }}</div>
    </div>

    <hr class="divider">

    <div class="section-title">Căn cứ Pháp lý</div>
    <div style="margin-left:15px;">
        <p>- Bộ luật Dân sự nước Cộng hòa Xã hội Chủ nghĩa Việt Nam năm 2015;</p>
        <p>- Luật Nhà ở năm 2014 và các văn bản pháp luật có liên quan;</p>
        <p>- Nhu cầu và thỏa thuận của các bên.</p>
    </div>
    <p class="mt-10" style="margin-left:5px;">Hôm nay, hai bên gồm:</p>

    <!-- Bên Cho thuê (A) -->
    <div class="section">
        <div class="section-title">Bên Cho Thuê (Bên A)</div>
        <div class="party-block">
            <div class="party-name">Chủ Nhà Trọ / Đại diện Quản lý</div>
            <div class="info-row mt-10">
                <span class="info-label">Địa chỉ nhà trọ:</span>
                <span class="info-value bold">{{ $contract->room->house->name ?? 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Địa chỉ:</span>
                <span class="info-value">{{ $contract->room->house->address ?? 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Số điện thoại LH:</span>
                <span class="info-value">{{ $contract->room->house->phone ?? 'N/A' }}</span>
            </div>
        </div>
    </div>

    <!-- Bên Thuê (B) -->
    <div class="section">
        <div class="section-title">Bên Thuê (Bên B)</div>
        <div class="party-block">
            <div class="party-name">{{ $contract->tenant->user->name ?? 'N/A' }}</div>
            <div class="info-row mt-10">
                <span class="info-label">Email:</span>
                <span class="info-value">{{ $contract->tenant->user->email ?? 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Số CCCD/CMND:</span>
                <span class="info-value bold">{{ $contract->tenant->cccd ?? 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Số điện thoại:</span>
                <span class="info-value">{{ $contract->tenant->phone ?? 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Địa chỉ thường trú:</span>
                <span class="info-value">{{ $contract->tenant->hometown ?? 'N/A' }}</span>
            </div>
        </div>
    </div>

    <p style="margin: 10px 5px;">Hai bên cùng thỏa thuận ký kết hợp đồng thuê nhà với các điều khoản sau đây:</p>

    <!-- Điều 1 -->
    <div class="article">
        <div class="article-title">Điều 1: Đối Tượng Hợp Đồng</div>
        <div class="article-content">
            <p>Bên A đồng ý cho Bên B thuê phòng như sau:</p>
            <div class="info-row mt-10">
                <span class="info-label">Phòng số:</span>
                <span class="info-value bold">{{ $contract->room->name ?? 'N/A' }} - {{ $contract->room->house->name ?? 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Địa chỉ:</span>
                <span class="info-value">{{ $contract->room->house->address ?? 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Diện tích:</span>
                <span class="info-value">{{ $contract->room->area ?? 'N/A' }} m²</span>
            </div>
            <div class="info-row">
                <span class="info-label">Số người ở:</span>
                <span class="info-value">{{ $contract->occupants }} người</span>
            </div>
        </div>
    </div>

    <!-- Điều 2 -->
    <div class="article">
        <div class="article-title">Điều 2: Thời Hạn Thuê</div>
        <div class="article-content">
            <div class="info-row">
                <span class="info-label">Bắt đầu từ:</span>
                <span class="info-value bold">{{ \Carbon\Carbon::parse($contract->start_date)->format('d/m/Y') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Đến ngày:</span>
                <span class="info-value bold">{{ \Carbon\Carbon::parse($contract->end_date)->format('d/m/Y') }}</span>
            </div>
            <p class="mt-10">Sau khi hết thời hạn, nếu hai bên không có thông báo chấm dứt hợp đồng, hợp đồng sẽ tự động gia hạn thêm 01 tháng.</p>
        </div>
    </div>

    <!-- Điều 3 -->
    <div class="article">
        <div class="article-title">Điều 3: Giá Thuê và Tiền Cọc</div>
        <div class="article-content">
            <div class="info-row">
                <span class="info-label">Giá thuê hàng tháng:</span>
                <span class="info-value highlight-price">{{ number_format($contract->monthly_price, 0, ',', '.') }} đồng/tháng</span>
            </div>
            <div class="info-row mt-10">
                <span class="info-label">Tiền cọc:</span>
                <span class="info-value bold">{{ number_format($contract->deposit, 0, ',', '.') }} đồng</span>
            </div>
            <p class="mt-10 italic">Tiền thuê được thanh toán vào <span class="bold">ngày 05 hàng tháng</span>. Bên A sẽ xuất hóa đơn thu tiền trên hệ thống quản lý.</p>
        </div>
    </div>

    <!-- Điều 4 -->
    <div class="article">
        <div class="article-title">Điều 4: Trách Nhiệm và Quyền Lợi Các Bên</div>
        <div class="article-content">
            <p><span class="bold">4.1. Bên A có trách nhiệm:</span></p>
            <p>- Bàn giao phòng đúng tình trạng đã cam kết, đảm bảo điện nước đầy đủ;</p>
            <p>- Hỗ trợ sửa chữa hư hỏng do tự nhiên (trừ hư hỏng do Bên B gây ra);</p>
            <p>- Không tăng giá thuê đột ngột trong thời hạn hợp đồng.</p>

            <p class="mt-10"><span class="bold">4.2. Bên B có trách nhiệm:</span></p>
            <p>- Thanh toán tiền thuê đúng hạn;</p>
            <p>- Không tự ý sửa chữa, cải tạo phòng khi chưa được Bên A đồng ý;</p>
            <p>- Giữ gìn vệ sinh chung, không gây ồn ào ảnh hưởng người xung quanh;</p>
            <p>- Thông báo kịp thời cho Bên A khi có hư hỏng xảy ra;</p>
            <p>- Trả phòng nguyên vẹn khi kết thúc hợp đồng.</p>
        </div>
    </div>

    <!-- Điều 5 -->
    <div class="article">
        <div class="article-title">Điều 5: Ghi Chú và Điều Khoản Bổ Sung</div>
        <div class="article-content">
            @if($contract->notes)
                <p>{{ $contract->notes }}</p>
            @else
                <p>Hai bên cam kết thực hiện đúng các điều khoản đã ghi trong hợp đồng. Mọi tranh chấp phát sinh sẽ được giải quyết trên tinh thần thương lượng hòa giải.</p>
            @endif
        </div>
    </div>

    <hr class="divider">
    <p class="text-center italic" style="font-size:12px;">Hợp đồng được lập thành 02 bản có giá trị như nhau, mỗi bên giữ 01 bản.</p>

    <!-- Chữ ký -->
    <div class="signature-section">
        <div class="sig-col">
            <div class="sig-title">BÊN CHO THUÊ (BÊN A)</div>
            <div class="sig-note">(Ký, ghi rõ họ tên)</div>
            <div><span class="sig-name">Chủ Nhà Trọ</span></div>
        </div>
        <div class="sig-col">
            <div class="sig-title">BÊN THUÊ (BÊN B)</div>
            <div class="sig-note">(Ký, ghi rõ họ tên)</div>
            <div><span class="sig-name">{{ $contract->tenant->user->name ?? '' }}</span></div>
        </div>
    </div>

</div>
</body>
</html>
