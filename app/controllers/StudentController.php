<?php
class StudentController {
    private Listing $listingModel;
    private ViewingRequest $viewingModel;
    private Conversation $convModel;
    private Complaint $complaintModel;
    private Notification $notifModel;

    public function __construct() {
        $this->listingModel  = new Listing();
        $this->viewingModel  = new ViewingRequest();
        $this->convModel     = new Conversation();
        $this->complaintModel = new Complaint();
        $this->notifModel    = new Notification();
    }

    public function index(?string $param = null): void {
        $this->search($param);
    }

    public function dashboard(?string $param = null): void {
        requireStudent();
        $userId       = getSessionUserId();
        $viewings     = $this->viewingModel->getByStudent($userId);
        $conversations = $this->convModel->getByStudent($userId);
        $complaints   = $this->complaintModel->getByComplainant($userId);
        $notifications = $this->notifModel->getByUser($userId);
        $this->notifModel->markRead($userId);
        require BASE_PATH . '/app/views/student/dashboard.php';
    }

    public function search(?string $param = null): void {
        $filters = [
            'keyword'       => sanitize($_GET['keyword'] ?? ''),
            'property_type' => sanitize($_GET['property_type'] ?? ''),
            'min_rent'      => sanitize($_GET['min_rent'] ?? ''),
            'max_rent'      => sanitize($_GET['max_rent'] ?? ''),
            'gender_pref'   => sanitize($_GET['gender_pref'] ?? ''),
            'max_distance'  => sanitize($_GET['max_distance'] ?? ''),
            'amenities'     => array_map('intval', (array)($_GET['amenities'] ?? [])),
        ];

        $page       = max(1, (int)($_GET['page'] ?? 1));
        $perPage    = 12;
        $listings   = $this->listingModel->getAll($filters, $page, $perPage);
        $totalCount = $this->listingModel->countAll($filters);
        $totalPages = (int) ceil($totalCount / $perPage);
        $amenities  = $this->listingModel->getAllAmenities();

        require BASE_PATH . '/app/views/student/search.php';
    }

    public function listing(?string $id = null): void {
        if (!$id) redirect('student/search');

        $listing = $this->listingModel->getById((int)$id);
        if (!$listing || $listing['status'] !== 'active') {
            setFlash('error', 'Iklan tidak dijumpai atau tidak aktif.');
            redirect('student/search');
        }

        // Compute and persist distance_km on first view if coordinates are known but distance is missing
        if (!$listing['distance_km'] && $listing['latitude'] && $listing['longitude']) {
            $dist = $this->computeDistance((float)$listing['latitude'], (float)$listing['longitude']);
            $this->listingModel->updateDistance((int)$listing['listing_id'], $dist);
            $listing['distance_km'] = $dist;
        }

        $alreadyRequested = false;
        $conversation     = null;
        if (isLoggedIn() && getSessionRole() === 'pelajar') {
            $studentId = getSessionUserId();
            $alreadyRequested = $this->viewingModel->existsPending((int)$id, $studentId);
            $conversation = $this->convModel->findOrCreate((int)$id, $studentId, (int)$listing['owner_id']);
        }

        require BASE_PATH . '/app/views/student/listing_detail.php';
    }

    public function profile(?string $param = null): void {
        requireStudent();
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
                redirect('student/profile');
            }

