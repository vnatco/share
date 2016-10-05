<?php
require_once("config.inc.php");

if(@$_GET['act']=="share"){
	if(!isset($_POST['data'])||!isset($_POST['time'])||!isset($_POST['password'])) die();
	$d=$_POST['data'];
	$t=$_POST['time'];
	$p=$_POST['password'];
	
	if((string)$p=="true") $p=true; else $p=false;
	
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
	
	$id=md5(time().rand(1000,9999));
	while(file_exists(REC_DIR.$id.REC_EXT)) $id=md5(time().rand(1000,9999));
	
	if(file_put_contents(REC_DIR.$id.REC_EXT,json_encode($data))) echo $id;
}else if(@$_GET['act']=="delete"){
	if(!isset($_POST['id'])) die("ERROR");
	$id=$_POST['id'];
	
	if(file_exists(REC_DIR.$id.REC_EXT)){
		$data=@json_decode(file_get_contents(REC_DIR.$id.REC_EXT),true);
		if(is_array($data)&&
		   array_key_exists("viewer",$data)&&
		   array_key_exists("fingerprint",$data['viewer'])&&
		   $data['viewer']['fingerprint']===hash("sha256",$_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT'])){
			@unlink(REC_DIR.$id.REC_EXT);
			die("SUCCESS");
		}
	}
	die("ERROR");
}

?>