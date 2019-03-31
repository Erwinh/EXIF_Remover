<?php

use EXIF_Remover\Classes\StorageManager;
use EXIF_Remover\Classes\Uploader;

ini_set('memory_limit', '-1');

require_once ('vendor/autoload.php');

session_start();

header('Content-type:application/json;charset=utf-8');


$storageManager = new StorageManager();

$uploadPath = __DIR__ . '/uploads/';
$downloadPath = dirname($_SERVER['REQUEST_URI']) . '/uploads/';

$uploader = new Uploader($uploadPath, $downloadPath, $storageManager->getToken());


$action = 'upload';

if (isset($_REQUEST['action']) && $_REQUEST['action'] !== '') {
    $action = $_REQUEST['action'];
}

try {

    if ($action === 'upload') {
        $response = $uploader->uploadFile($_FILES['file']);
    } elseif ($action === 'crop') {
        $response = $uploader->cropImage($_REQUEST['id'], $_FILES['file']);
    } elseif ($action === 'delete') {
        $storageManager->removeFile($_REQUEST['id'])->save();
        $response = [];
    }

    echo json_encode($response);

} catch (RuntimeException $e) {
    http_response_code(400);

    echo json_encode([
        'status'  => 'error',
        'message' => $e->getMessage()
    ]);
}