            foreach ($errors as $e) setFlash('error', $e);
        }

        $data = ['csrf' => csrfToken(), 'user' => $user];
        require BASE_PATH . '/app/views/student/profile.php';
    }

    public function notifications(?string $param = null): void {
        requireStudent();
        $userId        = getSessionUserId();
        $notifications = $this->notifModel->getByUser($userId, 50);
        $this->notifModel->markRead($userId);
        require BASE_PATH . '/app/views/student/notifications.php';
    }

    private function computeDistance(float $lat, float $lng): float {
        // UKM Bangi main gate (per spec)
        $ukmLat = 2.9213;
        $ukmLng = 101.7740;

        // Try Google Distance Matrix API for road distance
        $url = 'https://maps.googleapis.com/maps/api/distancematrix/json?' . http_build_query([
            'origins'      => "{$ukmLat},{$ukmLng}",
            'destinations' => "{$lat},{$lng}",
            'mode'         => 'driving',
            'key'          => GOOGLE_MAPS_API_KEY,
        ]);
        $ctx      = stream_context_create(['http' => ['timeout' => 4]]);
        $response = @file_get_contents($url, false, $ctx);
        if ($response !== false) {
            $json   = json_decode($response, true);
            $meters = $json['rows'][0]['elements'][0]['distance']['value'] ?? null;
            if ($meters !== null) {
                return round($meters / 1000, 2);
            }
        }

        // Fallback: Haversine straight-line distance
        $R    = 6371;
        $dLat = deg2rad($lat - $ukmLat);
        $dLng = deg2rad($lng - $ukmLng);
        $a    = sin($dLat / 2) ** 2 + cos(deg2rad($ukmLat)) * cos(deg2rad($lat)) * sin($dLng / 2) ** 2;
        return round(2 * $R * asin(sqrt($a)), 2);
    }

    public function map(?string $param = null): void {
        requireStudent();
        $filters  = ['keyword' => sanitize($_GET['keyword'] ?? '')];
        $listings = $this->listingModel->getAll($filters, 1, 200);
        require BASE_PATH . '/app/views/student/map.php';
    }

    public function viewing_request(?string $listingId = null): void {
        requireStudent();
        if (!$listingId) redirect('student/search');

        $listing   = $this->listingModel->getById((int)$listingId);
        $studentId = getSessionUserId();

        if (!$listing || $listing['status'] !== 'active') {
            setFlash('error', 'Iklan tidak tersedia.');
            redirect('student/search');
        }

        if ($this->viewingModel->existsPending((int)$listingId, $studentId)) {
            setFlash('error', 'Anda sudah mempunyai permintaan tontonan yang menunggu untuk iklan ini.');
            redirect('student/listing/' . $listingId);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verifyCsrf();
            $date = sanitize($_POST['proposed_date'] ?? '');
            $time = sanitize($_POST['proposed_time'] ?? '');
            $errors = [];

            if (!validateDate($date)) $errors[] = 'Tarikh tidak sah atau sudah lepas.';
            if (!validateTime($time)) $errors[] = 'Masa tidak sah.';

            if (empty($errors)) {
                $reqId = $this->viewingModel->create((int)$listingId, $studentId, $date, $time);
                // Notify owner
                $this->notifModel->create(
                    (int)$listing['owner_id'],
                    'viewing_request',
                    'Permintaan tontonan baharu untuk "' . $listing['title'] . '".'
                );
                setFlash('success', 'Permintaan tontonan berjaya dihantar!');
                redirect('student/dashboard');
            }

            foreach ($errors as $e) setFlash('error', $e);
        }

        $data = ['csrf' => csrfToken(), 'listing' => $listing];
        require BASE_PATH . '/app/views/student/viewing_request.php';
    }

    public function chat(?string $convId = null): void {
        requireStudent();
        if (!$convId) redirect('student/dashboard');

        $conv = $this->convModel->getById((int)$convId);
        if (!$conv || (int)$conv['student_id'] !== getSessionUserId()) {
            setFlash('error', 'Perbualan tidak dijumpai.');
            redirect('student/dashboard');
        }

        require BASE_PATH . '/app/views/student/chat.php';
    }

    public function complaint_form(?string $listingId = null): void {
        requireStudent();
        $listing = null;
        if ($listingId) {
            $listing = $this->listingModel->getById((int)$listingId);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verifyCsrf();
            $studentId = getSessionUserId();
            $ownerId   = (int)sanitize($_POST['owner_id'] ?? '0');
            $lid       = !empty($_POST['listing_id']) ? (int)$_POST['listing_id'] : null;
            $category  = sanitize($_POST['category'] ?? '');
            $desc      = sanitize($_POST['description'] ?? '');

            $errors = [];
            if (empty($category))   $errors[] = 'Kategori diperlukan.';
            if (strlen($desc) < 20) $errors[] = 'Penerangan mesti sekurang-kurangnya 20 aksara.';
            if (!$ownerId)          $errors[] = 'Pemilik tidak sah.';

            if (empty($errors)) {
                $complaintId = $this->complaintModel->create([
                    'complainant_id'    => $studentId,
                    'reported_owner_id' => $ownerId,
                    'listing_id'        => $lid,
                    'category'          => $category,
                    'description'       => $desc,
                ]);

                $userModel    = new User();
                $ownerUser    = $userModel->findById($ownerId);
                $ownerName    = $ownerUser ? $ownerUser['full_name'] : 'Pemilik';
                $listingTitle = $listing ? $listing['title'] : null;

                // Notify admin
                $adminUser = $userModel->findByEmail('admin@stayu.ukm.my');
                if ($adminUser) {
                    $adminMsg = 'Aduan baharu diterima terhadap ' . $ownerName
                        . ($listingTitle ? ' untuk iklan "' . $listingTitle . '".' : '.');
                    $this->notifModel->create(
                        (int)$adminUser['user_id'],
                        'new_complaint',
                        $adminMsg
                    );
                }

                // Notify reported owner
                $ownerMsg = 'Aduan telah difailkan terhadap anda'
                    . ($listingTitle ? ' berkaitan iklan "' . $listingTitle . '".' : '.');
                $this->notifModel->create($ownerId, 'complaint_received', $ownerMsg);

                setFlash('success', 'Aduan berjaya dihantar. Admin akan menyemaknya dalam masa 3 hari bekerja.');
                redirect('student/dashboard');
            }

            foreach ($errors as $e) setFlash('error', $e);
        }

        $data = ['csrf' => csrfToken(), 'listing' => $listing];
        require BASE_PATH . '/app/views/student/complaint_form.php';
    }
}
