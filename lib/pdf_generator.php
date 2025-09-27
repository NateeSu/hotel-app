<?php
/**
 * Hotel Management System - PDF Generator
 *
 * Generate PDF reports using HTML to PDF conversion
 */

if (!defined('APP_INIT')) {
    define('APP_INIT', true);
}

class PDFGenerator {

    /**
     * Generate PDF from HTML content
     */
    public function generateFromHTML($html, $filename, $orientation = 'P') {
        // Clean the HTML
        $html = $this->cleanHTML($html);

        // Add CSS for PDF formatting
        $pdfHTML = $this->wrapHTMLForPDF($html, $orientation);

        // Set headers for PDF download
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');

        // Use browser's print to PDF capability
        echo $pdfHTML;
        exit;
    }

    /**
     * Generate sales report PDF
     */
    public function generateSalesReportPDF($salesData, $dateFrom, $dateTo, $totals) {
        $html = $this->buildSalesReportHTML($salesData, $dateFrom, $dateTo, $totals);
        $filename = 'sales_report_' . $dateFrom . '_to_' . $dateTo . '.pdf';
        $this->generateFromHTML($html, $filename, 'L'); // Landscape
    }

    /**
     * Generate occupancy report PDF
     */
    public function generateOccupancyReportPDF($occupancyData, $dateFrom, $dateTo, $summary) {
        $html = $this->buildOccupancyReportHTML($occupancyData, $dateFrom, $dateTo, $summary);
        $filename = 'occupancy_report_' . $dateFrom . '_to_' . $dateTo . '.pdf';
        $this->generateFromHTML($html, $filename, 'P'); // Portrait
    }

