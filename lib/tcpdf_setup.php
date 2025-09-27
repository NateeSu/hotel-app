<?php
/**
 * TCPDF Setup for Hotel Management System
 * Download and setup instructions for TCPDF library
 */

// Check if TCPDF is available
function checkTCPDFAvailability() {
    $tcpdfPath = __DIR__ . '/tcpdf/tcpdf.php';
    return file_exists($tcpdfPath);
}

// Simple HTML to PDF converter as fallback
function generateHTMLReceipt($receiptData) {
    ob_start();
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>ใบเสร็จรับเงิน - <?php echo htmlspecialchars($receiptData['booking_code']); ?></title>
        <style>
            @media print {
                body { margin: 0; }
                .no-print { display: none; }
            }
            body {
                font-family: 'Sarabun', 'TH Sarabun New', sans-serif;
                font-size: 14px;
                line-height: 1.4;
                max-width: 800px;
                margin: 0 auto;
                padding: 20px;
            }
            .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
            .hotel-name { font-size: 24px; font-weight: bold; margin-bottom: 5px; }
            .hotel-address { font-size: 12px; color: #666; }
            .receipt-title { font-size: 18px; font-weight: bold; margin: 20px 0; text-align: center; }
            .info-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
            .info-table td { padding: 8px; border-bottom: 1px solid #eee; }
            .info-table .label { font-weight: bold; width: 150px; }
            .charges-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
            .charges-table th, .charges-table td { padding: 10px; border: 1px solid #ddd; text-align: left; }
            .charges-table th { background-color: #f5f5f5; font-weight: bold; }
            .charges-table .amount { text-align: right; }
            .total-section { background-color: #f9f9f9; padding: 15px; border: 2px solid #333; margin: 20px 0; }
            .total-amount { font-size: 18px; font-weight: bold; text-align: right; }
            .footer { margin-top: 30px; font-size: 12px; color: #666; text-align: center; }
            .signature-section { margin-top: 40px; }
            .signature-box { width: 200px; text-align: center; margin: 0 auto; }
            .signature-line { border-top: 1px solid #333; padding-top: 5px; margin-top: 50px; }
        </style>
    </head>
    <body>
        <div class="header">
            <div class="hotel-name"><?php echo htmlspecialchars($receiptData['hotel_name'] ?? 'HOTEL MANAGEMENT SYSTEM'); ?></div>
            <div class="hotel-address"><?php echo htmlspecialchars($receiptData['hotel_address'] ?? 'Address not set'); ?></div>
            <div class="hotel-address">โทร: <?php echo htmlspecialchars($receiptData['hotel_phone'] ?? 'Phone not set'); ?></div>
        </div>

        <div class="receipt-title">ใบเสร็จรับเงิน / RECEIPT</div>

        <table class="info-table">
            <tr>
                <td class="label">เลขที่ใบเสร็จ:</td>
                <td><?php echo htmlspecialchars($receiptData['receipt_number']); ?></td>
                <td class="label">วันที่:</td>
                <td><?php echo htmlspecialchars($receiptData['date']); ?></td>
            </tr>
            <tr>
                <td class="label">รหัสการจอง:</td>
                <td><?php echo htmlspecialchars($receiptData['booking_code']); ?></td>
                <td class="label">ห้อง:</td>
                <td><?php echo htmlspecialchars($receiptData['room_number']); ?></td>
            </tr>
            <tr>
                <td class="label">ชื่อผู้เข้าพัก:</td>
                <td colspan="3"><?php echo htmlspecialchars($receiptData['guest_name']); ?></td>
            </tr>
            <tr>
                <td class="label">เบอร์โทร:</td>
                <td><?php echo htmlspecialchars($receiptData['guest_phone']); ?></td>
                <td class="label">จำนวนผู้เข้าพัก:</td>
                <td><?php echo htmlspecialchars($receiptData['guest_count']); ?> ท่าน</td>
            </tr>
            <tr>
                <td class="label">เข้าพัก:</td>
                <td><?php echo htmlspecialchars($receiptData['checkin_time']); ?></td>
                <td class="label">ออก:</td>
                <td><?php echo htmlspecialchars($receiptData['checkout_time']); ?></td>
            </tr>
            <tr>
                <td class="label">ระยะเวลาพัก:</td>
                <td colspan="3"><?php echo htmlspecialchars($receiptData['duration']); ?></td>
            </tr>
        </table>

        <table class="charges-table">
            <thead>
                <tr>
                    <th>รายการ</th>
                    <th>รายละเอียด</th>
                    <th class="amount">จำนวนเงิน</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>ค่าห้องพัก (<?php echo htmlspecialchars($receiptData['plan_type_text']); ?>)</td>
                    <td><?php echo htmlspecialchars($receiptData['base_duration']); ?> ชั่วโมง</td>
                    <td class="amount"><?php echo number_format($receiptData['base_amount'], 2); ?> บาท</td>
                </tr>
                <?php if ($receiptData['overtime_amount'] > 0): ?>
                <tr>
                    <td>ค่าเวลาเกิน</td>
                    <td><?php echo htmlspecialchars($receiptData['overtime_hours']); ?> ชั่วโมง × 100 บาท</td>
                    <td class="amount"><?php echo number_format($receiptData['overtime_amount'], 2); ?> บาท</td>
                </tr>
                <?php endif; ?>
                <?php if ($receiptData['extra_amount'] > 0): ?>
                <tr>
                    <td>ค่าบริการเพิ่มเติม</td>
                    <td><?php echo htmlspecialchars($receiptData['extra_notes'] ?? '-'); ?></td>
                    <td class="amount"><?php echo number_format($receiptData['extra_amount'], 2); ?> บาท</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="total-section">
            <div class="total-amount">
                <strong>รวมเป็นเงินทั้งสิ้น: <?php echo number_format($receiptData['total_amount'], 2); ?> บาท</strong>
            </div>
            <div style="margin-top: 10px;">
                <strong>วิธีการชำระเงิน:</strong> <?php echo htmlspecialchars($receiptData['payment_method_text']); ?>
            </div>
        </div>

        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-line">
                    ผู้รับเงิน / Received by
                </div>
            </div>
        </div>

        <div class="footer">
            <p>*** ขอบคุณที่ใช้บริการ / Thank you for your patronage ***</p>
            <p>ออกใบเสร็จเมื่อ: <?php echo date('d/m/Y H:i:s'); ?></p>
        </div>

        <div class="no-print" style="margin-top: 30px; text-align: center;">
            <button onclick="window.print()" class="btn btn-primary">พิมพ์ใบเสร็จ</button>
            <button onclick="window.close()" class="btn btn-secondary">ปิด</button>
        </div>
    </body>
    </html>
    <?php
    return ob_get_clean();
}

// Convert Thai number to text (for legal requirements)
function numberToThaiText($number) {
    $ones = ['', 'หนึ่ง', 'สอง', 'สาม', 'สี่', 'ห้า', 'หก', 'เจ็ด', 'แปด', 'เก้า'];
    $tens = ['', '', 'ยี่', 'สาม', 'สี่', 'ห้า', 'หก', 'เจ็ด', 'แปด', 'เก้า'];

    $number = floor($number);

    if ($number == 0) return 'ศูนย์บาท';
    if ($number > 999999) return 'จำนวนเงินเกินขีดจำกัด';

    $result = '';

    // Handle hundreds of thousands
    if ($number >= 100000) {
        $result .= $ones[floor($number / 100000)] . 'แสน';
        $number %= 100000;
    }

    // Handle ten thousands
    if ($number >= 10000) {
        $result .= $ones[floor($number / 10000)] . 'หมื่น';
        $number %= 10000;
    }

    // Handle thousands
    if ($number >= 1000) {
        $result .= $ones[floor($number / 1000)] . 'พัน';
        $number %= 1000;
    }

    // Handle hundreds
    if ($number >= 100) {
        $result .= $ones[floor($number / 100)] . 'ร้อย';
        $number %= 100;
    }

    // Handle tens and ones
    if ($number >= 20) {
        $result .= $tens[floor($number / 10)] . 'สิบ';
        if ($number % 10 > 0) {
            $result .= $ones[$number % 10];
        }
    } elseif ($number >= 10) {
        $result .= 'สิบ';
        if ($number % 10 > 0) {
            $result .= $ones[$number % 10];
        }
    } elseif ($number > 0) {
        $result .= $ones[$number];
    }

    return $result . 'บาท';
}
?>