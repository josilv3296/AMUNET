<?php
	/*
	continue-fix 1.0.0
	------------------
	basic idea - on "import" check if there is a "continue" file for that steam id and check if the content matches the current server.
	if the user joined the "wrong" server, tell the client to connect the right one (without export), and remote the "continue"-file.
	*/

	function continueExport($parameter, $funcom_id){
		$filename = 'continue_'.$funcom_id.'.json';
		$handle = fopen($filename, 'w');
		fwrite($handle, $parameter);
		fclose($handle);
	}

	function continueCheck($server_id, $funcom_id){
		global $ini_array;

		$filename = 'continue_'.$funcom_id.'.json';
		if (file_exists($filename)) {
			// in case of file exists check if valid server provided
			$handle = fopen($filename, 'r');
			$parameter = fread($handle, filesize($filename));
			fclose($handle);
			if (!isset($ini_array[$parameter]))
				return;

			if ($server_id == $parameter)
				return;

			// fetch config details
			$config = $ini_array[$server_id];
			$config2 = $ini_array[$parameter];

			// try to connect to rcon
			$rcon = new rcon($config['pass'], $config['host'], $config['port']);
			if ($rcon->connected){
				$rcon->send('ast exec "'.$funcom_id.'" "open '.$config2['open']).'"';
				unlink($filename);
				returnError(400, 'moved user to other server');
			}
		}
	}
?>