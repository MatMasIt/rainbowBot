<?php
function flagGetFromUser($p){
	$o = strtoupper($p->orient);
	if (contains("LESB", $o)) return "lesbian";
	if (contains("BI", $o)) return "bi";
	if (contains("ACE", $o)) return "asex";
	if (contains("ASE", $o)) return "asex";
	if (contains("GAY", $o)) return "gay";
	if (contains("BSX", $o)) return "bi";
	if (contains("PAN", $o)) return "pan";
	if (contains("LELL", $o)) return "lesbian";
	if (contains("OMNI", $o)) return "omni";
	if (contains("QUEST", $o)) return "questioning";
	if (contains("ETERO", $o) || contains("HET", $o)) return "etero";
	return false;
}
function dlImage($userId,$fileName){	



$r=API("getUserProfilePhotos", ["user_id" => $userId]);
$n=$r["result"]["photos"];
$a=0;//count($n)-1;
$b=count($n[$a])-1;
$file_id=$r["result"]["photos"][0][0]["file_id"];
if($file_id==null){
	copy("gdImg/default.jpg",$fileName);
	return 0;
}

$fO=API("getFile", ["file_id" => $file_id]);
$fu="https://api.telegram.org/file/".$GLOBALS["config"]["lgbt"]["TOKEN"]."/".$fO["result"]["file_path"]; 
unlink($fileName);
$ch = curl_init($fu);
$fp = fopen($fileName, 'wb+');
curl_setopt($ch, CURLOPT_FILE, $fp); 
curl_setopt($ch, CURLOPT_HEADER, 0); 
curl_exec($ch);
curl_close($ch); 
fclose($fp);
}


			
function photo_id_by_user($u,$chat_id){
	dlImage($u->getId(),"gdImg/archive/".$u->getId().".pp.jpeg");
	$flag=flagGetFromUser($u);
	make_id_card_img($u->name,$u->gender,$u->orient,$u->birthDate." (".years($u->birthDate)." anni) ","gdImg/archive/".$u->getId().".pp.jpeg","gdImg/archive/".$u->getId().".fi.jpeg",$u->getId(),$chat_id,$flag);
}
function ellipses($text,$maxChar){
	return strlen($text) > $maxChar ? substr($text,0,$maxChar)."..." : $text;
}
function make_id_card_img($name,$gender,$orient,$birth,$userPhotoPath,$dest,$id,$chat_id,$fln,$template="gdImg/cid.png",$font_path = 'gdImg/Roboto-Thin.ttf'){
$jpg_image = imagecreatefrompng($template);


// Allocate A Color For The Text
$white = imagecolorallocate($jpg_image, 255, 255, 255);

// Set Text to Be Printed On Image
$name = ellipses($name,30);

imagettftext($jpg_image, 25, 0, 655, 305, $white, $font_path, $name);

$gender = ellipses($gender,15);

imagettftext($jpg_image, 25, 0, 655, 375, $white, $font_path, $gender);


$orient = ellipses($orient,25);

if(is_admin($id,$chat_id)){
	$photo= imagecreatefrompng("gdImg/admin.png");
list($width, $height) = getimagesize("gdImg/admin.png");
$hw=150*$height/$width;
if($hw>600){$hw=600;}
    imagecopyresized($jpg_image, $photo, 20, 600, 0, 0, 500, $hw, $width, $height+50);
}
if($id==$GLOBALS["config"]["lgbt"]["devId"]){
	$photo= imagecreatefrompng("gdImg/dev.png");
list($width, $height) = getimagesize("gdImg/dev.png");
$hw=150*$height/$width;
if($hw>600){$hw=600;}
    imagecopyresized($jpg_image, $photo, 800, 0, 0, 0, 500, $hw, $width, $height);
}
imagettftext($jpg_image, 25, 0, 720, 435, $white, $font_path, $orient);

$birth = ellipses($birth,20);

imagettftext($jpg_image, 25, 0, 740, 495, $white, $font_path, $birth);

$photo= imagecreatefromjpeg($userPhotoPath);
list($width, $height) = getimagesize($userPhotoPath);
$hw=500*$height/$width;
if($hw>600){$hw=600;}
imagecopyresized($jpg_image, $photo, 10, 100, 0, 0, 500, $hw, $width, $height);

if($fln){
	$flag = imagecreatefrompng("gdImg/flags/".$fln.".png");
	list($width, $height) = getimagesize("gdImg/flags/".$fln.".png");
	$w2 = $width;
	//if($hw>600){$hw=600;}
	imagecopyresized($jpg_image, $flag, 10, 0, 0, 0, 200, 100, $width, $height);
	$gender = strtolower($gender);
	$orient = strtolower($orient);
	if(contains("mtf",$gender) || contains("ftm",$gender)  || contains("trans",$gender) || contains("mtf",$orient) || contains("ftm",$orient)  || contains("trans",$orient) ){
		$flag = imagecreatefrompng("gdImg/flags/trans.png");
		list($width, $height) = getimagesize("gdImg/flags/trans.png");
		//if($hw>600){$hw=600;}
		imagecopyresized($jpg_image, $flag, 200, 0, 0, 0, 200, 100, $width, $height);
	}
}
unlink($dest);
// Send Image to Browser
imagejpeg($jpg_image,$dest);



// Clear Memory
imagedestroy($jpg_image);
}
?>
