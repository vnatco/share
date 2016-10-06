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
			<textarea placeholder="Type your message here..."></textarea>
			<div style="height:40px;"></div>
			<input class="pwd" type="password" placeholder="Enter Password. Keep empty if don't want to." value="" />
			<div style="height:30px;"></div>
			<div align="center">
				<div class="radioButton" data-group="format" data-value="1800">30 Minutes</div>
				<div class="radioButton" data-group="format" data-value="3600">1 Hour</div>
				<div class="radioButton" data-group="format" data-value="21600" data-active="true">6 Hour</div>
				<div class="radioButton" data-group="format" data-value="86400">1 Day</div>
				<div class="radioButton" data-group="format" data-value="259200">3 Day</div>
				<div class="radioButton" data-group="format" data-value="604800">1 Week</div>
				<div class="radioButton" data-group="format" data-value="2592000">1 Month</div>
			</div>
			<div style="height:30px;"></div>
			<button>Share</button>
		</div>
		
	   </div>
	  </div>
	 </div>
	</div>
	<div class="hTHolder"></div>
	<script>
		// Clicking on header will reload page if there's no textarea tag.
		// Textarea is removed after clicking on share button.
		$(".head").bind("click",function(){
			if($("textarea").length!=1) top.location.reload();
		});
		
		// Scroll to top after changing textarea content
		$("textarea").change(function(){
			$(this).scrollTop(0);
		});
		// Refresh textarea height every time key has been pressed
		$(document).bind("keyup keydown keypress",function(e){
			refreshSize();
		});
		// Function to refresh textarea height
		function refreshSize(){
			var t=escape($("textarea").val()); // Escape html characters
			if(t=="") t="&nbsp;"; // If text is empty, add whitespace to it.
			t=t.replace(/(\r\n|\n\r|\r|\n)/g,"<br />"); // Replace new line with <br /> tag
			t+="<br />&nbsp;"; // Add new line at the end of text
			
			// hTHolder is hidden div element used to calculate height of textarea
			$(".hTHolder").show().width($("textarea").width()).empty().append(t);
			
			// Set textarea height to height of hidden div element
			$("textarea").height($(".hTHolder").height());
			$(".hTHolder").hide();
		}
		refreshSize();
		
		// Fix input Auto-complete!
		$(document).ready(function(){
			setTimeout(function(){
				$("input,textarea").val("");
				$("input,textarea").css("opacity",1); // Textareas and inputs has opacity 0 by default
			},200);
		});
		
		/* SHARE button Click */
		$("button").bind("click",function(){
			// Check length of textarea value
			if($("textarea").val().length<1) return $("textarea").focus();
			
			// Encrypt data with password from input field
			var d=CryptoJS.AES.encrypt($("textarea").val(),$("input").val()).toString();
			// Get expiration time from selected radio button
			var t=parseInt($(".radioButton[data-active='true']").attr("data-value"));
			// Check if password field is empty
			var p=$("input").val().length>0?true:false;
			
			// Send POST request to ajax.php file to save current data
			showLoader();
			$.post("ajax.php?act=share",{data:d,time:t,password:p},function(response){
				// Check if everything went well
				if(response=="ERROR"||response.length<1) alert("Something went wrong. Try again.");
				else{
					// Create Link using Response as ID
					var link='<?php echo SITE_FULL_URL; ?>'+response;
					
					// Clear main div element and append new elements in it
					$(".holder").empty();
					$(".holder").append('<div class="description">Click on Copy Link button or select and copy link manually. Please, note that content can be viewed only once and will be deleted from server after opening this link.</div>');
					$(".holder").append('<div class="link">'+link+'</div>');
					$(".holder").append('<div class="copy"><button>Copy Link</button></div>');
					
					// I made clipboard.swf long time ago from Online flash (swf) maker. Don't remember site name.
					$(".holder>.copy").append('<embed menu="false" wmode="transparent" type="application/x-shockwave-flash" allowfullscreen="false" flashvars="text='+link+'" src="clipboard.swf" />');
					// Set width and height of embed to width and height of button
					$(".copy>embed").css({width:$(".holder>.copy>button").outerWidth(true),height:$(".holder>.copy>button").outerHeight(true)});
				}
			});
		});
		
		/* Radio Button click */
		$(".radioButton").bind("click",function(){
			var hasClass=$(this).find(".checkbox").hasClass("icCkOn"); // Get state of current radio button
			
			// If radio button has group, disable all radio buttons in this group.
			if($(this).attr("data-group")!="") $(".radioButton[data-group='"+$(this).attr("data-group")+"'] .checkbox").removeClass("icCkOn").addClass("icCkOff");
			
			// Check current state of radio button
			if(hasClass){
				// If radio button was enabled, and it's part of group, keep it enabled. But if it was not part of group, act like normal checkbox and disable it.
				if($(this).attr("data-group")!="") $(this).find(".checkbox").removeClass("icCkOff").addClass("icCkOn");
				else $(this).find(".checkbox").removeClass("icCkOn").addClass("icCkOff");
			}else $(this).find(".checkbox").removeClass("icCkOff").addClass("icCkOn"); // Radio button was unchecked, check it
		});
		/* Initialize Radio Buttons */
		$.each($(".radioButton"),function(){
			$(this).html('<table><tr><td><div class="checkbox icCkOff"></div></td><td><div style="width:8px;"></div></td><td>'+$(this).html()+'</td></tr></table>');
			if($(this).attr("data-active")=="true") $(this).trigger("click");
		});
		
		/* Loader. But it's fast, we don't really need loader. */
		function showLoader(){}
		function hideLoader(){}
		function escape(unsafe){
			return unsafe.replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;").replace(/'/g,"&#039;");
		}
	</script>
</body>
</html><?php
// Delete expired files from records directory
try{
	$skip=array(".","..",".htaccess");
	foreach(scandir(REC_DIR) as $file){
		// Check if file name is in skip array
		if(in_array($file,$skip)) continue;
		
		// Check if file name ends with extension from configuration
		if(strpos($file,REC_EXT)!==strlen($file)-strlen(REC_EXT)) continue;
		
		// Get data from file and decode it. Check if was able to decode.
		$data=@json_decode(file_get_contents(REC_DIR.$file),true);
		if(json_last_error()!==JSON_ERROR_NONE) continue;
		
		if(time()>$data['expires']) @unlink(REC_DIR.$file);
	}
}catch(Exception $e){}
?>