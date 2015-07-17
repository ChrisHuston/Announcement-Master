<?php
session_start();
function _log($str) {

}

/**
 *
 * Delete a directory RECURSIVELY
 * @param string $dir - directory path
 * @link http://php.net/manual/en/function.rmdir.php
 */
function rrmdir($dir) {
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (filetype($dir . "/" . $object) == "dir") {
                    rrmdir($dir . "/" . $object);
                } else {
                    unlink($dir . "/" . $object);
                }
            }
        }
        reset($objects);
        rmdir($dir);
    }
}

/**
 *
 * Check if all the parts exist, and
 * gather all the parts of the file together
 * @param string $dir - the temporary directory holding all the parts of the file
 * @param string $fileName - the original file name
 * @param string $chunkSize - each chunk size (in bytes)
 * @param string $totalSize - original file size (in bytes)
 */
function createFileFromChunks($temp_dir, $fileName, $chunkSize, $totalSize) {

    // count all the parts of this file
    $total_files = 0;
    foreach(scandir($temp_dir) as $file) {
        if (stripos($file, $fileName) !== false) {
            $total_files++;
        }
    }

    // check that all the parts are present
    // the size of the last part is between chunkSize and 2*$chunkSize
    if ($total_files * $chunkSize >=  ($totalSize - $chunkSize + 1)) {

        // create the final destination file
        if (($fp = fopen('../files/'.$_SESSION['course_id'].'/'.$fileName, 'w')) !== false) {
            for ($i=1; $i<=$total_files; $i++) {
                fwrite($fp, file_get_contents($temp_dir.'/'.$fileName.'.part'.$i));
                _log('writing chunk '.$i);
            }
            fclose($fp);
        } else {
            _log('cannot create the destination file');
            return false;
        }

        // rename the temporary directory (to avoid access from other
        // concurrent chunks uploads) and than delete it
        if (rename($temp_dir, $temp_dir.'_UNUSED')) {
            rrmdir($temp_dir.'_UNUSED');
        } else {
            rrmdir($temp_dir);
        }
    }

}


////////////////////////////////////////////////////////////////////
// THE SCRIPT
////////////////////////////////////////////////////////////////////

//check if request is GET and the requested chunk exists or not. this makes testChunks work
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $temp_dir = '../files/'.$_SESSION['course_id'].'/'.$_GET['flowIdentifier'];
    $chunk_file = $temp_dir.'/'.$_GET['flowFilename'].'.part'.$_GET['flowChunkNumber'];
    if (file_exists($chunk_file)) {
        header("HTTP/1.0 200 Ok");
    } else
    {
        header("HTTP/1.0 404 Not Found");
    }
}



// loop through files and move the chunks to a temporarily created directory
if (!empty($_FILES)) foreach ($_FILES as $file) {

    // check the error status
    if ($file['error'] != 0) {
        _log('error '.$file['error'].' in file '.$_POST['flowFilename']);
        continue;
    }

    // init the destination file (format <filename.ext>.part<#chunk>
    // the file is stored in a temporary directory
    $temp_dir = '../files/'.$_SESSION['course_id'].'/'.$_POST['flowIdentifier'];
    $dest_file = $temp_dir.'/'.$_POST['flowFilename'].'.part'.$_POST['flowChunkNumber'];

    // create the temporary directory
    if (!is_dir($temp_dir)) {
        mkdir($temp_dir, 0777, true);
    }

    // move the temporary file
    if (!move_uploaded_file($file['tmp_name'], $dest_file)) {
        _log('Error saving (move_uploaded_file) chunk '.$_POST['flowChunkNumber'].' for file '.$_POST['flowFilename']);
    } else {

        // check if all the parts present, and create the final destination file
        createFileFromChunks($temp_dir, $_POST['flowFilename'],
            $_POST['flowChunkSize'], $_POST['flowTotalSize']);
    }
}

?>