    /**
     * Build HTML for sales report
     */
    private function buildSalesReportHTML($salesData, $dateFrom, $dateTo, $totals) {
        ob_start();
        ?>
        <div class="report-header">
            <h1>รายงานยอดขาย</h1>
            <p>ระหว่างวันที่ <?php echo date('d/m/Y', strtotime($dateFrom)); ?> - <?php echo date('d/m/Y', strtotime($dateTo)); ?></p>
            <p>สร้างเมื่อ: <?php echo date('d/m/Y H:i:s'); ?></p>
        </div>

        <div class="summary-section">
            <h2>สรุปภาพรวม</h2>
            <table class="summary-table">
                <tr>
                    <td>การจองทั้งหมด:</td>
                    <td><?php echo number_format($totals['total_bookings']); ?> รายการ</td>
                </tr>
                <tr>
                    <td>รายได้รวม:</td>
                    <td><?php echo number_format($totals['total_revenue'], 2); ?> บาท</td>
                </tr>
                <tr>
                    <td>รายได้เฉลี่ยต่อวัน:</td>
                    <td><?php echo number_format($totals['avg_daily_revenue'], 2); ?> บาท</td>
                </tr>
                <tr>
                    <td>รายได้เสริม:</td>
                    <td><?php echo number_format($totals['total_extras'], 2); ?> บาท</td>
                </tr>
            </table>
        </div>

        <div class="details-section">
            <h2>รายละเอียดรายวัน</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>วันที่</th>
                        <th>การจอง</th>
                        <th>รายชั่วโมง</th>
                        <th>รายคืน</th>
                        <th>รายได้รวม</th>
                        <th>รายได้เสริม</th>
                        <th>เฉลี่ย/การจอง</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($salesData as $row): ?>
                    <tr>
                        <td><?php echo date('d/m/Y', strtotime($row['sale_date'])); ?></td>
                        <td><?php echo $row['total_bookings']; ?></td>
                        <td><?php echo $row['short_bookings']; ?></td>
                        <td><?php echo $row['overnight_bookings']; ?></td>
                        <td><?php echo number_format($row['total_revenue'], 2); ?></td>
                        <td><?php echo number_format($row['total_extras'], 2); ?></td>
                        <td><?php echo number_format($row['avg_booking_value'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Build HTML for occupancy report
     */
    private function buildOccupancyReportHTML($occupancyData, $dateFrom, $dateTo, $summary) {
        ob_start();
        ?>
        <div class="report-header">
            <h1>รายงานการเข้าพัก</h1>
            <p>ระหว่างวันที่ <?php echo date('d/m/Y', strtotime($dateFrom)); ?> - <?php echo date('d/m/Y', strtotime($dateTo)); ?></p>
            <p>สร้างเมื่อ: <?php echo date('d/m/Y H:i:s'); ?></p>
        </div>

        <div class="summary-section">
            <h2>สรุปภาพรวม</h2>
            <table class="summary-table">
                <tr>
                    <td>ห้องทั้งหมด:</td>
                    <td><?php echo $summary['total_rooms']; ?> ห้อง</td>
                </tr>
                <tr>
                    <td>อัตราการเข้าพักเฉลี่ย:</td>
                    <td><?php echo number_format($summary['avg_occupancy_rate'], 1); ?>%</td>
                </tr>
                <tr>
                    <td>อัตราการเข้าพักสูงสุด:</td>
                    <td><?php echo number_format($summary['max_occupancy'], 1); ?>%</td>
                </tr>
                <tr>
                    <td>อัตราการเข้าพักต่ำสุด:</td>
                    <td><?php echo number_format($summary['min_occupancy'], 1); ?>%</td>
                </tr>
            </table>
        </div>

        <div class="details-section">
            <h2>รายละเอียดรายวัน</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>วันที่</th>
                        <th>ห้องที่เข้าพัก</th>
                        <th>อัตราการเข้าพัก</th>
                        <th>ชั่วโมงเฉลี่ย</th>
                        <th>รายได้</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($occupancyData as $row): ?>
                    <tr>
                        <td><?php echo date('d/m/Y', strtotime($row['occupancy_date'])); ?></td>
                        <td><?php echo $row['occupied_rooms']; ?>/<?php echo $summary['total_rooms']; ?></td>
                        <td><?php echo number_format($row['occupancy_rate'], 1); ?>%</td>
                        <td><?php echo number_format($row['avg_stay_hours'], 1); ?></td>
                        <td><?php echo number_format($row['date_revenue'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Wrap HTML with PDF-specific CSS and structure
     */
    private function wrapHTMLForPDF($content, $orientation = 'P') {
        $pageSize = $orientation === 'L' ? '@page { size: A4 landscape; }' : '@page { size: A4 portrait; }';

        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Hotel Management Report</title>
    <style>
        ' . $pageSize . '

        body {
            font-family: "Sarabun", "TH Sarabun New", Arial, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            background: white;
        }

        .report-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #333;
        }

        .report-header h1 {
            font-size: 24px;
            margin: 0 0 10px 0;
            color: #333;
        }

        .report-header p {
            margin: 5px 0;
            color: #666;
        }

        .summary-section {
            margin-bottom: 30px;
        }

        .summary-section h2 {
            font-size: 18px;
            margin-bottom: 15px;
            color: #333;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .summary-table td {
            padding: 8px;
            border-bottom: 1px solid #eee;
        }

        .summary-table td:first-child {
            font-weight: bold;
            width: 200px;
        }

        .details-section h2 {
            font-size: 18px;
            margin-bottom: 15px;
            color: #333;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }

        .data-table th,
        .data-table td {
            padding: 8px;
            text-align: left;
            border: 1px solid #ddd;
        }

        .data-table th {
            background-color: #f5f5f5;
            font-weight: bold;
            text-align: center;
        }

        .data-table td:nth-child(n+3) {
            text-align: right;
        }

        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }

        .footer {
            position: fixed;
            bottom: 20px;
            right: 20px;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    ' . $content . '

    <div class="footer">
        Hotel Management System - สร้างอัตโนมัติ
    </div>

    <script>
        // Auto-print when loaded
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>';
    }

    /**
     * Clean HTML for PDF generation
     */
    private function cleanHTML($html) {
        // Remove any script tags for security
        $html = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi', '', $html);

        // Convert relative URLs to absolute if needed
        // $html = str_replace('src="/', 'src="' . $GLOBALS['baseUrl'] . '/', $html);

        return $html;
    }

    /**
     * Generate simple HTML report
     */
    public function generateSimpleReport($title, $data, $headers) {
        ob_start();
        ?>
        <div class="report-header">
            <h1><?php echo htmlspecialchars($title); ?></h1>
            <p>สร้างเมื่อ: <?php echo date('d/m/Y H:i:s'); ?></p>
        </div>

        <div class="details-section">
            <table class="data-table">
                <thead>
                    <tr>
                        <?php foreach ($headers as $header): ?>
                        <th><?php echo htmlspecialchars($header); ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data as $row): ?>
                    <tr>
                        <?php foreach ($row as $cell): ?>
                        <td><?php echo htmlspecialchars($cell); ?></td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php

        $html = ob_get_clean();
        return $this->wrapHTMLForPDF($html);
    }
}
?>