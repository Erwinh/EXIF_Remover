<?php
/**
 * Created by PhpStorm.
 * User: Erwin
 * Date: 12/8/2018
 * Time: 20:50
 */


namespace EXIF_Remover\Models;

use PHPExif\Exif;

class AbstractFile {

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $fileName;

    /**
     * @var string
     */
    private $originalFileName;

    /**
     * @var Exif|boolean
     */
    private $exifData;

    /**
     * @var string
     */
    private $type;

    /**
     * @var integer
     */
    private $size;

    /**
     * @var \DateTime
     */
    private $date;

    /**
     * @var array
     */
    private $dimensions;

    /**
     * @var string
     */
    private $uploadPath;

    /**
     * @var string
     */
    private $downloadPath;

    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param int $id
     * @return AbstractFile
     */
    public function setId($id) {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getFileName() {
        return $this->fileName;
    }

    /**
     * @param string $fileName
     * @return AbstractFile
     */
    public function setFileName($fileName) {
        $this->fileName = $fileName;

        return $this;
    }

    /**
     * @return string
     */
    public function getOriginalFileName() {
        return $this->originalFileName;
    }

    /**
     * @param string $originalFileName
     * @return AbstractFile
     */
    public function setOriginalFileName($originalFileName) {
        $this->originalFileName = $originalFileName;

        return $this;
    }

    /**
     * @return string
     */
    public function getUploadPath() {
        return $this->uploadPath;
    }

    /**
     * @param string $uploadPath
     * @return AbstractFile
     */
    public function setUploadPath($uploadPath) {
        $this->uploadPath = $uploadPath;

        return $this;
    }

    /**
     * @return string
     */
    public function getDownloadPath() {
        return $this->downloadPath;
    }

    /**
     * @param string $downloadPath
     * @return AbstractFile
     */
    public function setDownloadPath($downloadPath) {
        $this->downloadPath = $downloadPath;

        return $this;
    }

    /**
     * @return string
     */
    public function getType() {
        return $this->type;
    }

    /**
     * @param string $type
     * @return AbstractFile
     */
    public function setType($type) {
        $this->type = $type;

        return $this;
    }

    /**
     * @return int
     */
    public function getSize() {
        return $this->size;
    }

    /**
     * @param int $size
     * @return AbstractFile
     */
    public function setSize($size) {
        $this->size = $size;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDate() {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     * @return AbstractFile
     */
    public function setDate($date) {
        $this->date = $date;

        return $this;
    }

    /**
     * @param null $type
     * @return string|array
     */
    public function getDimensions($type = null) {
        if ($type !== null) {
            if ($type === 'width') {
                return $this->dimensions[0];
            } else if ($type === 'height') {
                return $this->dimensions[1];
            }
        }

        return $this->dimensions;
    }

    /**
     * @param array $dimensions
     * @return AbstractFile
     */
    public function setDimensions($dimensions) {
        $this->dimensions = $dimensions;

        return $this;
    }

    /**
     * @return Exif|boolean
     */
    public function getExifData() {
        return $this->exifData;
    }

    /**
     * @param Exif|boolean $exifData
     * @return AbstractFile
     */
    public function setExifData($exifData) {
        $this->exifData = $exifData;

        return $this;
    }


    /**
     * @return array
     */
    public function toViewArray() {
        $dimensions = '%s by %s pixels';

        return [
            'id'         => $this->getId(),
            'name'       => $this->getFileName(),
            'path'       => $this->getDownloadPath(),
            'size'       => $this->humanFilesize($this->getSize()),
            'date'       => $this->getDate()->format('l jS \of F Y H:i:s'),
            'type'       => $this->getType(),
            'dimensions' => sprintf($dimensions, $this->getDimensions('width'), $this->getDimensions('height')),
            'exifData'   => ($this->getExifData() !== false) ? (array)$this->getExifData()->getData() : []
        ];
    }

    private function humanFilesize($bytes, $decimals = 0) {
        $sz = 'BKMGTP';
        $factor = floor((strlen($bytes) - 1) / 3);

        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
    }

}