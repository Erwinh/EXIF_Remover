<?php

namespace EXIF_Remover\Classes;

use EXIF_Remover\Models\AbstractFile;

/**
 * Created by PhpStorm.
 * User: Erwin
 * Date: 12/8/2018
 * Time: 20:46
 */
class StorageManager {

    /**
     * @var string
     */
    private $token;

    /**
     * @var array
     */
    private $files = [];


    /**
     * StorageManager constructor.
     */
    public function __construct() {
        if (!isset($_SESSION['token']) || $_SESSION['token'] === null) {
            $this->token = md5(uniqid() . microtime());
            $_SESSION['token'] = $this->token;
        } else {
            $this->token = $_SESSION['token'];
        }

        if (isset($_SESSION['files']) && !empty($_SESSION['files'])) {
            $this->files = $_SESSION['files'];
        }

    }

    /**
     * @return string
     */
    public function getToken() {
        return $this->token;
    }


    /**
     * @return array
     */
    public function getFiles() {
        return array_reverse($this->files);
    }


    /**
     * @param AbstractFile $file
     * @return StorageManager
     */
    public function addFile(AbstractFile $file) {
        $this->files[$file->getId()] = $file;

        return $this;
    }


    /**
     * @param $key
     * @return StorageManager
     */
    public function removeFile($key) {
        if (isset($this->files[$key])) {
            unset($this->files[$key]);
        }

        return $this;
    }

    /**
     * @param $key
     * @return AbstractFile|null
     */
    public function getFile($key) {
        if (isset($this->files[$key])) {
            return $this->files[$key];
        }

        return null;
    }

    public function save() {
        $_SESSION['files'] = $this->files;
    }

}