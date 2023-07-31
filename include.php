<?php
// get the parameters from URL
$blobUrl = $_REQUEST["blobUrl"];
$inputPath = $_REQUEST["inputPath"];
$outputPath = $_REQUEST["outputPath"];

$blenderPath = "C:/Users/CXJKCS/Dev/Protiq/lschoenepxc.github.io/blender/blender";
$scriptPath = "C:/Users/CXJKCS/Dev/Protiq/lschoenepxc.github.io/blender-files/UVMapping.py";

// $inputPath = "C:/Users/CXJKCS/Dev/Protiq/lschoenepxc.github.io/blender-files/models/test.glb";
// $outputPath = "C:/Users/CXJKCS/Dev/Protiq/lschoenepxc.github.io/blender-files/models/modelUV.glb";

$execBlender = $blenderPath . " -b --python " . $scriptPath . " -- " . $blobUrl . " " . $inputPath . " " . $outputPath . " > blender.log 2>&1 & echo $! >> blender.pid";

$output=null;
$retval=null;

exec($execBlender, $output, $retval);
// Output responseText
echo "Returned with status $retval";
?>