<?php
/**
 * Hotel Management System - Reports Engine
 *
 * Generate business intelligence reports and analytics
 */

if (!defined('APP_INIT')) {
    define('APP_INIT', true);
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';

class ReportsEngine {
    private $pdo;

    public function __construct() {
        $this->pdo = getDatabase();
    }

    /**
     * Get daily sales report
     */
    public function getDailySalesReport($dateFrom = null, $dateTo = null) {
        $dateFrom = $dateFrom ?? date('Y-m-d', strtotime('-30 days'));
        $dateTo = $dateTo ?? date('Y-m-d');

        try {
            $stmt = $this->pdo->prepare("
                SELECT
                    DATE(b.checkout_at) as sale_date,
                    COUNT(b.id) as total_bookings,
                    COUNT(CASE WHEN b.plan_type = 'short' THEN 1 END) as short_bookings,
                    COUNT(CASE WHEN b.plan_type = 'overnight' THEN 1 END) as overnight_bookings,
                    SUM(b.total_amount) as total_revenue,
                    SUM(CASE WHEN b.plan_type = 'short' THEN b.total_amount ELSE 0 END) as short_revenue,
                    SUM(CASE WHEN b.plan_type = 'overnight' THEN b.total_amount ELSE 0 END) as overnight_revenue,
                    AVG(b.total_amount) as avg_booking_value,
                    SUM(b.extra_amount) as total_extras,
                    COUNT(CASE WHEN b.payment_method = 'cash' THEN 1 END) as cash_payments,
                    COUNT(CASE WHEN b.payment_method = 'card' THEN 1 END) as card_payments,
                    COUNT(CASE WHEN b.payment_method = 'transfer' THEN 1 END) as transfer_payments
                FROM bookings b
                WHERE b.status = 'completed'
                AND DATE(b.checkout_at) BETWEEN ? AND ?
                GROUP BY DATE(b.checkout_at)
                ORDER BY sale_date DESC
            ");

            $stmt->execute([$dateFrom, $dateTo]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("Daily sales report error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get occupancy rate analytics
     */
    public function getOccupancyReport($dateFrom = null, $dateTo = null) {
        $dateFrom = $dateFrom ?? date('Y-m-d', strtotime('-30 days'));
        $dateTo = $dateTo ?? date('Y-m-d');

        try {
            // Get total rooms
            $stmt = $this->pdo->query("SELECT COUNT(*) as total_rooms FROM rooms");
            $totalRooms = $stmt->fetch(PDO::FETCH_ASSOC)['total_rooms'];

            // Get occupancy by date
            $stmt = $this->pdo->prepare("
                SELECT
                    DATE(checkin_at) as occupancy_date,
                    COUNT(DISTINCT room_id) as occupied_rooms,
                    ROUND((COUNT(DISTINCT room_id) / ?) * 100, 2) as occupancy_rate,
                    AVG(TIMESTAMPDIFF(HOUR, checkin_at, COALESCE(checkout_at, NOW()))) as avg_stay_hours,
                    SUM(total_amount) as date_revenue
                FROM bookings
                WHERE status IN ('active', 'completed')
                AND DATE(checkin_at) BETWEEN ? AND ?
                GROUP BY DATE(checkin_at)
                ORDER BY occupancy_date DESC
            ");

            $stmt->execute([$totalRooms, $dateFrom, $dateTo]);
            $occupancyData = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Get room type breakdown
            $stmt = $this->pdo->prepare("
                SELECT
                    r.room_type,
                    COUNT(b.id) as bookings_count,
                    SUM(b.total_amount) as type_revenue,
                    AVG(b.total_amount) as avg_rate,
                    AVG(TIMESTAMPDIFF(HOUR, b.checkin_at, COALESCE(b.checkout_at, NOW()))) as avg_stay_hours
                FROM bookings b
                JOIN rooms r ON b.room_id = r.id
                WHERE b.status IN ('active', 'completed')
                AND DATE(b.checkin_at) BETWEEN ? AND ?
                GROUP BY r.room_type
                ORDER BY type_revenue DESC
            ");

            $stmt->execute([$dateFrom, $dateTo]);
            $roomTypeData = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'total_rooms' => $totalRooms,
                'occupancy_by_date' => $occupancyData,
                'room_type_performance' => $roomTypeData
            ];

        } catch (Exception $e) {
            error_log("Occupancy report error: " . $e->getMessage());
            return ['total_rooms' => 0, 'occupancy_by_date' => [], 'room_type_performance' => []];
        }
    }

    /**
     * Get revenue breakdown report
     */
    public function getRevenueReport($dateFrom = null, $dateTo = null) {
        $dateFrom = $dateFrom ?? date('Y-m-d', strtotime('-30 days'));
        $dateTo = $dateTo ?? date('Y-m-d');

        try {
            // Total revenue summary
            $stmt = $this->pdo->prepare("
                SELECT
                    COUNT(b.id) as total_bookings,
                    SUM(b.base_amount) as base_revenue,
                    SUM(b.extra_amount) as extra_revenue,
                    SUM(b.total_amount) as total_revenue,
                    AVG(b.total_amount) as avg_booking_value,
                    MAX(b.total_amount) as highest_booking,
                    MIN(b.total_amount) as lowest_booking
                FROM bookings b
                WHERE b.status = 'completed'
                AND DATE(b.checkout_at) BETWEEN ? AND ?
            ");

            $stmt->execute([$dateFrom, $dateTo]);
            $summary = $stmt->fetch(PDO::FETCH_ASSOC);

            // Payment method breakdown
            $stmt = $this->pdo->prepare("
                SELECT
                    b.payment_method,
                    COUNT(b.id) as payment_count,
                    SUM(b.total_amount) as payment_revenue,
                    ROUND((COUNT(b.id) / (SELECT COUNT(*) FROM bookings WHERE status = 'completed' AND DATE(checkout_at) BETWEEN ? AND ?)) * 100, 2) as payment_percentage
                FROM bookings b
                WHERE b.status = 'completed'
                AND DATE(b.checkout_at) BETWEEN ? AND ?
                GROUP BY b.payment_method
                ORDER BY payment_revenue DESC
            ");

            $stmt->execute([$dateFrom, $dateTo, $dateFrom, $dateTo]);
            $paymentMethods = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Plan type breakdown
            $stmt = $this->pdo->prepare("
                SELECT
                    b.plan_type,
                    COUNT(b.id) as plan_count,
                    SUM(b.total_amount) as plan_revenue,
                    AVG(b.total_amount) as avg_plan_rate,
                    ROUND((COUNT(b.id) / (SELECT COUNT(*) FROM bookings WHERE status = 'completed' AND DATE(checkout_at) BETWEEN ? AND ?)) * 100, 2) as plan_percentage
                FROM bookings b
                WHERE b.status = 'completed'
                AND DATE(b.checkout_at) BETWEEN ? AND ?
                GROUP BY b.plan_type
                ORDER BY plan_revenue DESC
            ");

            $stmt->execute([$dateFrom, $dateTo, $dateFrom, $dateTo]);
            $planTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Monthly trend (last 12 months)
            $stmt = $this->pdo->prepare("
                SELECT
                    DATE_FORMAT(b.checkout_at, '%Y-%m') as month_year,
                    MONTHNAME(b.checkout_at) as month_name,
                    YEAR(b.checkout_at) as year,
                    COUNT(b.id) as monthly_bookings,
                    SUM(b.total_amount) as monthly_revenue
                FROM bookings b
                WHERE b.status = 'completed'
                AND b.checkout_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                GROUP BY DATE_FORMAT(b.checkout_at, '%Y-%m')
                ORDER BY month_year DESC
                LIMIT 12
            ");

            $stmt->execute();
            $monthlyTrend = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'summary' => $summary,
                'payment_methods' => $paymentMethods,
                'plan_types' => $planTypes,
                'monthly_trend' => $monthlyTrend
            ];

        } catch (Exception $e) {
            error_log("Revenue report error: " . $e->getMessage());
            return ['summary' => [], 'payment_methods' => [], 'plan_types' => [], 'monthly_trend' => []];
        }
    }

    /**
     * Get guest analytics
     */
    public function getGuestReport($dateFrom = null, $dateTo = null) {
        $dateFrom = $dateFrom ?? date('Y-m-d', strtotime('-30 days'));
        $dateTo = $dateTo ?? date('Y-m-d');

        try {
            // Guest statistics
            $stmt = $this->pdo->prepare("
                SELECT
                    COUNT(DISTINCT b.guest_name) as unique_guests,
                    COUNT(b.id) as total_bookings,
                    ROUND(COUNT(b.id) / COUNT(DISTINCT b.guest_name), 2) as avg_bookings_per_guest,
                    SUM(b.guest_count) as total_guests,
                    AVG(b.guest_count) as avg_guests_per_booking,
                    COUNT(CASE WHEN b.guest_count = 1 THEN 1 END) as single_guest_bookings,
                    COUNT(CASE WHEN b.guest_count > 1 THEN 1 END) as group_bookings
                FROM bookings b
                WHERE b.status IN ('active', 'completed')
                AND DATE(b.checkin_at) BETWEEN ? AND ?
            ");

            $stmt->execute([$dateFrom, $dateTo]);
            $guestStats = $stmt->fetch(PDO::FETCH_ASSOC);

            // Top guests by revenue
            $stmt = $this->pdo->prepare("
                SELECT
                    b.guest_name,
                    b.guest_phone,
                    COUNT(b.id) as booking_count,
                    SUM(b.total_amount) as total_spent,
                    AVG(b.total_amount) as avg_spent,
                    MAX(b.checkout_at) as last_visit
                FROM bookings b
                WHERE b.status = 'completed'
                AND DATE(b.checkout_at) BETWEEN ? AND ?
                GROUP BY b.guest_name, b.guest_phone
                HAVING booking_count > 1
                ORDER BY total_spent DESC
                LIMIT 20
            ");

            $stmt->execute([$dateFrom, $dateTo]);
            $topGuests = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'guest_statistics' => $guestStats,
                'top_guests' => $topGuests
            ];

        } catch (Exception $e) {
            error_log("Guest report error: " . $e->getMessage());
            return ['guest_statistics' => [], 'top_guests' => []];
        }
    }

    /**
     * Get current dashboard summary
     */
    public function getDashboardSummary() {
        try {
            // Today's stats
            $today = date('Y-m-d');
            $stmt = $this->pdo->prepare("
                SELECT
                    COUNT(CASE WHEN DATE(b.checkin_at) = ? THEN 1 END) as today_checkins,
                    COUNT(CASE WHEN DATE(b.checkout_at) = ? THEN 1 END) as today_checkouts,
                    SUM(CASE WHEN DATE(b.checkout_at) = ? THEN b.total_amount ELSE 0 END) as today_revenue,
                    COUNT(CASE WHEN b.status = 'active' THEN 1 END) as current_occupied
                FROM bookings b
            ");

            $stmt->execute([$today, $today, $today]);
            $todayStats = $stmt->fetch(PDO::FETCH_ASSOC);

            // Room status summary
            $stmt = $this->pdo->query("
                SELECT
                    status,
                    COUNT(*) as count
                FROM rooms
                GROUP BY status
            ");
            $roomStatus = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // This month vs last month
            $thisMonth = date('Y-m');
            $lastMonth = date('Y-m', strtotime('-1 month'));

            $stmt = $this->pdo->prepare("
                SELECT
                    SUM(CASE WHEN DATE_FORMAT(b.checkout_at, '%Y-%m') = ? THEN b.total_amount ELSE 0 END) as this_month_revenue,
                    SUM(CASE WHEN DATE_FORMAT(b.checkout_at, '%Y-%m') = ? THEN b.total_amount ELSE 0 END) as last_month_revenue,
                    COUNT(CASE WHEN DATE_FORMAT(b.checkout_at, '%Y-%m') = ? THEN 1 END) as this_month_bookings,
                    COUNT(CASE WHEN DATE_FORMAT(b.checkout_at, '%Y-%m') = ? THEN 1 END) as last_month_bookings
                FROM bookings b
                WHERE b.status = 'completed'
            ");

            $stmt->execute([$thisMonth, $lastMonth, $thisMonth, $lastMonth]);
            $monthlyComparison = $stmt->fetch(PDO::FETCH_ASSOC);

            return [
                'today' => $todayStats,
                'room_status' => $roomStatus,
                'monthly_comparison' => $monthlyComparison
            ];

        } catch (Exception $e) {
            error_log("Dashboard summary error: " . $e->getMessage());
            return ['today' => [], 'room_status' => [], 'monthly_comparison' => []];
        }
    }

    /**
     * Export data to CSV format
     */
    public function exportToCSV($data, $filename, $headers) {
        $output = fopen('php://temp/maxmemory:' . (5*1024*1024), 'r+');

        // Add BOM for UTF-8
        fwrite($output, "\xEF\xBB\xBF");

        // Add headers
        fputcsv($output, $headers);

        // Add data
        foreach ($data as $row) {
            fputcsv($output, $row);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        // Set headers for download
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($csv));

        echo $csv;
        exit;
    }

    /**
     * Generate chart data for JavaScript
     */
    public function getChartData($type, $dateFrom = null, $dateTo = null) {
        switch ($type) {
            case 'daily_revenue':
                $data = $this->getDailySalesReport($dateFrom, $dateTo);
                return [
                    'labels' => array_column($data, 'sale_date'),
                    'datasets' => [
                        [
                            'label' => 'รายได้รายวัน',
                            'data' => array_column($data, 'total_revenue'),
                            'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                            'borderColor' => 'rgba(54, 162, 235, 1)',
                            'borderWidth' => 2
                        ]
                    ]
                ];

            case 'occupancy_rate':
                $report = $this->getOccupancyReport($dateFrom, $dateTo);
                return [
                    'labels' => array_column($report['occupancy_by_date'], 'occupancy_date'),
                    'datasets' => [
                        [
                            'label' => 'อัตราการเข้าพัก (%)',
                            'data' => array_column($report['occupancy_by_date'], 'occupancy_rate'),
                            'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                            'borderColor' => 'rgba(255, 99, 132, 1)',
                            'borderWidth' => 2
                        ]
                    ]
                ];

            case 'payment_methods':
                $report = $this->getRevenueReport($dateFrom, $dateTo);
                return [
                    'labels' => array_map(function($method) {
                        return ['cash' => 'เงินสด', 'card' => 'บัตร', 'transfer' => 'โอนเงิน'][$method['payment_method']] ?? $method['payment_method'];
                    }, $report['payment_methods']),
                    'datasets' => [
                        [
                            'data' => array_column($report['payment_methods'], 'payment_revenue'),
                            'backgroundColor' => ['#FF6384', '#36A2EB', '#FFCE56']
                        ]
                    ]
                ];

            default:
                return ['labels' => [], 'datasets' => []];
        }
    }
}
?>