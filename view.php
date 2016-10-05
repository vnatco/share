<?php require_once("config.inc.php"); ?><!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Private Share</title>
	<meta name="description" content="">
	<link rel="stylesheet" href="styles.css">
	<script src="js/jQuery.js"></script>
	<script src="js/aes.js"></script>
</head>
<body>
	<div class="content">
	 <div class="table">
	  <div class="cell">
	   <div style="margin:40px;" align="center">
		
		<div class="head">Private Share</div>
		<div style="height:40px;"></div>
		
		<div class="holder">
			<?php
				try{
					// Check and get ID from request URL
					if(!isset($_GET['id'])) throw new Exception("ID is missing");
					$id=$_GET['id'];
					if(!file_exists(REC_DIR.$id.REC_EXT)) throw new Exception("Link has been expired or ID is incorrect. Please, provide valid ID.");
					
					// Get data from file and decode it. Check if was able to decode.
					$data=@json_decode(file_get_contents(REC_DIR.$id.REC_EXT),true);
					if(json_last_error()!==JSON_ERROR_NONE) throw new Exception("Was not able to decode data from database.");
					
					// Check if file has been expired.
					if(time()>$data['expires']){
						@unlink(REC_DIR.$id.REC_EXT);
						throw new Exception("Link has been expired.");
					}
					
					// Check if it's disabled, and current viewer is the same who disabled it.
					if($data['disabled']===1&&$data['viewer']['fingerprint']!==hash("sha256",$_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT'])) throw new Exception("Link has been viewed from another browser.");
					
					// Set viewer information and disable data for anyone else
					$data['viewer']['ip']=$_SERVER['REMOTE_ADDR'];
					$data['viewer']['fingerprint']=hash("sha256",$_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT']);
					if($data['disabled']===0){
						$data['disabled']=1;
						// $data['expires']=time()+300;
					}
					
					// Put updated content in file
					file_put_contents(REC_DIR.$id.REC_EXT,json_encode($data));
					
					// Show data on screen
					echo '<div class="encrypted" data-id="'.$id.'" data-password="'.($data['password']===true?"true":"false").'">'.$data['data'].'</div>';
				}catch(Exception $e){
					if($e->getMessage()!=="") echo '<div class="error">'.$e->getMessage().'</div>';
				}
			?>
		</div>
		
	   </div>
	  </div>
	 </div>
	</div>
	<div class="hTHolder"></div>
	<script>
		if($(".encrypted").length==1){
			var encrypted=$(".encrypted").text();
			var id=$(".encrypted").attr("data-id");
			var password=$(".encrypted").attr("data-password")=="true"?true:false;
			$(".holder").empty();
			
			$(document).ready(function(){
				if(password){
					$(".holder").append('<input class="pwd" type="password" placeholder="Please, enter password." value="" />').find("input").focus();
					$(".holder").append('<div style="height:30px;"></div>');
					$(".holder").append('<button class="submit">Submit</button>');
					$(".holder").append('<div class="error" style="margin-top:30px;display:none;">Incorrect password submitted. Please, enter valid password.</div>');
					
					// Fix input Auto-complete!
					setTimeout(function(){
						$("input,textarea").val("");
						$("input,textarea").css("opacity",1);
					},200);
					
					$(".holder input").bind("keydown",function(e){
						if(e.which==13) $(".holder button").trigger("click");
					});
				}else{
					var decrypted=CryptoJS.AES.decrypt(encrypted,"").toString(CryptoJS.enc.Utf8);
					$(".holder").empty();
					if(decrypted.length<1){
						$(".holder").append('<div class="error">Something went wrong while decrypting data. :(</div>');
						shakeBox($(".holder"));
					}else $(".holder").append('<div class="decrypted"><pre>'+(decrypted)+'</pre></div><div><button class="delete">Delete</button></div>');
				}
			});
			
			$(".holder").on("click","button.submit",function(){
				var password=$("input").val();
				if(password.length<1) return $("input").focus();
				
				var decrypted=CryptoJS.AES.decrypt(encrypted,password).toString(CryptoJS.enc.Utf8);
				if(decrypted.length<1){
					$(".error").slideDown(100);
					$("input").focus();
					shakeBox($(".holder"));
				}else $(".holder").empty().append('<div class="decrypted"><pre>'+(decrypted)+'</pre></div><div><button class="delete">Delete</button></div>');
			});
			
			$(".holder").on("click","button.delete",function(){
				showLoader();
				$.post("ajax.php?act=delete",{id:id},function(response){
					hideLoader();
					top.location.href='<?php echo SITE_FULL_URL; ?>';
				});
			});
			
			// Box is jQuery instance, not checking but keep in mind.
			var shakeBox=function(box){
				$("body").css({"overflow-x":"hidden"});
				var saved={position:box.css("position"),transition:box.css("transition")};
				box.css({position:"relative",transition:"left 0.1s ease 0s"});
				setTimeout(function(){box.css({left:-10});},10);
				setTimeout(function(){box.css({left:8});},110);
				setTimeout(function(){box.css({left:-4});},210);
				setTimeout(function(){box.css({left:2});},310);
				setTimeout(function(){box.css({left:0});},410);
				setTimeout(function(){
					box.css(saved);
					$("body").css({"overflow-x":"auto"});
				},440);
			}
			
			/* Loader. But it's fast, we don't really need loader. */
			function showLoader(){}
			function hideLoader(){}
			function escape(unsafe){return unsafe.replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;").replace(/'/g,"&#039;");}
		}
	</script>
</body>
</html>