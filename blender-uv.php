<h1>Remote Blender</h1>
<?php
$pid = 0;  //initialisation
$pids = [];
if(file_exists('blender.pid')) $pid = intval(file_get_contents('blender.pid')); //load blender.pid
exec('pgrep blender',$pids); //get all blender processes

// 1.
if($pid && in_array($pid,$pids)) {
	echo "Blender is running!<br>";
	//display render progress

	exec("tail -c 300 blender.log",$logcontent);
	preg_match_all("/Rendered (\d+)\/(\d+) Tiles/",implode("",$logcontent),$found);
	$rendered = intval($found[1][0]);
	$to_render = intval($found[2][0]);
	$progress = round($rendered / $to_render * 100);
	echo "$progress% completed";
	echo '<div style="border:1px solid #aaa;padding:3px;"><div style="background:#aadd33;width:'.$progress.'%;height:50px;"></div></div>';
	echo "<script>setTimeout('document.location.reload();',2000);</script>";
} else {
	if(isset($_POST["command"]) && $_POST["command"]=="start render"){
		@unlink("blender.log"); //clean up
		@unlink("blender.pid");
		
		echo "attempting to start render...";
		// exec('./Blender.app/Contents/MacOS/blender -b test.blend -o results/test####.png -f 1 > blender.log 2>&1 & echo $! >> blender.pid');
		exec('./blender/blender -b --python ./blender-files/UVMapping.py -- C:/Users/CXJKCS/Dev/Protiq/lschoenepxc.github.io/blender-files/models/test.glb C:/Users/CXJKCS/Dev/Protiq/lschoenepxc.github.io/blender-files/models/modelUV.glb > blender.log 2>&1 & echo $! >> blender.pid');
		echo "<script>setTimeout('document.location.reload();',2000);</script>";
	}
	else {
		$results = glob('blender-files/models/*'); //load all result files
		if(count($results)>0){
			echo "<h3>Results</h3><ul>";
			foreach($results as $file) echo "<li><a href=\"$file\">$file</a></li>";
			echo "</ul>";
		}
		//load the last 300 chars from the log
		exec("tail -c 300 blender.log",$messages);
		echo "<h3>Last log entries</h3><textarea cols=100 rows=8>".implode("\n",$messages)."</textarea>";
		echo '<br><br><form method="post">';
		echo '<input name="command" type="submit" value="start render"></form>';
	}
}

?>
