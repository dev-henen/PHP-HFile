# HFiles Library - README

## Overview

The `HFiles` library provides a comprehensive set of methods for handling file operations, including uploading, moving, compressing, renaming, and more. This document provides an overview of each method, along with examples of how to use them.

## Class: HFile

### Properties

- **target_dir**: The directory where the file will be saved.
- **tmp_dir**: Temporary directory for file operations.
- **file**: The file array from the uploaded file.
- **target_file**: The full path to the target file.
- **file_type**: The type of the file (extension).
- **check**: Holds image file details.
- **mime**: The MIME type of the file.
- **size**: The size of the file.
- **name**: The name of the file.
- **auto_generated_name**: A name generated automatically for the file.
- **generated_name**: Another generated name for the file.
- **file_new_name**: The new name assigned to the file.
- **min_compress_size**: Minimum size to trigger compression.
- **error_code**: Error code for any errors that occur.
- **error_message**: Error message for any errors that occur.

## Methods

### `__construct($file, $tmp_dir = 'tmp/')`

Initializes the class with a file and a temporary directory.

**Example:**
```php
$fileHandler = new HFile($_FILES['uploaded_file']);
```

### `set_folder($x, $categorize_by_year = false)`

Sets the target directory for the file, with an option to categorize by year.

**Example:**
```php
$fileHandler->set_folder('uploads', true);
```

### `is_existing()`

Checks if the file already exists in the target directory.

**Example:**
```php
if ($fileHandler->is_existing()) {
    echo "File already exists.";
}
```

### `max_file_size($size)`

Checks if the file size exceeds the specified size.

**Example:**
```php
if ($fileHandler->max_file_size(5000000)) {
    echo "File is too large.";
}
```

### `permit($arr)`

Checks if the file type is in the allowed list.

**Example:**
```php
if ($fileHandler->permit(['jpg', 'png', 'gif'])) {
    echo "File type is allowed.";
}
```

### `move_to_folder()`

Moves the file from the temporary directory to the target directory.

**Example:**
```php
if ($fileHandler->move_to_folder()) {
    echo "File moved successfully.";
}
```

### `rename($name = null, $type = null)`

Renames the file to the specified name and type.

**Example:**
```php
$fileHandler->rename('new_filename', 'png');
```

### `is_image()`

Checks if the file is an image.

**Example:**
```php
if ($fileHandler->is_image()) {
    echo "File is an image.";
}
```

### `is_video()`

Checks if the file is a video.

**Example:**
```php
if ($fileHandler->is_video()) {
    echo "File is a video.";
}
```

### `is_audio()`

Checks if the file is an audio file.

**Example:**
```php
if ($fileHandler->is_audio()) {
    echo "File is an audio file.";
}
```

### `compress_image($quality, $min_size = 0)`

Compresses the image file to the specified quality.

**Example:**
```php
$fileHandler->compress_image(75, 500000);
```

### `compress_video($rate, $min_size = 0)`

Compresses the video file to the specified bitrate.

**Example:**
```php
$fileHandler->compress_video('1000k', 1000000);
```

### `extract_video_frame($index)`

Extracts a frame from the video at the specified index.

**Example:**
```php
$framePath = $fileHandler->extract_video_frame(10);
```

### `lock()`

Locks the file for exclusive access.

**Example:**
```php
if ($fileHandler->lock()) {
    echo "File locked.";
}
```

### `unlock()`

Unlocks the file.

**Example:**
```php
if ($fileHandler->unlock()) {
    echo "File unlocked.";
}
```

### `get_file_size()`

Returns the size of the file.

**Example:**
```php
echo $fileHandler->get_file_size();
```

### `get_modify_time()`

Returns the last modification time of the file.

**Example:**
```php
echo date('Y-m-d H:i:s', $fileHandler->get_modify_time());
```

### `get_state()`

Returns the state of the file, including size, modification time, and type.

**Example:**
```php
$state = $fileHandler->get_state();
print_r($state);
```

### `remove()`

Removes the file from the target directory.

**Example:**
```php
if ($fileHandler->remove()) {
    echo "File removed.";
}
```

### `move_from($dir1, $dir2)`

