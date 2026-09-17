<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/User.php';

class AuthController {
    private User $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function register(): void {
        if (isset($_SESSION['user_id'])) {
            header("Location: index.php");
            exit;
        }

        $errorMessage = '';
        $successMessage = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_register'])) {
            try {
                $username = trim((string) ($_POST['username'] ?? ''));
                $email = trim((string) ($_POST['email'] ?? ''));
                $password = (string) ($_POST['password'] ?? '');
                $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

                if (empty($username) || empty($email) || empty($password)) {
                    throw new Exception("All fields are required.");
                }

                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    throw new Exception("Please provide a valid email address.");
                }

                if ($password !== $confirmPassword) {
                    throw new Exception("Passwords do not match.");
                }

                if (strlen($password) < 6) {
                    throw new Exception("Password must be at least 6 characters long.");
                }

                $this->userModel->create($username, $email, $password, 'user');
                $successMessage = "Account created successfully! You can now log in.";
            } catch (Exception $e) {
                $errorMessage = $e->getMessage();
            }
        }

        require_once __DIR__ . '/../views/auth/register.php';
    }

    public function login(): void {
        if (isset($_SESSION['user_id'])) {
            header("Location: index.php");
            exit;
        }

        $errorMessage = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_login'])) {
            try {
                $email = trim((string) ($_POST['email'] ?? ''));
                $password = (string) ($_POST['password'] ?? '');

                if (empty($email) || empty($password)) {
                    throw new Exception("Email and password are required.");
                }

                $user = $this->userModel->findByEmail($email);

                if (!$user || !$this->userModel->verifyPassword($password, $user['password_hash'])) {
                    throw new Exception("Invalid email or password.");
                }

                // Prevent Session Fixation
                session_regenerate_id(true);

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['profile_pic'] = $user['profile_pic'] ?? null;

                // Redirect based on role
                if ($user['role'] === 'admin') {
                    header("Location: index.php?page=admin");
                } else {
                    header("Location: index.php");
                }
                exit;
            } catch (Exception $e) {
                $errorMessage = $e->getMessage();
            }
        }

        require_once __DIR__ . '/../views/auth/login.php';
    }

    public function logout(): void {
        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        session_destroy();
        header("Location: index.php?page=login");
        exit;
    }
}