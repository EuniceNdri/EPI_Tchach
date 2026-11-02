<?php
/**
 * =============================================================
 * controllers/NotificationController.php – Contrôleur Notifications
 * =============================================================
 * Gère le centre de notifications, la récupération du flux en direct,
 * et le marquage comme lu des alertes utilisateurs.
 * =============================================================
 */

if (defined('ROOT_PATH')) {
    require_once ROOT_PATH . '/config/database.php';
    require_once ROOT_PATH . '/models/Notification.php';
} else {
    require_once dirname(__DIR__) . '/config/database.php';
    require_once dirname(__DIR__) . '/models/Notification.php';
}

class NotificationController {
    private ?PDO $pdo;
    private Notification $notificationModel;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
        $this->notificationModel = new Notification($this->pdo);

        $this->checkAuth();
    }

    private function checkAuth(): void {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        if (empty($_SESSION['user_id'])) {
            $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
                   || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

            if ($isAjax) {
                header('Content-Type: application/json; charset=UTF-8', true, 401);
                echo json_encode(['success' => false, 'message' => "Non authentifié."]);
                exit;
            }

            header('Location: ' . url('login'));
            exit;
        }
    }

    /**
     * Affiche la liste des notifications ou renvoie le JSON
     */
    public function index(): void {
        global $baseUrl, $base, $assetBase, $asset;

        $userId = (int)$_SESSION['user_id'];
        $notifications = $this->notificationModel->getUserNotifications($userId, 50);
        $unreadCount = $this->notificationModel->countUnread($userId);

        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
               || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

        if ($isAjax) {
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode([
                'success'       => true,
                'unread_count'  => $unreadCount,
                'notifications' => $notifications
            ]);
            exit;
        }

        require_once ROOT_PATH . '/views/desktop/notifications.php';
    }

    /**
     * Retourne uniquement le nombre de notifications non lues (pour le badge dans la barre supérieure)
     */
    public function unreadCount(): void {
        $userId = (int)$_SESSION['user_id'];
        $count = $this->notificationModel->countUnread($userId);

        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['success' => true, 'count' => $count]);
        exit;
    }

    /**
     * Marque une notification spécifique comme lue
     */
    public function markAsRead(): void {
        $userId = (int)$_SESSION['user_id'];
        $input = $_POST;
        if (empty($input)) {
            $raw = file_get_contents('php://input');
            if ($raw) {
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) $input = $decoded;
            }
        }

        $id = (int)($input['id'] ?? $input['notification_id'] ?? 0);
        $success = false;
        if ($id > 0) {
            $success = $this->notificationModel->markAsRead($id, $userId);
        }

        $remainingUnread = $this->notificationModel->countUnread($userId);

        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            'success'        => $success,
            'unread_count'   => $remainingUnread
        ]);
        exit;
    }

    /**
     * Marque toutes les notifications comme lues
     */
    public function markAllRead(): void {
        $userId = (int)$_SESSION['user_id'];
        $success = $this->notificationModel->markAllAsRead($userId);

        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
               || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

        if ($isAjax) {
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(['success' => $success, 'unread_count' => 0]);
            exit;
        }

        $_SESSION['flash_success'] = "Toutes les notifications ont été marquées comme lues.";
        header('Location: ' . url('notifications'));
        exit;
    }

    /**
     * Supprime une notification
     */
    public function delete(): void {
        $userId = (int)$_SESSION['user_id'];
        $input = $_POST;
        if (empty($input)) {
            $raw = file_get_contents('php://input');
            if ($raw) {
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) $input = $decoded;
            }
        }

        $id = (int)($input['id'] ?? $input['notification_id'] ?? 0);
        $success = false;
        if ($id > 0) {
            $success = $this->notificationModel->delete($id, $userId);
        }

        $remainingUnread = $this->notificationModel->countUnread($userId);

        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            'success'      => $success,
            'unread_count' => $remainingUnread
        ]);
        exit;
    }
}