Moves the file from one directory to another.

**Example:**
```php
if ($fileHandler->move_from('dir1', 'dir2')) {
    echo "File moved.";
}
```

### `upload_dir($dir)`

Uploads all files from a specified directory.

**Example:**
```php
if ($fileHandler->upload_dir('path/to/directory')) {
    echo "Files uploaded.";
}
```

### `upload_file($file_path)`

Uploads a specific file.

**Example:**
```php
if ($fileHandler->upload_file('path/to/file.jpg')) {
    echo "File uploaded.";
}
```

### `extract_compressed_file($file)`

Extracts files from a compressed (zip) file.

**Example:**
```php
if ($fileHandler->extract_compressed_file('path/to/file.zip')) {
    echo "Files extracted.";
}
```

### `add_to_compressed_file($zipFile, $file)`

Adds a file to a compressed (zip) file.

**Example:**
```php
if ($fileHandler->add_to_compressed_file('path/to/archive.zip', 'path/to/file.jpg')) {
    echo "File added to zip.";
}
```

### `remove_from_compressed_file($zipFile, $file)`

Removes a file from a compressed (zip) file.

**Example:**
```php
if ($fileHandler->remove_from_compressed_file('path/to/archive.zip', 'file.jpg')) {
    echo "File removed from zip.";
}
```

### `delete_dir($dir)`

Deletes a directory and all its contents.

**Example:**
```php
if ($fileHandler->delete_dir('path/to/directory')) {
    echo "Directory deleted.";
}
```

### `upload_multiple_files($files)`

Uploads multiple files.

**Example:**
```php
$result = $fileHandler->upload_multiple_files($_FILES['uploaded_files']);
print_r($result);
```

### `set_maximum_upload_length($length)`

Sets the maximum upload length for files.

**Example:**
```php
$fileHandler->set_maximum_upload_length('10M');
```

### `check_if_file_is_empty()`

Checks if the file is empty.

**Example:**
```php
if ($fileHandler->check_if_file_is_empty()) {
    echo "File is empty.";
}
```

### `get_file_name()`

Returns the name of the file.

**Example:**
```php
echo $fileHandler->get_file_name();
```

### `get_location()`

Returns the location of the file.

**Example:**
```php
echo $fileHandler->get_location();
```

### `get_client_path()`

Returns the client-accessible path of the file.

**Example:**
```php
echo $fileHandler->get_client_path();
```

### `get_server_path()`

Returns the server path of the file.

**Example:**
```php
echo $fileHandler->get_server_path();
```

### `set_error($code, $message)`

Sets an error code and message.

**Example:**
```php
$fileHandler->set_error(404, 'File not found');
```

### `get_error()`

Returns the error code and message.

**Example:**
```php
$error = $fileHandler->get_error();
print_r($error);
```

### `display_image($params = [])`

Displays the image with specified parameters.

**Example:**
```php
echo $fileHandler->display_image(['width' => '500px', 'class' => 'img-responsive']);
```

### `download()`

Downloads the file.

**Example:**
```php
$fileHandler->download();
```

### `add_prefix($prefix)`

Adds a prefix to the file name.

**Example:**
```php
if ($fileHandler->add_prefix('prefix_')) {
    echo "Prefix added.";
}
```

### `add_suffix($suffix)`

Adds a suffix to the file name.

**Example:**
```php
if ($fileHandler->add_suffix('_suffix')) {
    echo "Suffix added.";
}
```

### `json()`

Returns the file details in JSON format.

**Example:**
```php
echo $fileHandler->json();
```

### `read()`

Reads the contents of the file.

**Example:**
```php
echo $fileHandler->read();
```

### `update()`

Updates the modification time of the file.

**Example:**
```php
if ($fileHandler->update

()) {
    echo "File updated.";
}
```

### `create()`

Creates a new file if it doesn't exist.

**Example:**
```php
if ($fileHandler->create()) {
    echo "File created.";
}
```

## License

This project is licensed under the MIT License. See the LICENSE file for details.

## Contributing

Feel free to contribute by submitting issues or pull requests.

## Authors

- Moses Henen - Initial work
