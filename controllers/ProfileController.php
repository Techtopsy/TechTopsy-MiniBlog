<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/User.php';

class ProfileController {
    private User $userModel;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?page=login");
            exit;
        }
        $this->userModel = new User();
    }

    public function index(): void {
        $userId = (int) $_SESSION['user_id'];
        $errorMessage = '';
        $successMessage = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_avatar'])) {
            try {
                if (!isset($_FILES['avatar'])) {
                    throw new Exception("Please select a valid image file.");
                }

                $file = $_FILES['avatar'];
                if ($file['error'] !== UPLOAD_ERR_OK) {
                    throw new Exception("Please select a valid image file.");
                }

                $maxSizeBytes = 5 * 1024 * 1024; // Allow up to 5MB initial upload (will be compressed)

                if ($file['size'] > $maxSizeBytes) {
                    throw new Exception("Original file size exceeds maximum allowed limit of 5MB.");
                }

                // Validate File Type via MIME (with safe fallback when fileinfo is unavailable)
                $mimeType = $this->detectMimeType($file);
                $allowedMimes = [
                    'image/jpeg' => 'jpg',
                    'image/png'  => 'png',
                    'image/webp' => 'webp'
                ];

                if (!array_key_exists($mimeType, $allowedMimes)) {
                    throw new Exception("Invalid image format. Only JPG, PNG, and WebP are allowed.");
                }

                // Generate Unique Filename
                $extension = $allowedMimes[$mimeType];
                $newFilename = 'avatar_' . $userId . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
                $uploadDir = __DIR__ . '/../Public/uploads/avatars/';

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $destinationPath = $uploadDir . $newFilename;

                // Process image: crop/resize if GD is available, otherwise save as-is.
                if (extension_loaded('gd')) {
                    $this->processAndCompressImage($file['tmp_name'], $destinationPath, $mimeType, 300);
                } else {
                    $this->storeUploadedFile($file['tmp_name'], $destinationPath);
                }

                // Delete old avatar if it exists
                $currentUser = $this->userModel->findById($userId);
                if (!empty($currentUser['profile_pic'])) {
                    $oldFilePath = $uploadDir . $currentUser['profile_pic'];
                    if (file_exists($oldFilePath)) {
                        unlink($oldFilePath);
                    }
                }

                $this->userModel->updateProfilePic($userId, $newFilename);
                $_SESSION['profile_pic'] = $newFilename;
                $successMessage = "Profile picture cropped, resized, and updated successfully!";
            } catch (Exception $e) {
                $errorMessage = $e->getMessage();
            }
        }

        $user = $this->userModel->findById($userId);
        require_once __DIR__ . '/../views/user/profile.php';
    }

    private function detectMimeType(array $file): string {
        if (class_exists('finfo')) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $detected = $finfo->file($file['tmp_name']);
            if (is_string($detected) && $detected !== '') {
                return $detected;
            }
        }

        if (!empty($file['type']) && is_string($file['type'])) {
            $type = strtolower($file['type']);
            if (in_array($type, ['image/jpeg', 'image/png', 'image/webp'], true)) {
                return $type;
            }
        }

        $extension = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
        $map = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
        ];

        if (isset($map[$extension])) {
            return $map[$extension];
        }

        return '';
    }

    private function storeUploadedFile(string $sourcePath, string $destPath): void {
        if (!is_uploaded_file($sourcePath)) {
            if (!@move_uploaded_file($sourcePath, $destPath)) {
                throw new Exception("Failed to save uploaded file.");
            }
            return;
        }

        if (!@move_uploaded_file($sourcePath, $destPath)) {
            throw new Exception("Failed to save uploaded file.");
        }
    }

    /**
     * Center-crops, resizes, and compresses an image using PHP GD.
     */
    private function processAndCompressImage(string $sourcePath, string $destPath, string $mimeType, int $targetSize = 300): void {
        // 1. Create GD Resource from Source
        switch ($mimeType) {
            case 'image/jpeg':
                $sourceImage = @imagecreatefromjpeg($sourcePath);
                break;
            case 'image/png':
                $sourceImage = @imagecreatefrompng($sourcePath);
                break;
            case 'image/webp':
                $sourceImage = @imagecreatefromwebp($sourcePath);
                break;
            default:
                throw new Exception("Unsupported image format for GD processing.");
        }

        if (!$sourceImage) {
            throw new Exception("Corrupted or invalid image file.");
        }

        $origWidth = imagesx($sourceImage);
        $origHeight = imagesy($sourceImage);

        // 2. Calculate Center-Crop Coordinates (1:1 Aspect Ratio)
        $cropSize = min($origWidth, $origHeight);
        $srcX = (int) (($origWidth - $cropSize) / 2);
        $srcY = (int) (($origHeight - $cropSize) / 2);

        // 3. Create Truecolor Target Canvas
        $targetCanvas = imagecreatetruecolor($targetSize, $targetSize);

        // 4. Preserve Transparency for PNG and WebP
        if ($mimeType === 'image/png' || $mimeType === 'image/webp') {
            imagealphablending($targetCanvas, false);
            imagesavealpha($targetCanvas, true);
            $transparent = imagecolorallocatealpha($targetCanvas, 255, 255, 255, 127);
            imagefilledrectangle($targetCanvas, 0, 0, $targetSize, $targetSize, $transparent);
        }

        // 5. Resample and Resize
        imagecopyresampled(
            $targetCanvas,
            $sourceImage,
            0, 0,                  // Target X, Y
            $srcX, $srcY,          // Source X, Y (Centered crop)
            $targetSize, $targetSize,
            $cropSize, $cropSize
        );

        // 6. Save Compressed File to Destination
        $saved = false;
        switch ($mimeType) {
            case 'image/jpeg':
                $saved = imagejpeg($targetCanvas, $destPath, 85); // 85% Quality Compression
                break;
            case 'image/png':
                $saved = imagepng($targetCanvas, $destPath, 6);  // Level 6 Compression (0-9)
                break;
            case 'image/webp':
                $saved = imagewebp($targetCanvas, $destPath, 80); // 80% Quality Compression
                break;
        }

        // Clean up memory
        imagedestroy($sourceImage);
        imagedestroy($targetCanvas);

        if (!$saved) {
            throw new Exception("Failed to save the compressed image.");
        }
    }
}