<div class="table-responsive"><table class="table ops-breakdown"><tbody>
    <tr><td>Tiền phòng kỳ cuối ({{ $breakdown['rent_days'] }} ngày)</td><td class="text-end">{{ number_format($breakdown['invoice']['room_fee'], 0, ',', '.') }} đ</td></tr>
    @foreach(['electricity_fee' => 'Điện chốt', 'water_fee' => 'Nước chốt', 'service_fee' => 'Dịch vụ, hư hỏng và phụ phí'] as $key => $label)<tr><td>{{ $label }}</td><td class="text-end text-nowrap">{{ number_format($breakdown['invoice'][$key], 0, ',', '.') }} đ</td></tr>@endforeach
    <tr><th>Tổng phát sinh kỳ cuối</th><th class="text-end text-nowrap">{{ number_format($breakdown['invoice']['total'], 0, ',', '.') }} đ</th></tr>
    <tr><td>Công nợ trước quyết toán</td><td class="text-end">{{ number_format($breakdown['old_debt'], 0, ',', '.') }} đ</td></tr>
    <tr><td>Cọc đang giữ</td><td class="text-end">{{ number_format($breakdown['deposit_held'], 0, ',', '.') }} đ</td></tr>
    <tr><td>Cọc bù công nợ</td><td class="text-end">{{ number_format($breakdown['deposit_applied'], 0, ',', '.') }} đ</td></tr>
</tbody></table></div>
@if($breakdown['already_billed'])<p class="text-muted">Kỳ cuối đã có hóa đơn tiền phòng; quyết toán chỉ thu thêm điện nước và phí phát sinh chưa lập.</p>@endif
