<?php
class NotificationController {
    private Notification $notifModel;

    public function __construct() {
        $this->notifModel = new Notification();
    }

    // Mark a single notification as read, then send user to their notifications page.
    public function mark_read(?string $id = null): void {
        requireLogin();
        if ($id && ctype_digit($id)) {
            $this->notifModel->markOneById((int)$id, getSessionUserId());
        }
        $this->redirectNotifications();
    }

    // Mark all notifications as read, then send user to their notifications page.
    public function mark_all_read(?string $param = null): void {
        requireLogin();
        $this->notifModel->markAllRead(getSessionUserId());
        $this->redirectNotifications();
    }

    private function redirectNotifications(): never {
        $role = getSessionRole();
        match ($role) {
            'pemilik' => redirect('owner/notifications'),
            'admin'   => redirect('admin/notifications'),
            default   => redirect('student/notifications'),
        };
    }
}
