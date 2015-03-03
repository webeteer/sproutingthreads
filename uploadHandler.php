<?php
require('uploader/Uploader.php');

// Directory where we're storing uploaded images
// Remember to set correct permissions or it won't work
$upload_dir = 'var/uploads/';

$dt = date("Y-m-d_H:i:s");

$origName = $_GET['uploadfile'];
$rand = rand(1000, 9999);
$newName = $dt."_".$rand."_".$origName;

$uploader = new FileUpload('uploadfile');
$uploader->allowedExtensions = array('jpg', 'jpeg', 'png', 'gif');
$uploader->newFileName  = $newName;

// Handle the upload
$result = $uploader->handleUpload($upload_dir);

if (!$result) {
  exit(json_encode(array('success' => false, 'msg' => $uploader->getErrorMsg())));  
}

echo json_encode(array('success' => true, 'filename' => $newName));
