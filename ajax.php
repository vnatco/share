<?php
require_once("config.inc.php");

if(@$_GET['act']=="share"){
	// Save encrypted shared content in file and show 
	
	// Get data from Post
	if(!isset($_POST['data'])||!isset($_POST['time'])||!isset($_POST['password'])) die("ERROR");
	$d=$_POST['data'];
	$t=(int)$_POST['time'];
	$p=$_POST['password']; // This is not password. It's more like "HAS PASSWORD". It can be "yes" or "no"
	
	if($t<1) die("ERROR"); // Check if expiration time is positive number
	
	if((string)$p=="true") $p=true; else $p=false; // Replace "has password" string with boolean true / false
	
	// Create array that will be converted to JSON string after
	$data=array(
		"data"=>$d,
		"password"=>$p,
		"expires"=>time()+$t,
		"disabled"=>0,
		"sharer"=>array(
			"ip"=>$_SERVER['REMOTE_ADDR'],
			"fingerprint"=>hash("sha256",$_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT'])
		),
		"viewer"=>array(
			"ip"=>"",
			"fingerprint"=>""
		)
	);
	
	// Generate file name that currently does not exists in records folder
	$id=md5(time().rand(1000,9999)); // <- Replace with something else
	while(file_exists(REC_DIR.$id.REC_EXT)) $id=md5(time().rand(1000,9999));
	
	// 
	if(file_put_contents(REC_DIR.$id.REC_EXT,json_encode($data))) die($id); else die("ERROR");
}else if(@$_GET['act']=="delete"){
	// Delete record 
	
	// Get ID from Post
	if(!isset($_POST['id'])) die("ERROR");
	$id=$_POST['id'];
	
	// Check if file exists in records directory
	if(file_exists(REC_DIR.$id.REC_EXT)){
		// Open file and try to decode it
		$data=@json_decode(file_get_contents(REC_DIR.$id.REC_EXT),true);
		// Check if data is array, we have all values we need and fingerprint matches the one from file. We can also check for JSON last error.
		if(is_array($data)&&
		   array_key_exists("viewer",$data)&&
		   array_key_exists("fingerprint",$data['viewer'])&&
		   $data['viewer']['fingerprint']===hash("sha256",$_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT'])){
			// Delete file
			@unlink(REC_DIR.$id.REC_EXT);
			die("SUCCESS");
		}
	}
	die("ERROR");
}

?>