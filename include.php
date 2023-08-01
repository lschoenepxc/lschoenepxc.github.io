<?php
// get the parameters from URL
$inputPath = $_REQUEST["inputPath"];

$blenderPath = "C:/Users/CXJKCS/Dev/Protiq/lschoenepxc.github.io/blender/blender";
$scriptPath = "C:/Users/CXJKCS/Dev/Protiq/lschoenepxc.github.io/blender-files/UVMapping.py";

// $inputPath = "C:/Users/CXJKCS/Dev/Protiq/lschoenepxc.github.io/blender-files/models/uploads/export.glb";

$execBlender = $blenderPath . " -b --python " . $scriptPath . " -- " . $inputPath . " > blender.log 2>&1 & echo $! >> blender.pid";

$output=null;
$retval=null;

exec($execBlender, $output, $retval);
// Output responseText
// echo "Returned with status $retval";
echo "Now viewable with texture!";
?>