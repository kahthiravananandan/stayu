<?php
class OwnerController {
    private Listing $listingModel;
    private VerificationDocument $docModel;
    private ViewingRequest $viewingModel;
    private Conversation $convModel;
    private Complaint $complaintModel;
    private Notification $notifModel;

    public function __construct() {
        $this->listingModel   = new Listing();
        $this->docModel       = new VerificationDocument();
        $this->viewingModel   = new ViewingRequest();
        $this->convModel      = new Conversation();
        $this->complaintModel = new Complaint();
        $this->notifModel     = new Notification();
    }

    public function index(?string $param = null): void {
        redirect('owner/dashboard');
    }

    public function dashboard(?string $param = null): void {
        requireOwner();
        $ownerId       = getSessionUserId();
        $listings      = $this->listingModel->getByOwner($ownerId);
        $viewings      = $this->viewingModel->getByOwner($ownerId);
        $conversations = $this->convModel->getByOwner($ownerId);
        $complaints    = $this->complaintModel->getByOwner($ownerId);
        $notifications = $this->notifModel->getByUser($ownerId);
        $this->notifModel->markRead($ownerId);
        require BASE_PATH . '/app/views/owner/dashboard.php';
    }

    public function listing_form(?string $id = null): void {
        requireOwner();
        $ownerId  = getSessionUserId();
        $user     = (new User())->findById($ownerId);
        $amenityList = $this->listingModel->getAllAmenities();
        $listing  = null;

        if ($id) {
            $listing = $this->listingModel->getById((int)$id);
            if (!$listing || (int)$listing['owner_id'] !== $ownerId) {
                setFlash('error', 'Iklan tidak dijumpai.');
                redirect('owner/listing_manage');
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verifyCsrf();
            $errors = $this->validateListingForm();

            if (empty($errors)) {
                $isCorporate = ($user['owner_type'] === 'korporat');
                $data = [
                    'owner_id'      => $ownerId,
                    'title'         => sanitize($_POST['title']),
                    'description'   => sanitize($_POST['description']),
                    'property_type' => sanitize($_POST['property_type']),
                    'monthly_rent'  => (float)$_POST['monthly_rent'],
                    'deposit'       => !empty($_POST['deposit']) ? (float)$_POST['deposit'] : null,
                    'gender_pref'   => sanitize($_POST['gender_pref']),
                    'address'       => sanitize($_POST['address']),
                    'latitude'      => !empty($_POST['latitude'])  ? (float)$_POST['latitude']  : null,
                    'longitude'     => !empty($_POST['longitude']) ? (float)$_POST['longitude'] : null,
                    'distance_km'   => !empty($_POST['distance_km']) ? (float)$_POST['distance_km'] : null,
                    'status'        => $isCorporate ? 'active' : 'in_review',
                ];

                if ($id) {
                    $this->listingModel->update((int)$id, $data);
                    $listingId = (int)$id;
                } else {
                    $listingId = $this->listingModel->create($data);
                }

                // Sync amenities
                $amenities = array_map('intval', (array)($_POST['amenities'] ?? []));
                $this->listingModel->syncAmenities($listingId, $amenities);

                // Handle photo uploads
                $this->handlePhotoUploads($listingId);

                if (!$isCorporate && !$id) {
                    setFlash('success', 'Iklan dihantar untuk semakan. Sila muat naik dokumen pengesahan.');
                    redirect('owner/document_upload/' . $listingId);
                } else {
                    setFlash('success', 'Iklan berjaya ' . ($id ? 'dikemaskini' : 'diterbitkan') . '!');
                    redirect('owner/listing_manage');
                }
            }

            foreach ($errors as $e) setFlash('error', $e);
        }

        $data = ['csrf' => csrfToken(), 'listing' => $listing, 'amenityList' => $amenityList];
        require BASE_PATH . '/app/views/owner/listing_form.php';
    }

    public function listing_manage(?string $param = null): void {
        requireOwner();
        $ownerId  = getSessionUserId();
        $listings = $this->listingModel->getByOwner($ownerId);
        require BASE_PATH . '/app/views/owner/listing_manage.php';
    }

    public function listing_status(?string $id = null): void {
        requireOwner();
        if (!$id || $_SERVER['REQUEST_METHOD'] !== 'POST') redirect('owner/listing_manage');
        verifyCsrf();

        $listing = $this->listingModel->getById((int)$id);
        if (!$listing || (int)$listing['owner_id'] !== getSessionUserId()) {
            setFlash('error', 'Tidak dibenarkan.');
            redirect('owner/listing_manage');
        }

        $status = sanitize($_POST['status'] ?? '');
        $allowed = ['in_negotiation', 'unavailable', 'active'];
        if (in_array($status, $allowed, true)) {
            $this->listingModel->updateStatus((int)$id, $status);
            setFlash('success', 'Status iklan dikemaskini.');
        }
        redirect('owner/listing_manage');
    }

    public function listing_delete(?string $id = null): void {
        requireOwner();
        if (!$id || $_SERVER['REQUEST_METHOD'] !== 'POST') redirect('owner/listing_manage');
        verifyCsrf();

        $listing = $this->listingModel->getById((int)$id);
        if (!$listing || (int)$listing['owner_id'] !== getSessionUserId()) {
            setFlash('error', 'Tidak dibenarkan.');
            redirect('owner/listing_manage');
        }

        $this->listingModel->delete((int)$id);
        setFlash('success', 'Iklan berjaya dipadam.');
        redirect('owner/listing_manage');
    }

    public function document_upload(?string $listingId = null): void {
        requireOwner();
        if (!$listingId) redirect('owner/listing_manage');

        $ownerId = getSessionUserId();
        $listing = $this->listingModel->getById((int)$listingId);
        $user    = (new User())->findById($ownerId);

        // Only individu owners need document verification; korporat listings go live immediately
        if (!$listing || (int)$listing['owner_id'] !== $ownerId || $user['owner_type'] !== 'individu') {
            setFlash('error', 'Tidak dibenarkan.');
            redirect('owner/listing_manage');
        }

        $existingDoc = $this->docModel->getByListing((int)$listingId);

        // Block access while a submission is already under pending review
        if ($existingDoc && $existingDoc['status'] === 'pending') {
            setFlash('error', 'Dokumen anda sedang disemak oleh admin. Sila tunggu keputusan.');
            redirect('owner/listing_manage');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verifyCsrf();
            $errors       = [];
            $allowedMimes = ['application/pdf', 'image/jpeg', 'image/png'];
            $icErrors     = validateFileUpload($_FILES['ic_doc']    ?? [], $allowedMimes, 5 * 1024 * 1024);
            $grantErrors  = validateFileUpload($_FILES['grant_doc'] ?? [], $allowedMimes, 5 * 1024 * 1024);
            $errors       = array_merge($icErrors, $grantErrors);

            if (empty($errors)) {
                $uploadDir = BASE_PATH . '/public/uploads/documents/';
                $icName    = uniqid('ic_')    . '_' . basename($_FILES['ic_doc']['name']);
                $grantName = uniqid('grant_') . '_' . basename($_FILES['grant_doc']['name']);

                if (move_uploaded_file($_FILES['ic_doc']['tmp_name'],    $uploadDir . $icName) &&
                    move_uploaded_file($_FILES['grant_doc']['tmp_name'], $uploadDir . $grantName)) {

                    $isReupload = $existingDoc && $existingDoc['status'] === 'rejected';
                    if ($isReupload) {
                        // UC13 — re-upload: reset existing record to pending with new files
                        $this->docModel->reupload((int)$existingDoc['document_id'], $icName, $grantName);
                    } else {
                        // UC12 — initial upload
                        $this->docModel->create((int)$listingId, $ownerId, $icName, $grantName);
                    }

                    // Notify admin
                    $adminUser = (new User())->findByEmail('admin@stayu.ukm.my');
                    if ($adminUser) {
                        $verb = $isReupload ? 'dimuat naik semula' : 'baharu';
                        $this->notifModel->create(
                            (int)$adminUser['user_id'],
                            'document_upload',
                            'Dokumen pengesahan ' . $verb . ' untuk iklan "' . $listing['title'] . '".'
                        );
                    }

                    setFlash('success', 'Dokumen berjaya dimuat naik. Admin akan menyemaknya dalam masa 2 hari bekerja.');
                    redirect('owner/listing_manage');
                } else {
                    $errors[] = 'Muat naik fail gagal. Cuba lagi.';
                }
            }

            foreach ($errors as $e) setFlash('error', $e);
        }

        $data = ['csrf' => csrfToken(), 'listing' => $listing, 'existingDoc' => $existingDoc];
        require BASE_PATH . '/app/views/owner/document_upload.php';
    }

    public function profile(?string $param = null): void {
        requireOwner();
        $userId    = getSessionUserId();
        $userModel = new User();
        $user      = $userModel->findById($userId);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verifyCsrf();
            $name   = sanitize($_POST['full_name'] ?? '');
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
                redirect('owner/profile');
            }

            foreach ($errors as $e) setFlash('error', $e);
        }

