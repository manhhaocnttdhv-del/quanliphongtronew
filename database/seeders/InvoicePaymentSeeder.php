<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InvoicePaymentSeeder extends Seeder
{
    public function run(): void
    {
        $contracts  = DB::table('contracts')->where('status', 'active')->get();
        $electricSvc = DB::table('services')->where('type', 'electricity')->first();
        $waterSvc    = DB::table('services')->where('type', 'water')->first();
        $fixedSvcs   = DB::table('services')->where('type', 'fixed')->get();

        // Tạo 3 tháng lịch sử hóa đơn (tháng trước, 2 tháng trước, 3 tháng trước)
        $months = [
            Carbon::now()->subMonths(3),
            Carbon::now()->subMonths(2),
            Carbon::now()->subMonths(1),
        ];

        foreach ($contracts as $contract) {
            $room = DB::table('rooms')->where('id', $contract->room_id)->first();

            // Chỉ số điện/nước ban đầu ngẫu nhiên
            $elecBase  = rand(100, 300);
            $waterBase = rand(5, 15);

            foreach ($months as $idx => $monthDate) {
                $month = (int) $monthDate->format('m');
                $year  = (int) $monthDate->format('Y');

                // Chỉ số điện
                $elecOld  = $elecBase + ($idx * rand(50, 120));
                $elecNew  = $elecOld + rand(40, 120);
                $elecFee  = ($elecNew - $elecOld) * $electricSvc->price;

                DB::table('meter_readings')->insertOrIgnore([
                    'room_id'    => $contract->room_id,
                    'service_id' => $electricSvc->id,
                    'month'      => $month,
                    'year'       => $year,
                    'old_value'  => $elecOld,
                    'new_value'  => $elecNew,
                    'unit_price' => $electricSvc->price,
                    'total_amount' => $elecFee,
                    'created_at' => $monthDate->copy()->day(rand(1, 5)),
                    'updated_at' => $monthDate->copy()->day(rand(1, 5)),
                ]);

                // Chỉ số nước
                $waterOld = $waterBase + ($idx * rand(2, 5));
                $waterNew = $waterOld + rand(3, 8);
                $waterFee = ($waterNew - $waterOld) * $waterSvc->price;

                DB::table('meter_readings')->insertOrIgnore([
                    'room_id'    => $contract->room_id,
                    'service_id' => $waterSvc->id,
                    'month'      => $month,
                    'year'       => $year,
                    'old_value'  => $waterOld,
                    'new_value'  => $waterNew,
                    'unit_price' => $waterSvc->price,
                    'total_amount' => $waterFee,
                    'created_at' => $monthDate->copy()->day(rand(1, 5)),
                    'updated_at' => $monthDate->copy()->day(rand(1, 5)),
                ]);

                // Phí dịch vụ cố định
                $serviceFee = $fixedSvcs->sum('price');

                // Tổng hóa đơn
                $total = $contract->monthly_price + $elecFee + $waterFee + $serviceFee;

                // Tháng cũ nhất và tháng giữa: đã thanh toán. Tháng gần nhất: chưa TT
                $isPaid  = ($idx < 2);
                $status  = $isPaid ? 'paid' : 'unpaid';
                $dueDate = $monthDate->copy()->day(10);

                $invoiceId = DB::table('invoices')->insertGetId([
                    'contract_id'   => $contract->id,
                    'month'         => $month,
                    'year'          => $year,
                    'room_fee'      => $contract->monthly_price,
                    'electricity_fee'=> $elecFee,
                    'water_fee'     => $waterFee,
                    'service_fee'   => $serviceFee,
                    'total'         => $total,
                    'paid_amount'   => $isPaid ? $total : 0,
                    'debt'          => $isPaid ? 0 : $total,
                    'due_date'      => $dueDate->toDateString(),
                    'status'        => $status,
                    'created_at'    => $monthDate->copy()->day(5),
                    'updated_at'    => $monthDate->copy()->day(5),
                ]);

                // Tạo bản ghi payment nếu đã thanh toán
                if ($isPaid) {
                    DB::table('payments')->insert([
                        'invoice_id'     => $invoiceId,
                        'amount'         => $total,
                        'method'         => (rand(0, 1) ? 'transfer' : 'cash'),
                        'reference_code' => $isPaid && rand(0, 1) ? 'TXN' . rand(100000, 999999) : null,
                        'paid_at'        => $monthDate->copy()->day(rand(5, 9)),
                        'received_by'    => 'Admin',
                        'created_at'     => $monthDate->copy()->day(rand(5, 9)),
                        'updated_at'     => $monthDate->copy()->day(rand(5, 9)),
                    ]);
                }
            }
        }

        $this->command->info('✅ Đã tạo lịch sử 3 tháng hóa đơn + thanh toán cho tất cả hợp đồng.');
    }
}
