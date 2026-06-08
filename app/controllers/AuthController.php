<?php
class AuthController {
    private User $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function index(?string $param = null): void {
        redirect('auth/login');
    }

    public function login(?string $param = null): void {
        if (isLoggedIn()) {
            $this->redirectByRole();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verifyCsrf();

            $rawIdentifier = trim($_POST['identifier'] ?? '');
            $password      = $_POST['password'] ?? '';
            $role          = sanitize($_POST['role'] ?? '');
            $user          = null;
            $error         = null;

            if ($role === 'pelajar') {
                // Pelajar: matric_number only. UKM matric starts with 'A' + 6 digits.
                $matric = strtoupper($rawIdentifier);
                if (!preg_match('/^A\d{6}$/', $matric)) {
                    $error = 'Nombor matrik tidak sah. Format: A diikuti 6 digit (cth: A202584).';
                } else {
                    $user = $this->userModel->findByMatric($matric);
                }

            } elseif ($role === 'pemilik') {
                // Pemilik: ic_number only (no email fallback).
                $ic = preg_replace('/[-\s]/', '', $rawIdentifier);
                if (!preg_match('/^\d{12}$/', $ic)) {
                    $error = 'Nombor IC tidak sah. Masukkan 12 digit tanpa tanda pisah (cth: 900101145555).';
                } else {
                    $user = $this->userModel->findByIC($ic);
                }

            } elseif ($role === 'admin') {
                // Admin: email only.
                $email = strtolower(trim($rawIdentifier));
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $error = 'Alamat e-mel tidak sah.';
                } else {
                    $user = $this->userModel->findByEmail($email);
                }

            } else {
                $error = 'Sila pilih peranan untuk log masuk.';
            }

            if ($error === null) {
                if (!$user || !$this->userModel->verifyPassword($password, $user['password_hash'])) {
                    $error = 'ID atau kata laluan tidak sah.';
                } elseif ($user['role'] !== $role) {
                    $error = 'ID atau kata laluan tidak sah.';
                } elseif ($user['status'] !== 'active') {
                    $error = 'Akaun anda telah digantung. Hubungi Pusat Perumahan Pelajar (PPP).';
                }
            }

            if ($error !== null) {
                setFlash('error', $error);
                redirect('auth/login');
            }

            setUserSession($user);
            $this->redirectByRole();
        }

        $data = ['csrf' => csrfToken()];
        require BASE_PATH . '/app/views/auth/login.php';
    }

    public function register(?string $param = null): void {
        if (isLoggedIn()) {
            $this->redirectByRole();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verifyCsrf();
            $role = sanitize($_POST['role'] ?? '');
            $errors = [];

            if ($role === 'pelajar') {
                $errors = $this->registerStudent();
            } elseif ($role === 'pemilik') {
                $errors = $this->registerOwner();
            } else {
                $errors[] = 'Peranan tidak sah.';
            }

            if (empty($errors)) {
                setFlash('success', 'Pendaftaran berjaya! Sila log masuk.');
                redirect('auth/login');
            }

            foreach ($errors as $e) setFlash('error', $e);
        }

        $data = ['csrf' => csrfToken()];
        require BASE_PATH . '/app/views/auth/register.php';
    }

    private function registerStudent(): array {
        $errors = [];
        $name   = sanitize($_POST['full_name'] ?? '');
        $matric = strtoupper(sanitize($_POST['matric_number'] ?? ''));
        $phone  = sanitize($_POST['phone_number'] ?? '');
        $pass   = $_POST['password'] ?? '';
        $pass2  = $_POST['password_confirm'] ?? '';

        if (empty($name))             $errors[] = 'Nama penuh diperlukan.';
        if (!validateMatric($matric)) $errors[] = 'Nombor matrik tidak sah. Format: A diikuti 6 digit (cth: A202584).';
        if (!validatePhone($phone))   $errors[] = 'Nombor telefon tidak sah.';
        if (!validatePassword($pass)) $errors[] = 'Kata laluan mesti sekurang-kurangnya 8 aksara.';
        if ($pass !== $pass2)         $errors[] = 'Pengesahan kata laluan tidak sepadan.';

        if (empty($errors)) {
            if ($this->userModel->findByMatric($matric)) $errors[] = 'Nombor matrik sudah berdaftar.';
        }

        if (empty($errors)) {
            $this->userModel->createStudent([
                'full_name'     => $name,
                'matric_number' => $matric,
                'phone_number'  => $phone,
                'password'      => $pass,
            ]);
        }

        return $errors;
    }

    private function registerOwner(): array {
        $errors = [];
        $name  = sanitize($_POST['full_name'] ?? '');
        $ic    = preg_replace('/[-\s]/', '', sanitize($_POST['ic_number'] ?? ''));
        $phone = sanitize($_POST['phone_number'] ?? '');
        $pass  = $_POST['password'] ?? '';
        $pass2 = $_POST['password_confirm'] ?? '';

        if (empty($name))             $errors[] = 'Nama penuh diperlukan.';
        if (!validateIC($ic))         $errors[] = 'Nombor IC tidak sah. Masukkan 12 digit (cth: 900101145555).';
        if (!validatePhone($phone))   $errors[] = 'Nombor telefon tidak sah.';
        if (!validatePassword($pass)) $errors[] = 'Kata laluan mesti sekurang-kurangnya 8 aksara.';
        if ($pass !== $pass2)         $errors[] = 'Pengesahan kata laluan tidak sepadan.';

        if (empty($errors)) {
            if ($this->userModel->findByIC($ic)) $errors[] = 'Nombor IC sudah berdaftar.';
        }

        if (empty($errors)) {
            $this->userModel->createOwner([
                'full_name'    => $name,
                'ic_number'    => $ic,
                'phone_number' => $phone,
                'password'     => $pass,
                'owner_type'   => 'individu',
            ]);
        }

        return $errors;
    }

    public function logout(?string $param = null): void {
        destroySession();
        redirect('auth/login');
    }

    private function redirectByRole(): never {
        $role = getSessionRole();
        match ($role) {
            'pelajar' => redirect('student/dashboard'),
            'pemilik' => redirect('owner/dashboard'),
            'admin'   => redirect('admin/dashboard'),
            default   => redirect('auth/login'),
        };
    }
}
