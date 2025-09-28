<?php
/**
 * Hotel Management System - Telegram Notification Service
 *
 * Send notifications to housekeeping staff via Telegram
 */

if (!defined('APP_INIT')) {
    define('APP_INIT', true);
}

class TelegramService {
    private $botToken;
    private $apiUrl;
    private $pdo;

    public function __construct() {
        $this->pdo = getDatabase();

        // Get bot token from database settings or environment
        $this->botToken = $this->getSetting('telegram_bot_token');
        $this->apiUrl = "https://api.telegram.org/bot{$this->botToken}/";

        if (!$this->botToken) {
            error_log("Telegram bot token not configured");
        }
    }

    /**
     * Send housekeeping notification
     */
    public function sendHousekeepingNotification($housekeepingJobId, $chatId = null) {
        try {
            // Get housekeeping job details
            $jobDetails = $this->getHousekeepingJobDetails($housekeepingJobId);

            if (!$jobDetails) {
                throw new Exception("Housekeeping job not found: $housekeepingJobId");
            }

            // Get chat IDs for housekeeping staff if not specified
            if (!$chatId) {
                $chatIds = $this->getHousekeepingChatIds();
            } else {
                $chatIds = [$chatId];
            }

            $results = [];
            foreach ($chatIds as $chat) {
                $result = $this->sendJobNotification($jobDetails, $chat);
                $results[] = $result;

                // Log notification
                $this->logNotification($housekeepingJobId, $chat, $result);
            }

            return $results;

        } catch (Exception $e) {
            error_log("Telegram notification error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send job completion notification
     */
    public function sendJobCompletionNotification($housekeepingJobId, $completedBy) {
        try {
            $jobDetails = $this->getHousekeepingJobDetails($housekeepingJobId);
            $chatIds = $this->getReceptionChatIds();

            $message = "✅ งานทำความสะอาดเสร็จสิ้น\n\n";
            $message .= "🏠 ห้อง: {$jobDetails['room_number']}\n";
            $message .= "👤 ดำเนินการโดย: {$completedBy}\n";
            $message .= "⏰ เสร็จเมื่อ: " . date('d/m/Y H:i') . "\n";
            $message .= "📝 สถานะห้อง: เปลี่ยนเป็น 'ว่าง' แล้ว\n";

            foreach ($chatIds as $chatId) {
                $this->sendMessage($chatId, $message);
            }

            return true;

        } catch (Exception $e) {
            error_log("Job completion notification error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send job notification with details and link
     */
    private function sendJobNotification($jobDetails, $chatId) {
        $jobUrl = $this->generateAccessibleUrl("/?r=housekeeping.job&id=" . $jobDetails['id']);

        $message = "🧹 <b>งานทำความสะอาดใหม่!</b>\n\n";
        $message .= "🏠 ห้อง: <b>{$jobDetails['room_number']}</b> ({$jobDetails['room_type']})\n";
        $message .= "👤 แขกเช็คเอาท์: {$jobDetails['guest_name']}\n";
        $message .= "⏰ เวลาเช็คเอาท์: " . date('d/m/Y H:i', strtotime($jobDetails['checkout_time'])) . "\n";
        $message .= "📋 ประเภทงาน: {$jobDetails['task_description']}\n";
        $message .= "🎯 ความสำคัญ: {$jobDetails['priority_text']}\n\n";

        if (!empty($jobDetails['special_notes'])) {
            $message .= "📝 หมายเหตุพิเศษ: {$jobDetails['special_notes']}\n\n";
        }

        $message .= "🔗 <b>คลิกเพื่อดูรายละเอียดงาน:</b>\n";
        $message .= "<a href=\"{$jobUrl}\">🖱️ เปิดหน้างาน #{$jobDetails['id']}</a>\n\n";
        $message .= "📱 <i>หรือคัดลอก URL นี้:</i>\n";
        $message .= "<code>{$jobUrl}</code>";

        return $this->sendMessage($chatId, $message);
    }

    /**
     * Send message via Telegram API
     */
    public function sendMessage($chatId, $message) {
        if (!$this->botToken) {
            return ['success' => false, 'error' => 'Bot token not configured'];
        }

        $url = $this->apiUrl . "sendMessage";

        $data = [
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => false
        ];

        $options = [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json'
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false
        ];

        $curl = curl_init();
        curl_setopt_array($curl, $options);
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        $result = json_decode($response, true);

        return [
            'success' => $httpCode === 200 && $result['ok'],
            'response' => $result,
            'http_code' => $httpCode
        ];
    }

    /**
     * Get housekeeping job details
     */
    private function getHousekeepingJobDetails($jobId) {
        $stmt = $this->pdo->prepare("
            SELECT
                hj.*,
                r.room_number,
                r.room_type,
                b.guest_name,
                b.checkout_at as checkout_time,
                CASE
                    WHEN hj.priority = 'high' THEN 'สูง'
                    WHEN hj.priority = 'normal' THEN 'ปกติ'
                    WHEN hj.priority = 'low' THEN 'ต่ำ'
                    ELSE 'ปกติ'
                END as priority_text,
                CASE
                    WHEN hj.task_type = 'checkout_cleaning' THEN 'ทำความสะอาดหลังเช็คเอาท์'
                    WHEN hj.task_type = 'maintenance' THEN 'งานซ่อมบำรุง'
                    WHEN hj.task_type = 'inspection' THEN 'ตรวจสอบห้อง'
                    ELSE 'งานทั่วไป'
                END as task_description
            FROM housekeeping_jobs hj
            JOIN rooms r ON hj.room_id = r.id
            LEFT JOIN bookings b ON hj.booking_id = b.id
            WHERE hj.id = ?
        ");

        $stmt->execute([$jobId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get chat IDs for housekeeping staff
     */
    private function getHousekeepingChatIds() {
        $stmt = $this->pdo->prepare("
            SELECT telegram_chat_id
            FROM users
            WHERE role = 'housekeeping'
            AND telegram_chat_id IS NOT NULL
            AND is_active = 1
        ");

        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // If no housekeeping staff configured, get from settings
        if (empty($results)) {
            $defaultChatId = $this->getSetting('default_housekeeping_chat_id');
            if ($defaultChatId) {
                return [$defaultChatId];
            }
        }

        return $results;
    }

    /**
     * Get chat IDs for reception staff
     */
    private function getReceptionChatIds() {
        $stmt = $this->pdo->prepare("
            SELECT telegram_chat_id
            FROM users
            WHERE role IN ('reception', 'admin')
            AND telegram_chat_id IS NOT NULL
            AND is_active = 1
        ");

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Log notification to database
     */
    private function logNotification($housekeepingJobId, $chatId, $result) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO telegram_notifications
                (housekeeping_job_id, chat_id, message_text, sent_at, status, response_data)
                VALUES (?, ?, ?, NOW(), ?, ?)
            ");

            $status = $result['success'] ? 'sent' : 'failed';
            $responseData = json_encode($result);

            $stmt->execute([
                $housekeepingJobId,
                $chatId,
                'Housekeeping job notification',
                $status,
                $responseData
            ]);

        } catch (Exception $e) {
            error_log("Failed to log notification: " . $e->getMessage());
        }
    }

    /**
     * Get system setting
     */
    private function getSetting($key, $default = null) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT setting_value
                FROM hotel_settings
                WHERE setting_key = ?
            ");

            $stmt->execute([$key]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result ? $result['setting_value'] : $default;

        } catch (Exception $e) {
            // Table might not exist yet
            return $default;
        }
    }

    /**
     * Generate accessible URL for Telegram
     */
    private function generateAccessibleUrl($path) {
        $baseUrl = $GLOBALS['baseUrl'];

        // If localhost, try to get external URL from settings
        if (strpos($baseUrl, 'localhost') !== false || strpos($baseUrl, '127.0.0.1') !== false) {
            $externalUrl = $this->getSetting('external_url');
            if ($externalUrl) {
                $baseUrl = rtrim($externalUrl, '/');
            } else {
                // Use ngrok or similar if available
                $ngrokUrl = $this->getSetting('ngrok_url');
                if ($ngrokUrl) {
                    $baseUrl = rtrim($ngrokUrl, '/');
                }
            }
        }

        return $baseUrl . $path;
    }

    /**
     * Set system setting
     */
    public function setSetting($key, $value) {
        try {
            // Create settings table if not exists
            $this->pdo->exec("
                CREATE TABLE IF NOT EXISTS hotel_settings (
                    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                    setting_key VARCHAR(255) UNIQUE NOT NULL,
                    setting_value TEXT,
                    setting_type ENUM('text', 'number', 'boolean', 'json') DEFAULT 'text',
                    updated_by INT UNSIGNED,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    INDEX idx_setting_key (setting_key)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            $stmt = $this->pdo->prepare("
                INSERT INTO hotel_settings (setting_key, setting_value, updated_by)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE
                setting_value = VALUES(setting_value),
                updated_by = VALUES(updated_by),
                updated_at = CURRENT_TIMESTAMP
            ");

            return $stmt->execute([$key, $value, $_SESSION['user_id'] ?? 1]);

        } catch (Exception $e) {
            error_log("Failed to set setting: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Test bot connection
     */
    public function testBotConnection() {
        if (!$this->botToken) {
            return ['success' => false, 'error' => 'Bot token not configured'];
        }

        $url = $this->apiUrl . "getMe";

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        $result = json_decode($response, true);

        return [
            'success' => $httpCode === 200 && $result['ok'],
            'bot_info' => $result['result'] ?? null,
            'error' => $result['description'] ?? null
        ];
    }

    /**
     * Get webhook info
     */
    public function getWebhookInfo() {
        $url = $this->apiUrl . "getWebhookInfo";

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        return json_decode($response, true);
    }
}
?>