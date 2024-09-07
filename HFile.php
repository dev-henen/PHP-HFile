<?php

namespace HFiles;

use Exception;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use InvalidArgumentException;

final class HFile {
    private $target_dir;
    private $tmp_dir;
    private $file;
    public $target_file;
    public $file_type;
    public $check;
    public $mime;
    public $size;
    public $name;
    public $auto_generated_name;
    public $generated_name;
    public $file_new_name;
    private $min_compress_size;
    private $error_code;
    private $error_message;

    public function __construct($file = null, $tmp_dir = 'tmp/') {
        // Initialize properties
        $this->file = $file;
        $this->size = 0;
        $this->name = '';
        
        // Ensure tmp_dir ends with a slash
        $this->tmp_dir = rtrim($tmp_dir, '/') . '/';
        
        // Create the temporary directory if it doesn't exist
        if (!is_dir($this->tmp_dir)) {
            mkdir($this->tmp_dir, 0755, true);
        }
        
        // Check the type of $file and handle accordingly
        if (is_null($file)) {
            // Handle null case
            $this->name = 'No file provided';
        } elseif (is_array($file) && isset($file["name"]) && isset($file["size"])) {
            // Handle $_FILES array
            $this->size = $file["size"];
            $this->name = htmlspecialchars(basename($file["name"]));
            $this->target_file = $this->tmp_dir . uniqid() . "_" . $this->name;
        } elseif (is_file($file)) {
            // Handle actual file path
            $this->size = filesize($file);
            $this->name = htmlspecialchars(basename($file));
        } elseif (is_dir($file)) {
            // Handle directory
            $this->name = htmlspecialchars(basename($file));
            $this->size = $this->getDirectorySize($file); // Custom method to calculate directory size
        } else {
            // Handle invalid input
            throw new InvalidArgumentException('Invalid file input');
        }
    }
    
