<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/Subscription.php';

class SubscriptionController {
    private Subscription $subscriptionModel;
    private const PRICE_NAIRA = 3000;
    private const CURRENCY = 'NGN';

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?page=login");
            exit;
        }
        $this->subscriptionModel = new Subscription();
    }

    public function pricing(): void {
        $userId = (int) $_SESSION['user_id'];
        $isSubscribed = $this->subscriptionModel->hasActiveSubscription($userId);

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        $csrfToken = $_SESSION['csrf_token'];
        
        $errorMessage = $_SESSION['paystack_error'] ?? '';
        $successMessage = $_SESSION['success_message'] ?? '';
        unset($_SESSION['paystack_error'], $_SESSION['success_message']);

        require_once __DIR__ . '/../views/subscription/pricing.php';
    }

    public function initialize(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$this->validCsrfToken()) {
            http_response_code(403);
            exit('Invalid payment request. Refresh the page and try again.');
        }

        $userId = (int) $_SESSION['user_id'];
        $email = (string) ($_SESSION['email'] ?? '');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->redirectWithError('A valid email address is required for payment.');
        }

        if ($this->subscriptionModel->hasActiveSubscription($userId)) {
            $_SESSION['success_message'] = 'Your Premium Subscription is already active.';
            header('Location: index.php?page=pricing');
            exit;
        }

        $amount = self::PRICE_NAIRA * 100;
        $reference = 'SUB_' . $userId . '_' . bin2hex(random_bytes(12));
        if (!$this->subscriptionModel->createPending($userId, $reference, self::PRICE_NAIRA)) {
            $this->redirectWithError('Could not create a payment record. Please try again.');
        }

        $fields = [
            'email' => $email,
            'amount' => $amount,
            'reference' => $reference,
            'currency' => self::CURRENCY,
            'callback_url' => $this->callbackUrl(),
        ];

        $tranx = $this->paystackRequest('POST', 'transaction/initialize', $fields);

        if (($tranx['status'] ?? false) === true && !empty($tranx['data']['authorization_url'])) {
            header("Location: " . $tranx['data']['authorization_url']);
            exit;
        }

        $this->subscriptionModel->failPending($userId, $reference);
        $this->redirectWithError('Paystack could not initialize payment. Check the server payment configuration and try again.');
    }

    public function verify(): void {
        $reference = trim((string) ($_GET['reference'] ?? ''));
        $userId = (int) $_SESSION['user_id'];
        $payment = $reference !== '' ? $this->subscriptionModel->findByReference($reference) : false;

        if (!$payment || (int) $payment['user_id'] !== $userId) {
            $this->redirectWithError('Payment reference was not found for your account.');
        }

        $tranx = $this->paystackRequest('GET', 'transaction/verify/' . rawurlencode($reference));
        $data = $tranx['data'] ?? [];
        $isValidPayment = ($tranx['status'] ?? false) === true
            && ($data['status'] ?? '') === 'success'
            && hash_equals($reference, (string) ($data['reference'] ?? ''))
            && (int) ($data['amount'] ?? 0) === self::PRICE_NAIRA * 100
            && strtoupper((string) ($data['currency'] ?? '')) === self::CURRENCY;

        if ($isValidPayment && $this->subscriptionModel->activatePayment($userId, $reference)) {
            $_SESSION['success_message'] = "Payment successful! Your Premium Subscription is now active.";
        } else {
            $verifiedReference = (string) ($data['reference'] ?? '');
            if (($tranx['status'] ?? false) === true
                && hash_equals($reference, $verifiedReference)
                && in_array(($data['status'] ?? ''), ['failed', 'abandoned'], true)) {
                $this->subscriptionModel->failPending($userId, $reference);
            }
            $_SESSION['paystack_error'] = 'Payment is not verified yet. If your account was charged, retry from this page or contact support.';
        }

        header("Location: index.php?page=pricing");
        exit;
    }

    private function paystackRequest(string $method, string $endpoint, ?array $fields = null): array {
        $secretKey = $this->paystackSecretKey();
        if ($secretKey === null || !function_exists('curl_init')) {
            error_log('Paystack is not configured or PHP cURL is unavailable.');
            return [];
        }

        $handle = curl_init('https://api.paystack.co/' . $endpoint);
        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $secretKey,
                'Cache-Control: no-cache',
                'Content-Type: application/json',
            ],
        ];

        if ($method === 'POST') {
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = json_encode($fields, JSON_THROW_ON_ERROR);
        }

        curl_setopt_array($handle, $options);
        $response = curl_exec($handle);
        $statusCode = (int) curl_getinfo($handle, CURLINFO_HTTP_CODE);
        if ($response === false) {
            error_log('Paystack request failed: ' . curl_error($handle));
            curl_close($handle);
            return [];
        }
        curl_close($handle);

        $result = json_decode($response, true);
        if ($statusCode < 200 || $statusCode >= 300 || !is_array($result)) {
            error_log('Paystack returned an invalid response with HTTP status ' . $statusCode . '.');
            return [];
        }
        return $result;
    }

    private function paystackSecretKey(): ?string {
        $secretKey = getenv('PAYSTACK_SECRET_KEY') ?: '';
        $envPath = dirname(__DIR__) . '/.env';
        if ($secretKey === '' && is_file($envPath)) {
            $env = parse_ini_file($envPath, false, INI_SCANNER_RAW) ?: [];
            $secretKey = (string) ($env['PAYSTACK_SECRET_KEY'] ?? '');
        }
        return str_starts_with($secretKey, 'sk_') && !str_contains($secretKey, 'replace_with_your_paystack_secret_key')
            ? $secretKey
            : null;
    }

    private function callbackUrl(): string {
        $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        $scheme = $https ? 'https' : 'http';
        $host = (string) ($_SERVER['SERVER_NAME'] ?? 'localhost');
        $script = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
        return $scheme . '://' . $host . $script . '?page=verify_payment';
    }

    private function validCsrfToken(): bool {
        $submitted = (string) ($_POST['csrf_token'] ?? '');
        $stored = (string) ($_SESSION['csrf_token'] ?? '');
        return $submitted !== '' && $stored !== '' && hash_equals($stored, $submitted);
    }

    private function redirectWithError(string $message): never {
        $_SESSION['paystack_error'] = $message;
        header('Location: index.php?page=pricing');
        exit;
    }
}