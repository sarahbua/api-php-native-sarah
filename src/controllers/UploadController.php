<?php
namespace Src\Controllers;

use Src\Helpers\Response;

class UploadController extends BaseController
{
    public function store()
    {
        // Pastikan request bukan JSON
        if ((($_SERVER['CONTENT_TYPE'] ?? '') && str_contains($_SERVER['CONTENT_TYPE'], 'application/json'))) {
            return $this->error(415, 'Use multipart/form-data for upload');
        }

        // Cek apakah file dikirim
        if (empty($_FILES['file'])) {
            return $this->error(422, 'file is required');
        }

        $f = $_FILES['file'];

        // Cek apakah upload berhasil
        if ($f['error'] !== UPLOAD_ERR_OK) {
            return $this->error(400, 'Upload error');
        }

        // Batas ukuran file 2MB
        if ($f['size'] > 2 * 1024 * 1024) {
            return $this->error(422, 'Max 3MB');
        }

        // Ambil MIME type
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($f['tmp_name']);

        // Format file yang diizinkan
        $allowed = [
            'image/png' => 'png',
            'image/jpeg' => 'jpg',
            'application/pdf' => 'pdf'
        ];

        // Validasi MIME
        if (!isset($allowed[$mime])) {
            return $this->error(422, 'Invalid mime type');
        }

        // Nama file acak + ekstensi
        $name = bin2hex(random_bytes(8)) . '.' . $allowed[$mime];

        // Folder tujuan: public/uploads/
        $uploadDir = __DIR__ . '/../../public/uploads/';

        // Buat folder jika belum ada
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Lokasi tujuan
        $dest = $uploadDir . $name;

        // Pindahkan file dari tmp ke folder tujuan
        if (!move_uploaded_file($f['tmp_name'], $dest)) {
            return $this->error(500, 'Save failed');
        }

        // URL file (akses langsung lewat browser)
        $baseUrl = sprintf(
            "%s://%s",
            isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http',
            $_SERVER['HTTP_HOST']
        );

        $fileUrl = $baseUrl . '/api_php_native_sarahh/public/uploads/' . $name;

        // Respons sukses
        return $this->ok([
            'message' => 'Upload success',
            'file_name' => $name,
            'mime_type' => $mime,
            'path' => "/uploads/$name",
            'url' => $fileUrl
        ], 201);
    }
}