    // Custom method to calculate the size of a directory
    private function getDirectorySize($directory) {
        $size = 0;
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory)) as $file) {
            $size += $file->getSize();
        }
        return $size;
    }
    
    public function set_folder($x, $categorize_by_year = false) {
        if ($categorize_by_year) {
            $year = date("Y");
            $this->target_dir = rtrim($x, '/') . '/' . $year . '/';
        } else {
            $this->target_dir = rtrim($x, '/') . '/';
        }

        if (!is_dir($this->target_dir)) {
            mkdir($this->target_dir, 0755, true);
        }
        
        $this->file_type = strtolower(pathinfo($this->target_file, PATHINFO_EXTENSION));
    }

    private function generate_unique_name() {
        return uniqid() . "_" . $this->name;
    }

    public function is_existing() {
        return file_exists($this->target_file);
    }

    public function max_file_size($size) {
        return $this->size > $size;
    }

    public function permit($arr) {
        return in_array($this->file_type, (array)$arr);
    }

    public function move_to_folder() {
        $final_target_file = $this->target_dir . basename($this->target_file);
        if (!is_dir(dirname($final_target_file))) {
            mkdir(dirname($final_target_file), 0755, true);
        }
        if (!file_exists($this->target_file)) {
            return false;
        }
        if (rename($this->target_file, $final_target_file)) {
            $this->target_file = $final_target_file;
            return true;
        }
        return false;
    }

    public function rename($name = null, $type = null) {
        $type = $type ? strtolower($type) : $this->file_type;
        if (!$name) {
            $name = $this->generate_random_name();
        }
        $this->file_new_name = $name . '.' . $type;
        $this->auto_generated_name = $this->tmp_dir . $this->file_new_name;

        if (rename($this->target_file, $this->auto_generated_name)) {
            $this->target_file = $this->auto_generated_name;
            $this->name = $this->file_new_name;
            return true;
        }
        return false;
    }

    private function generate_random_name() {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_';
        $new_name = '';
        for ($i = 0; $i < 12; $i++) {
            $new_name .= $characters[rand(0, strlen($characters) - 1)];
        }
        $new_name .= '_' . time();
        $new_name .= '_' . str_replace([' ', '.'], '', microtime());
        $new_name .= '_' . date('dmY');
        return $new_name;
    }

    public function is_image() {
        if (file_exists($this->file["tmp_name"])) {
            $this->check = getimagesize($this->file["tmp_name"]);
            return $this->check !== false;
        }
        return false;
    }

    public function is_video() {
        if (file_exists($this->file["tmp_name"])) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $this->file["tmp_name"]);
            finfo_close($finfo);
            return strstr($mime_type, "video/") !== false;
        }
        return false;
    }

    public function is_audio() {
        if (file_exists($this->file["tmp_name"])) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $this->file["tmp_name"]);
            finfo_close($finfo);
            return strstr($mime_type, "audio/") !== false;
        }
        return false;
    }

    public function compress_image($quality, $min_size = 0) {
        if ($this->is_image() && $this->size > $min_size) {
            $info = getimagesize($this->file["tmp_name"]);
            $image = null;

            switch ($info['mime']) {
                case 'image/jpeg':
                    $image = imagecreatefromjpeg($this->file["tmp_name"]);
                    break;
                case 'image/gif':
                    $image = imagecreatefromgif($this->file["tmp_name"]);
                    break;
                case 'image/png':
                    $image = imagecreatefrompng($this->file["tmp_name"]);
                    break;
                case 'image/webp':
                    $image = imagecreatefromwebp($this->file["tmp_name"]);
                    break;
                default:
                    throw new Exception('Unsupported image type');
            }

            if ($image) {
                imagejpeg($image, $this->target_file, $quality);
                imagedestroy($image);
                return true;
            }
        }
        return false;
    }

    public function compress_video($rate, $min_size = 0) {
        if ($this->is_video() && $this->size > $min_size) {
            $output_file = $this->tmp_dir . pathinfo($this->name, PATHINFO_FILENAME) . "_compressed." . $this->file_type;
            $ffmpeg = "/usr/bin/ffmpeg"; // Path to ffmpeg binary
            $command = "$ffmpeg -i {$this->file["tmp_name"]} -b:v $rate {$output_file}";

            exec($command, $output, $return_var);
            if ($return_var == 0) {
                $this->auto_generated_name = $output_file;
                return true;
            }
        }
        return false;
    }

    public function extract_video_frame($index) {
        if ($this->is_video()) {
            $frame_file = $this->tmp_dir . pathinfo($this->name, PATHINFO_FILENAME) . "_frame_" . $index . ".jpg";
            $ffmpeg = "/usr/bin/ffmpeg"; // Path to ffmpeg binary
            $command = "$ffmpeg -i {$this->file["tmp_name"]} -vf 'select=eq(n\\,$index)' -vsync vfr {$frame_file}";

            exec($command, $output, $return_var);
            if ($return_var == 0) {
                return $frame_file;
            }
        }
        return false;
    }

    public function lock() {
        if (file_exists($this->target_file)) {
            $fp = fopen($this->target_file, "r+");
            if (flock($fp, LOCK_EX)) {
                return true;
            }
            fclose($fp);
        }
        return false;
    }

    public function unlock() {
        if (file_exists($this->target_file)) {
            $fp = fopen($this->target_file, "r+");
            if (flock($fp, LOCK_UN)) {
                fclose($fp);
                return true;
            }
            fclose($fp);
        }
        return false;
    }

    public function get_file_size() {
        return $this->size;
    }

    public function get_modify_time() {
        return filemtime($this->file["tmp_name"]);
    }

    public function get_state() {
        return [
            'file_size' => $this->get_file_size(),
            'modify_time' => $this->get_modify_time(),
            'is_image' => $this->is_image(),
            'is_video' => $this->is_video(),
            'is_audio' => $this->is_audio(),
        ];
    }

    public function remove() {
        if (file_exists($this->target_file)) {
            return unlink($this->target_file);
        }
        return false;
    }

    public function move_from($dir1, $dir2) {
        $src = rtrim($dir1, '/') . '/' . $this->name;
        $dest = rtrim($dir2, '/') . '/' . $this->name;

        if (!is_dir(dirname($dest))) {
            mkdir(dirname($dest), 0755, true);
        }

        if (rename($src, $dest)) {
            $this->target_file = $dest;
            return true;
        }
        return false;
    }

    public function upload_dir($dir) {
        if (is_dir($dir)) {
            $files = scandir($dir);
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..') {
                    $this->upload_file($dir . '/' . $file);
                }
            }
            return true;
        }
        return false;
    }

    public function upload_file($file_path = null) {
        $target_file = $this->tmp_dir . basename($file_path);
        if(!is_null($file_path) && file_exists($file_path)) {
            if (move_uploaded_file($file_path, $target_file)) {
                $this->target_file = $target_file;
                return true;
            }
        } elseif(file_exists($this->file["tmp_name"])) {
            if (move_uploaded_file($this->file["tmp_name"], $this->target_file)) {
                return true;
            }
        }
        return false;
    }

    public function extract_compressed_file($file) {
        $zip = new \ZipArchive;
        if ($zip->open($file) === true) {
            $zip->extractTo($this->target_dir);
            $zip->close();
            return true;
        }
        return false;
    }

    public function add_to_compressed_file($zipFile, $file) {
        $zip = new \ZipArchive;
        if ($zip->open($zipFile) === true) {
            $zip->addFile($file, basename($file));
            $zip->close();
            return true;
        }
        return false;
    }

    public function remove_from_compressed_file($zipFile, $file) {
        $zip = new \ZipArchive;
        if ($zip->open($zipFile) === true) {
            $zip->deleteName(basename($file));
            $zip->close();
            return true;
        }
        return false;
    }

    public function delete_dir($dir) {
        if (is_dir($dir)) {
            $files = array_diff(scandir($dir), ['.', '..']);
            foreach ($files as $file) {
                $path = "$dir/$file";
                is_dir($path) ? $this->delete_dir($path) : unlink($path);
            }
            return rmdir($dir);
        }
        return false;
    }

    public function upload_multiple_files($files) {
        $file_names = [];
        $file_paths = [];
        foreach ($files as $file) {
            $fileHandler = new HFile($file);
            $fileHandler->set_folder($this->target_dir);
            if ($fileHandler->move_to_folder()) {
                $file_names[] = $fileHandler->name;
                $file_paths[] = $fileHandler->target_file;
            }
        }
        return ['names' => $file_names, 'paths' => $file_paths];
    }

    public function set_maximum_upload_length($length) {
        ini_set('upload_max_filesize', $length);
    }

    public function set_maximum_files_in_compressed($max_files) {
        // Implement this based on the specific use case
    }

    public function check_if_file_is_empty() {
        return $this->size == 0;
    }

    public function get_file_name() {
        return $this->name;
    }

    public function get_location() {
        return $this->target_file;
    }

    public function get_client_path() {
        return str_replace($_SERVER['DOCUMENT_ROOT'], '', $this->target_file);
    }

    public function get_server_path() {
        return realpath($this->target_file);
    }

    public function set_error($code, $message) {
        $this->error_code = $code;
        $this->error_message = $message;
    }

    public function get_error() {
        return ['code' => $this->error_code, 'message' => $this->error_message];
    }

    public function display_image($params = []) {
        $default_params = [
            'width' => '100%',
            'height' => 'auto',
            'class' => '',
            'alt' => ''
        ];

        $params = array_merge($default_params, $params);

        return sprintf(
            '<img src="%s" width="%s" height="%s" class="%s" alt="%s">',
            $this->target_file,
            htmlspecialchars($params['width']),
            htmlspecialchars($params['height']),
            htmlspecialchars($params['class']),
            htmlspecialchars($params['alt'])
        );
    }

    public function download() {
        if (file_exists($this->target_file)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($this->target_file) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($this->target_file));
            readfile($this->target_file);
            exit;
        }
        return false;
    }

    public function add_prefix($prefix) {
        $path_info = pathinfo($this->target_file);
        $new_name = $prefix . $path_info['basename'];
        $new_target_file = $path_info['dirname'] . '/' . $new_name;

        if (rename($this->target_file, $new_target_file)) {
            $this->target_file = $new_target_file;
            return true;
        }
        return false;
    }

    public function add_suffix($suffix) {
        $path_info = pathinfo($this->target_file);
        $new_name = $path_info['filename'] . $suffix . '.' . $path_info['extension'];
        $new_target_file = $path_info['dirname'] . '/' . $new_name;

        if (rename($this->target_file, $new_target_file)) {
            $this->target_file = $new_target_file;
            return true;
        }
        return false;
    }

    public function json() {
        return json_encode([
            'name' => $this->name,
            'size' => $this->size,
            'type' => $this->file_type,
            'target_file' => $this->target_file,
        ]);
    }

    public function read() {
        if (file_exists($this->target_file)) {
            return file_get_contents($this->target_file);
        }
        return false;
    }

    public function update() {
        if (file_exists($this->target_file)) {
            return touch($this->target_file);
        }
        return false;
    }

    public function create() {
        if (!file_exists($this->target_file)) {
            $fp = fopen($this->target_file, 'w');
            fclose($fp);
            return true;
        }
        return false;
    }


}


