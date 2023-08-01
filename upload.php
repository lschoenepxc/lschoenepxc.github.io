<?php

/* Get the name of the uploaded file */
$filename = $_FILES['file']['name'];

/* Choose where to save the uploaded file */
$location = "C:/Users/CXJKCS/Dev/Protiq/lschoenepxc.github.io/blender-files/models/uploads/".$filename;

/* Save the uploaded file to the local filesystem */
/* File size up to 8 MB, can be changed in php.ini via "upload_max_filesize" and "post_max_size" */
if ( move_uploaded_file($_FILES['file']['tmp_name'], $location) ) { 
  echo 'Success'; 
} else { 
  echo 'Failure'; 
}

?>