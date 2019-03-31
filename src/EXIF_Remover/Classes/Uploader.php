<?php
/**
 * Created by PhpStorm.
 * User: Erwin
 * Date: 9/8/2018
 * Time: 22:17
 */

namespace EXIF_Remover\Classes;

use EXIF_Remover\Models\Image;
use EXIF_Remover\Models\Video;

class Uploader {

    /**
     * @var string
     */
    private $uploadPath;

    /**
     * @var string
     */
    private $downloadPath;

    /**
     * @var string
     */
    private $rawUploadPath;

    /**
     * @var string
     */
    private $clearUploadPath;

    /**
     * @var string
     */
    private $clearDownloadPath;

    /**
     * @var StorageManager
     */
    private $storageManager;

    private $file;


    /**
     * Uploader constructor.
     *
     * @param string $uploadPath
     * @param string $downloadPath
     * @param string $token
     */
    public function __construct($uploadPath = '/var/www/uploads/', $downloadPath = '/uploads/', $token = '') {
        $this->uploadPath = $uploadPath;
        $this->downloadPath = $downloadPath;

        $this->rawUploadPath = $this->uploadPath . 'raw' . DIRECTORY_SEPARATOR . $token . DIRECTORY_SEPARATOR;
        $this->clearUploadPath = $this->uploadPath . 'clear' . DIRECTORY_SEPARATOR . $token . DIRECTORY_SEPARATOR;

        $this->clearDownloadPath = $this->downloadPath . 'clear' . DIRECTORY_SEPARATOR . $token . DIRECTORY_SEPARATOR;

        $this->storageManager = new StorageManager();

        $this->checkFolders();
    }

    /**
     * @param $file
     * @return array
     */
    public function uploadFile($file) {

        $this->file = $file;

        if ($this->validateFile()) {

            $fileName = $this->createFilename($this->file['name']);
            $mime = mime_content_type($file['tmp_name']);

            if (strstr($mime, "video/")) {
                $fileObject = new Video();
                $fileObject->setFileName($fileName);
                $fileObject->setOriginalFileName($fileName);

                $this->saveVideo($fileObject, $file);
            } elseif (strstr($mime, "image/")) {
                $fileObject = new Image();
                $fileObject->setFileName($fileName);
                $fileObject->setOriginalFileName($fileName);

                $this->saveImage($fileObject, $file);
            } else {
                throw new \RuntimeException('Invalid file type.');
            }

            if ($fileObject->getId() === null) {
                $fileObject->setId(uniqid('file_'));
            }
            $this->storageManager->addFile($fileObject)->save();

            return [
                'status' => 'ok',
                'file'   => $fileObject->toViewArray()
            ];
        }

        throw new \RuntimeException('Invalid file.');
    }

    /**
     * @param $id
     * @param $file
     * @return array
     */
    public function cropImage($id, $file) {

        $this->file = $file;

        if ($this->validateFile()) {

            $fileObject = $this->storageManager->getFile($id);

            $fileName = $this->createFilename($fileObject->getOriginalFileName(), '_cropped');
            $mime = mime_content_type($file['tmp_name']);


            $fileObject->setFileName($fileName);

            if (strstr($mime, "video/")) {
                $this->saveVideo($fileObject, $file);
            } elseif (strstr($mime, "image/")) {
                $this->saveImage($fileObject, $file);
            } else {
                throw new \RuntimeException('Invalid file type.');
            }

            $this->storageManager->addFile($fileObject)->save();

            return [
                'status' => 'ok',
                'file'   => $fileObject->toViewArray(),
            ];
        }

        throw new \RuntimeException('Invalid file.');
    }

    /**
     * @return bool
     */
    private function validateFile() {
        if (!isset($this->file['error']) || is_array($this->file['error'])) {
            throw new \RuntimeException('Invalid parameters.');
        }

        switch ($this->file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                throw new \RuntimeException('No file sent.');
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                throw new \RuntimeException('Exceeded filesize limit.');
            default:
                throw new \RuntimeException('Unknown errors.');
        }

        return true;
    }

    /**
     * @param      $fileName
     * @param null $postFix
     * @return string
     */
    private function createFilename($fileName, $postFix = null) {
        $filePath = $this->rawUploadPath;

        $name = pathinfo($fileName, PATHINFO_FILENAME);
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);