        $data = ['csrf' => csrfToken(), 'user' => $user];
        require BASE_PATH . '/app/views/owner/profile.php';
    }

    public function notifications(?string $param = null): void {
        requireOwner();
        $userId        = getSessionUserId();
        $notifications = $this->notifModel->getByUser($userId, 50);
        $this->notifModel->markRead($userId);
        require BASE_PATH . '/app/views/owner/notifications.php';
    }

    public function viewing_manage(?string $param = null): void {
        requireOwner();
        $ownerId  = getSessionUserId();
        $viewings = $this->viewingModel->getByOwner($ownerId);
        require BASE_PATH . '/app/views/owner/viewing_manage.php';
    }

    public function viewing_action(?string $requestId = null): void {
        requireOwner();
        if (!$requestId || $_SERVER['REQUEST_METHOD'] !== 'POST') redirect('owner/viewing_manage');
        verifyCsrf();

        $req     = $this->viewingModel->getById((int)$requestId);
        $listing = $req ? $this->listingModel->getById($req['listing_id']) : null;

        if (!$req || !$listing || (int)$listing['owner_id'] !== getSessionUserId()) {
            setFlash('error', 'Tidak dibenarkan.');
            redirect('owner/viewing_manage');
        }

        $action = sanitize($_POST['action'] ?? '');
        if (in_array($action, ['confirmed', 'rejected'], true)) {
            $this->viewingModel->updateStatus((int)$requestId, $action);
            $msg = $action === 'confirmed'
                ? 'Permintaan tontonan untuk "' . $listing['title'] . '" telah disahkan.'
                : 'Permintaan tontonan untuk "' . $listing['title'] . '" telah ditolak.';
            $this->notifModel->create((int)$req['student_id'], 'viewing_' . $action, $msg);
            setFlash('success', 'Status permintaan dikemaskini.');
        }

        redirect('owner/viewing_manage');
    }

    public function chat_inbox(?string $convId = null): void {
        requireOwner();
        $ownerId = getSessionUserId();

        if ($convId) {
            $conv = $this->convModel->getById((int)$convId);
            if (!$conv || (int)$conv['owner_id'] !== $ownerId) {
                setFlash('error', 'Perbualan tidak dijumpai.');
                redirect('owner/chat_inbox');
            }
            require BASE_PATH . '/app/views/owner/chat_inbox.php';
            return;
        }

        $conversations = $this->convModel->getByOwner($ownerId);
        require BASE_PATH . '/app/views/owner/chat_inbox.php';
    }

    public function complaint_response(?string $complaintId = null): void {
        requireOwner();
        if (!$complaintId) redirect('owner/dashboard');

        $ownerId   = getSessionUserId();
        $complaint = $this->complaintModel->getById((int)$complaintId);

        if (!$complaint || (int)$complaint['reported_owner_id'] !== $ownerId) {
            setFlash('error', 'Aduan tidak dijumpai.');
            redirect('owner/dashboard');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verifyCsrf();
            $defense  = sanitize($_POST['defense'] ?? '');
            $evidence = null;

            if (strlen($defense) < 20) {
                setFlash('error', 'Pembelaan mesti sekurang-kurangnya 20 aksara.');
                redirect('owner/complaint_response/' . $complaintId);
            }

            // Handle optional evidence upload
            if (!empty($_FILES['evidence']['name'])) {
                $allowedMimes = ['application/pdf', 'image/jpeg', 'image/png'];
                $errs = validateFileUpload($_FILES['evidence'], $allowedMimes, 5 * 1024 * 1024);
                if (empty($errs)) {
                    $uploadDir = BASE_PATH . '/public/uploads/documents/';
                    $fname = uniqid('ev_') . '_' . basename($_FILES['evidence']['name']);
                    if (move_uploaded_file($_FILES['evidence']['tmp_name'], $uploadDir . $fname)) {
                        $evidence = $fname;
                    }
                }
            }

            $this->complaintModel->submitDefense((int)$complaintId, $ownerId, $defense, $evidence);

            // Notify admin that a defense has been submitted
            $adminUser = (new User())->findByEmail('admin@stayu.ukm.my');
            if ($adminUser) {
                $ownerName = $_SESSION['full_name'] ?? 'Pemilik';
                $this->notifModel->create(
                    (int)$adminUser['user_id'],
                    'defense_submitted',
                    'Pemilik ' . $ownerName . ' telah menghantar pembelaan untuk aduan #' . $complaintId . '.'
                );
            }

            setFlash('success', 'Pembelaan berjaya dihantar.');
            redirect('owner/dashboard');
        }

        $data = ['csrf' => csrfToken(), 'complaint' => $complaint];
        require BASE_PATH . '/app/views/owner/complaint_response.php';
    }

    private function validateListingForm(): array {
        $errors = [];
        if (empty(trim($_POST['title'] ?? '')))       $errors[] = 'Tajuk diperlukan.';
        if (empty(trim($_POST['address'] ?? '')))     $errors[] = 'Alamat diperlukan.';
        if (empty($_POST['property_type'] ?? ''))     $errors[] = 'Jenis hartanah diperlukan.';
        if (!is_numeric($_POST['monthly_rent'] ?? '')) $errors[] = 'Sewa bulanan tidak sah.';
        if ((float)($_POST['monthly_rent'] ?? 0) <= 0) $errors[] = 'Sewa bulanan mesti lebih daripada 0.';
        return $errors;
    }

    private function handlePhotoUploads(int $listingId): void {
        if (empty($_FILES['photos']['name'][0])) return;

        $uploadDir   = BASE_PATH . '/public/uploads/photos/';
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
        $firstPhoto  = true;

        foreach ($_FILES['photos']['tmp_name'] as $i => $tmpName) {
            if ($_FILES['photos']['error'][$i] !== UPLOAD_ERR_OK) continue;
            $fakeFile = [
                'tmp_name' => $tmpName,
                'name'     => $_FILES['photos']['name'][$i],
                'size'     => $_FILES['photos']['size'][$i],
                'error'    => $_FILES['photos']['error'][$i],
            ];
            $errs = validateFileUpload($fakeFile, $allowedMimes, 5 * 1024 * 1024);
            if (!empty($errs)) continue;

            $fname = uniqid('photo_') . '_' . basename($_FILES['photos']['name'][$i]);
            if (move_uploaded_file($tmpName, $uploadDir . $fname)) {
                $existingPhotos = $this->listingModel->getPhotos($listingId);
                $isCover = $firstPhoto && empty($existingPhotos);
                $this->listingModel->addPhoto($listingId, $fname, $isCover);
                $firstPhoto = false;
            }
        }
    }
}
