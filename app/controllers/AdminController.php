<?php
class AdminController {
    private User $userModel;
    private Listing $listingModel;
    private VerificationDocument $docModel;
    private Complaint $complaintModel;
    private Notification $notifModel;

    public function __construct() {
        $this->userModel      = new User();
        $this->listingModel   = new Listing();
        $this->docModel       = new VerificationDocument();
        $this->complaintModel = new Complaint();
        $this->notifModel     = new Notification();
    }

    public function index(?string $param = null): void {
        redirect('admin/dashboard');
    }

    public function dashboard(?string $param = null): void {
        requireAdmin();
        $stats = [
            'total_students'  => $this->userModel->countByRole('pelajar'),
            'total_owners'    => $this->userModel->countByRole('pemilik'),
            'active_listings' => $this->listingModel->countByStatus('active'),
            'pending_docs'    => $this->docModel->countPending(),
            'open_complaints' => $this->complaintModel->countOpen(),
            'in_review'       => $this->listingModel->countByStatus('in_review'),
        ];
        $pendingDocs    = $this->docModel->getPending();
        $openComplaints = $this->complaintModel->getAll();
        require BASE_PATH . '/app/views/admin/dashboard.php';
    }

    public function profile(?string $param = null): void {
        requireAdmin();
        $userId    = getSessionUserId();
        $userModel = $this->userModel;
        $user      = $userModel->findById($userId);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verifyCsrf();
            $name   = sanitize($_POST['full_name']    ?? '');
            $phone  = sanitize($_POST['phone_number'] ?? '');
            $errors = [];

            if (empty($name))           $errors[] = 'Nama penuh diperlukan.';
            if (!validatePhone($phone)) $errors[] = 'Nombor telefon tidak sah.';

            $photoPath = $user['profile_photo'];
            if (!empty($_FILES['profile_photo']['name'])) {
                $allowed = ['image/jpeg', 'image/png', 'image/webp'];
                $errs    = validateFileUpload($_FILES['profile_photo'], $allowed, 2 * 1024 * 1024);
                if (empty($errs)) {
                    $ext   = strtolower(pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION));
                    $fname = 'avatar_' . $userId . '_' . uniqid() . '.' . $ext;
                    if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], BASE_PATH . '/public/uploads/photos/' . $fname)) {
                        $photoPath = $fname;
                    } else {
                        $errors[] = 'Muat naik gambar profil gagal. Cuba lagi.';
                    }
                } else {
                    $errors = array_merge($errors, $errs);
                }
            }

            if (empty($errors)) {
                $userModel->updateProfile($userId, [
                    'full_name'     => $name,
                    'phone_number'  => $phone,
                    'profile_photo' => $photoPath,
                ]);
                $_SESSION['full_name'] = $name;
                if (!empty($photoPath)) {
                    $_SESSION['profile_photo'] = $photoPath;
                }
                setFlash('success', 'Profil berjaya dikemaskini.');
                redirect('admin/profile');
            }

            foreach ($errors as $e) setFlash('error', $e);
        }

        $data = ['csrf' => csrfToken(), 'user' => $user];
        require BASE_PATH . '/app/views/admin/profile.php';
    }

    public function notifications(?string $param = null): void {
        requireAdmin();
        $userId        = getSessionUserId();
        $notifications = $this->notifModel->getByUser($userId, 50);
        $this->notifModel->markRead($userId);
        require BASE_PATH . '/app/views/admin/notifications.php';
    }

    // ─── UC19: Register Corporate Owner ──────────────────────────────────────

    public function register_corporate(?string $param = null): void {
        requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verifyCsrf();
            $errors = [];
            $name  = sanitize($_POST['full_name']    ?? '');
            $ic    = preg_replace('/[-\s]/', '', sanitize($_POST['ic_number']    ?? ''));
            $email = strtolower(trim(sanitize($_POST['email']          ?? '')));
            $phone = sanitize($_POST['phone_number'] ?? '');

            if (empty($name))           $errors[] = 'Nama syarikat diperlukan.';
            if (!validateIC($ic))       $errors[] = 'Nombor IC tidak sah. Masukkan 12 digit.';
            if (!validateEmail($email)) $errors[] = 'E-mel tidak sah.';
            if (!validatePhone($phone)) $errors[] = 'Nombor telefon tidak sah.';

            if (empty($errors)) {
                if ($this->userModel->findByIC($ic))       $errors[] = 'IC sudah berdaftar.';
                if ($this->userModel->findByEmail($email)) $errors[] = 'E-mel sudah berdaftar.';
            }

            if (empty($errors)) {
                // Generate secure temp password — shown once to admin
                $tempPass  = bin2hex(random_bytes(5)); // 10 hex chars
                $newUserId = $this->userModel->createCorporate([
                    'full_name'    => $name,
                    'ic_number'    => $ic,
                    'email'        => $email,
                    'phone_number' => $phone,
                    'password'     => $tempPass,
                ]);
                // Notify the new corporate owner
                $this->notifModel->create(
                    $newUserId,
                    'account_created',
                    'Akaun UKM Real Estate anda telah didaftarkan oleh admin. Sila tukar kata laluan anda selepas log masuk pertama.'
                );
                setFlash('corp_temp_password', $tempPass);
                setFlash('corp_temp_name',     $name);
                setFlash('success', 'Akaun korporat berjaya didaftarkan. Sila simpan kata laluan sementara.');
                redirect('admin/register_corporate');
            }

            foreach ($errors as $e) setFlash('error', $e);
        }

        $corporates = array_filter(
            $this->userModel->getAllOwners(),
            fn($u) => $u['owner_type'] === 'korporat'
        );
        $data = [
            'csrf'       => csrfToken(),
            'corporates' => $corporates,
            'tempPass'   => getFlash('corp_temp_password'),
            'tempName'   => getFlash('corp_temp_name'),
        ];
        require BASE_PATH . '/app/views/admin/register_corporate.php';
    }

    // ─── UC20: Document Review ────────────────────────────────────────────────

    public function document_review(?string $docId = null): void {
        requireAdmin();

        if ($docId) {
            $doc = $this->docModel->getById((int)$docId);
            if (!$doc) {
                setFlash('error', 'Dokumen tidak dijumpai.');
                redirect('admin/document_review');
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                verifyCsrf();
                $action  = sanitize($_POST['action'] ?? '');
                $adminId = getSessionUserId();

                if ($action === 'approve') {
                    $this->docModel->approve((int)$docId, $adminId);
                    $this->listingModel->updateStatus((int)$doc['listing_id'], 'active');
                    $this->notifModel->create(
                        (int)$doc['owner_id'],
                        'document_approved',
                        'Dokumen anda untuk iklan "' . $doc['listing_title'] . '" telah diluluskan. Iklan kini aktif.'
                    );
                    setFlash('success', 'Dokumen diluluskan. Iklan kini aktif.');

                } elseif ($action === 'reject') {
                    $reason = sanitize($_POST['rejection_reason'] ?? '');
                    if (empty($reason)) {
                        setFlash('error', 'Sebab penolakan diperlukan.');
                        redirect('admin/document_review/' . $docId);
                    }
                    $this->docModel->reject((int)$docId, $adminId, $reason);
                    $this->listingModel->updateStatus((int)$doc['listing_id'], 'rejected');
                    $this->notifModel->create(
                        (int)$doc['owner_id'],
                        'document_rejected',
                        'Dokumen anda untuk iklan "' . $doc['listing_title'] . '" ditolak. Sebab: ' . $reason
                    );
                    setFlash('success', 'Dokumen ditolak. Pemilik telah dimaklumkan.');
                }

                redirect('admin/document_review');
            }

            $data = ['csrf' => csrfToken(), 'doc' => $doc];
            require BASE_PATH . '/app/views/admin/document_review.php';
            return;
        }

        $pendingDocs = $this->docModel->getPending();
        $data = ['pendingDocs' => $pendingDocs];
        require BASE_PATH . '/app/views/admin/document_review.php';
    }

    // ─── UC21: Complaint Panel ────────────────────────────────────────────────

    public function complaint_panel(?string $complaintId = null): void {
        requireAdmin();

        if ($complaintId) {
            $complaint = $this->complaintModel->getById((int)$complaintId);
            if (!$complaint) {
                setFlash('error', 'Aduan tidak dijumpai.');
                redirect('admin/complaint_panel');
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                verifyCsrf();
                $action  = sanitize($_POST['action'] ?? '');
                $adminId = getSessionUserId();

                $actionLabel = match ($action) {
                    'suspend_listing'    => 'Iklan digantung',
                    'deactivate_account' => 'Akaun pemilik digantung',
                    'close_case'         => 'Kes ditutup — tiada pelanggaran',
                    default              => null,
                };

                if (!$actionLabel) {
                    setFlash('error', 'Tindakan tidak sah.');
                    redirect('admin/complaint_panel/' . $complaintId);
                }

                // Resolve the complaint first
                $this->complaintModel->resolve((int)$complaintId, $adminId, $actionLabel);

                // Take the specific action
                if ($action === 'suspend_listing' && !empty($complaint['listing_id'])) {
                    $this->listingModel->updateStatus((int)$complaint['listing_id'], 'suspended');
                    $this->notifModel->create(
                        (int)$complaint['reported_owner_id'],
                        'listing_suspended',
                        'Iklan "' . ($complaint['listing_title'] ?? '') . '" telah digantung berikutan aduan yang diterima.'
                    );
                    $this->notifModel->create(
                        (int)$complaint['complainant_id'],
                        'complaint_resolved',
                        'Aduan anda telah diselesaikan. Iklan pemilik telah digantung.'
                    );

                } elseif ($action === 'deactivate_account') {
                    $this->userModel->updateStatus((int)$complaint['reported_owner_id'], 'suspended');
                    $this->notifModel->create(
                        (int)$complaint['reported_owner_id'],
                        'account_suspended',
                        'Akaun anda telah digantung berikutan aduan yang diterima. Hubungi admin untuk maklumat lanjut.'
                    );
                    $this->notifModel->create(
                        (int)$complaint['complainant_id'],
                        'complaint_resolved',
                        'Aduan anda telah diselesaikan. Akaun pemilik telah digantung.'
                    );

                } elseif ($action === 'close_case') {
                    $this->notifModel->create(
                        (int)$complaint['reported_owner_id'],
                        'complaint_closed',
                        'Aduan terhadap anda telah dikaji dan ditutup. Tiada tindakan diambil.'
                    );
                    $this->notifModel->create(
                        (int)$complaint['complainant_id'],
                        'complaint_resolved',
                        'Aduan anda telah dikaji. Kes ditutup — tiada pelanggaran ditemui.'
                    );
                }

                setFlash('success', 'Aduan diselesaikan.');
                redirect('admin/complaint_panel');
            }

            $data = ['csrf' => csrfToken(), 'complaint' => $complaint];
            require BASE_PATH . '/app/views/admin/complaint_panel.php';
            return;
        }

        $complaints = $this->complaintModel->getAll();
        $data = ['complaints' => $complaints];
        require BASE_PATH . '/app/views/admin/complaint_panel.php';
    }

    // ─── System Monitor ───────────────────────────────────────────────────────

    public function system_monitor(?string $param = null): void {
        requireAdmin();
        $stats = [
            'total_students'     => $this->userModel->countByRole('pelajar'),
            'total_owners'       => $this->userModel->countByRole('pemilik'),
            'active_listings'    => $this->listingModel->countByStatus('active'),
            'in_review'          => $this->listingModel->countByStatus('in_review'),
            'pending_docs'       => $this->docModel->countPending(),
            'open_complaints'    => $this->complaintModel->countOpen(),
            'suspended_listings' => $this->listingModel->countByStatus('suspended'),
        ];
        $allOwners   = $this->userModel->getAllOwners();
        $allStudents = $this->userModel->getAllStudents();
        $allListings = $this->listingModel->getAllForAdmin();
        require BASE_PATH . '/app/views/admin/system_monitor.php';
    }

    public function suspend_user(?string $userId = null): void {
        requireAdmin();
        if (!$userId || $_SERVER['REQUEST_METHOD'] !== 'POST') redirect('admin/system_monitor');
        verifyCsrf();

        $action     = sanitize($_POST['action'] ?? 'suspend');
        $status     = ($action === 'activate') ? 'active' : 'suspended';
        $targetUser = $this->userModel->findById((int)$userId);

        $this->userModel->updateStatus((int)$userId, $status);

        if ($status === 'suspended' && $targetUser) {
            $this->notifModel->create(
                (int)$userId,
                'account_suspended',
                'Akaun anda telah digantung oleh admin. Hubungi Pusat Perumahan Pelajar (PPP) untuk maklumat lanjut.'
            );
        }

        setFlash('success', 'Status pengguna dikemaskini.');
        redirect('admin/system_monitor');
    }

    public function suspend_listing(?string $listingId = null): void {
        requireAdmin();
        if (!$listingId || $_SERVER['REQUEST_METHOD'] !== 'POST') redirect('admin/system_monitor');
        verifyCsrf();

        $action  = sanitize($_POST['action'] ?? 'suspend');
        $status  = ($action === 'activate') ? 'active' : 'suspended';
        $listing = $this->listingModel->getById((int)$listingId);

        $this->listingModel->updateStatus((int)$listingId, $status);

        if ($status === 'suspended' && $listing) {
            $this->notifModel->create(
                (int)$listing['owner_id'],
                'listing_suspended',
                'Iklan "' . $listing['title'] . '" telah digantung oleh admin. Hubungi PPP untuk maklumat lanjut.'
            );
        }

        setFlash('success', 'Status iklan dikemaskini.');
        redirect('admin/system_monitor');
    }

    // ─── Secure file serving ──────────────────────────────────────────────────

    public function serve_doc(?string $docId = null): void {
        requireAdmin();
        if (!$docId) redirect('admin/document_review');

        $doc = $this->docModel->getById((int)$docId);
        if (!$doc) {
            http_response_code(404);
            exit('Dokumen tidak dijumpai.');
        }

        $type     = sanitize($_GET['type'] ?? 'ic');
        $filename = match ($type) {
            'grant' => $doc['grant_doc_path'],
            default => $doc['ic_doc_path'],
        };

        $fullPath = BASE_PATH . '/public/uploads/documents/' . basename($filename);
        if (!$filename || !is_file($fullPath)) {
            http_response_code(404);
            exit('Fail tidak dijumpai.');
        }

        $finfo   = new finfo(FILEINFO_MIME_TYPE);
        $mime    = $finfo->file($fullPath) ?: 'application/octet-stream';
        $allowed = ['application/pdf', 'image/jpeg', 'image/png'];
        if (!in_array($mime, $allowed, true)) {
            http_response_code(403);
            exit('Jenis fail tidak dibenarkan.');
        }

        header('Content-Type: '           . $mime);
        header('Content-Length: '         . filesize($fullPath));
        header('Content-Disposition: inline; filename="' . basename($filename) . '"');
        header('Cache-Control: private, no-store');
        header('X-Content-Type-Options: nosniff');
        readfile($fullPath);
        exit;
    }

    public function serve_evidence(?string $complaintId = null): void {
        requireAdmin();
        if (!$complaintId) redirect('admin/complaint_panel');

        $complaint = $this->complaintModel->getById((int)$complaintId);
        if (!$complaint || empty($complaint['defense_evidence'])) {
            http_response_code(404);
            exit('Fail tidak dijumpai.');
        }

        $fullPath = BASE_PATH . '/public/uploads/documents/' . basename($complaint['defense_evidence']);
        if (!is_file($fullPath)) {
            http_response_code(404);
            exit('Fail tidak dijumpai.');
        }

        $finfo   = new finfo(FILEINFO_MIME_TYPE);
        $mime    = $finfo->file($fullPath) ?: 'application/octet-stream';
        $allowed = ['application/pdf', 'image/jpeg', 'image/png'];
        if (!in_array($mime, $allowed, true)) {
            http_response_code(403);
            exit('Jenis fail tidak dibenarkan.');
        }

        header('Content-Type: '           . $mime);
        header('Content-Length: '         . filesize($fullPath));
        header('Content-Disposition: inline; filename="' . basename($complaint['defense_evidence']) . '"');
        header('Cache-Control: private, no-store');
        header('X-Content-Type-Options: nosniff');
        readfile($fullPath);
        exit;
    }
}
