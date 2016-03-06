<?php
while(true){
	if(!file_exists("backup.lock")){ 
		fopen("backup.lock", "w"); 
		echo 'file do not exists';
	}else{
		$fp_lock_r = fopen('backup.lock', 'r+');
		if(filesize("backup.lock")<1) {$filesize_lock = 1;}
		else {$filesize_lock = filesize("backup.lock");}
		$locked = fread($fp_lock_r,$filesize_lock);
		fclose($fp_lock_r);
		break;
	}
}
if($locked==1) {
	
	$fp_fail_r = fopen('backup.fail', 'r+');
	if(filesize("backup.fail")<1){$filesize_fail = 1;} 
	else {$filesize_fail = filesize("backup.fail");}
	$locked_fail = fread($fp_fail_r,$filesize_fail);
	fclose($fp_fail_r);
	
	$fp_lock = fopen('backup.fail', 'w+');
	fwrite($fp_lock,'0');
	fclose($fp_lock);
	
	if($locked_fail>=10){
		
		$fp_lock = fopen('backup.lock', 'w+');
		fwrite($fp_lock,'0');
		fclose($fp_lock);
		
		$message = "Line 1\r\nLine 2\r\nLine 3";
		$message = wordwrap($message, 70, "\r\n");
		mail('caffeinated@example.com', 'My Subject', $message);
	}

	die('Backup In Progress...');
}elseif($locked==0) {
	function zipData($source, $destination) {
		if (extension_loaded('zip')) {
			if (file_exists($source)) {
				$zip = new ZipArchive();
				if ($zip->open($destination, ZIPARCHIVE::CREATE)) {
					$source = realpath($source);
					if (is_dir($source)) {
						$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);
						foreach ($files as $file) {
							$file = realpath($file);
							if (is_dir($file)) {
								$zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
							} else if (is_file($file)) {
								$zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
							}
						}
					} else if (is_file($source)) {
						$zip->addFromString(basename($source), file_get_contents($source));
					}
				}
				return $zip->close();
			}
		}
		return false;
	}
	$fp_lock = fopen('backup.lock', 'w+');
	fwrite($fp_lock,'1');
	fclose($fp_lock);

	// if (isset($_SERVER['REMOTE_ADDR'])) die('Permission denied.');
	ini_set('max_execution_time', 6000000000000);
	ini_set('memory_limit','1024M');
	zipData('./a', 'backup.zip');

	//echo 'Finished.';
	$fp_f = fopen('backup.time', 'w+');
	fwrite($fp_f,strtotime("now"));
	fclose($fp_f);

	$fp_lock = fopen('backup.lock', 'w+');
	fwrite($fp_lock,'0');
	fclose($fp_lock);
	// unlink('backup.lock');
}
?>