        if ($postFix !== null) {
            $fileName = $name . $postFix . '.' . $extension;
        }

        $file = $filePath . $fileName;

        if (file_exists($file)) {

            $i = 1;

            while (file_exists($file)) {
                if ($postFix !== null) {
                    $fileName = $name . $postFix . '(' . $i . ').' . $extension;
                } else {
                    $fileName = $name . '(' . $i . ').' . $extension;
                }

                $file = $filePath . $fileName;

                $i++;
            }
        }

        return $fileName;
    }

    /**
     * @param $image
     * @param $mimeContentType
     * @return resource
     */
    private function cleanImage($image, $mimeContentType) {
        switch ($mimeContentType) {
            case 'image/gif':
                $img = imagecreatefromgif($image);

                break;
            case 'image/jpeg':
                $img = imagecreatefromjpeg($image);

                break;
            case 'image/png':
                $img = imagecreatefrompng($image);

                break;
            case 'image/bmp':
                $img = imagecreatefrombmp($image);

                break;

            default:
                throw new \RuntimeException('Unknown image type ' . $mimeContentType);
        }

        return $img;
    }

    /**
     * @param Video $video
     * @param array $rawImage
     * @return Video
     */
    private function saveVideo(Video $video, $rawImage) {
        throw new \RuntimeException('Not implemented yet . ');
        $filePath = $this->rawUploadPath . $video->getFileName();

        // save origional image
        if (!move_uploaded_file($this->file['tmp_name'], $filePath)) {
            throw new \RuntimeException('Failed to move uploaded file . ');
        }

        $clearFilePath = $this->clearUploadPath . $video->getFileName();
        $clearDownloadPath = $this->clearDownloadPath . $video->getFileName();

        copy($filePath, $clearFilePath);

        $video = new Video();
        $video
            ->setDownloadPath($clearDownloadPath)
            ->setUploadPath($filePath);

        return $video;
    }

    /**
     * @param Image $image
     * @param array $rawImage
     * @return Image
     */
    private function saveImage(Image $image, $rawImage) {
        $mimeContentType = mime_content_type($rawImage['tmp_name']);
        $img = $this->cleanImage($rawImage['tmp_name'], $mimeContentType);

        $filePath = $this->rawUploadPath . $image->getFileName();

        // save origional image
        if (!move_uploaded_file($this->file['tmp_name'], $filePath)) {
            throw new \RuntimeException('Failed to move uploaded file . ');
        }

        $bg = imagecreatetruecolor(imagesx($img), imagesy($img));
        imagefill($bg, 0, 0, imagecolorallocate($bg, 255, 255, 255));
        imagealphablending($bg, TRUE);
        imagecopy($bg, $img, 0, 0, 0, 0, imagesx($img), imagesy($img));
        imagedestroy($img);

        $clearFilePath = $this->clearUploadPath . $image->getFileName();
        $clearDownloadPath = $this->clearDownloadPath . $image->getFileName();

        switch ($mimeContentType) {
            case 'image/gif':
                copy($filePath, $clearFilePath);

                break;
            case 'image/jpeg':
                imagejpeg($bg, $clearFilePath, 100);

                break;
            case 'image/png':
                imagepng($bg, $clearFilePath);

                break;
            case 'image/bmp':
                imagebmp($bg, $clearFilePath);

                break;

            default:
                throw new \RuntimeException('Unknown image type ' .$mimeContentType);
        }

        imagedestroy($bg);


        $imageInfo = getimagesize($clearFilePath);

        $reader = \PHPExif\Reader\Reader::factory(\PHPExif\Reader\Reader::TYPE_NATIVE);
        $exif = $reader->read($filePath);


        $image
            ->setDownloadPath($clearDownloadPath)
            ->setUploadPath($clearFilePath)
            ->setSize(filesize($clearFilePath))
            ->setDimensions([$imageInfo[0], $imageInfo[1]])
            ->setType($imageInfo['mime'])
            ->setDate(new \DateTime())
            ->setExifData($exif);

        return $image;
    }

    private function checkFolders() {

        if (!file_exists($this->rawUploadPath)) {
            mkdir($this->rawUploadPath, 0777, true);
        }

        if (!file_exists($this->clearUploadPath)) {
            mkdir($this->clearUploadPath, 0777, true);
        }
    }
}