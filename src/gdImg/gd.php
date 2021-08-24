<?php
function flagGetFromUser($p){
	 $re=[];
	$o = strtoupper($p->orient." ".$p->gender);
	//orientations, sexual
	if (contains("LESB", $o)) $re[]= "lesbian";
 if (contains("BI", $o)) $re[]="bi";
	if (contains("ACE", $o)) $re[]= "asex";
	if (contains("ASE", $o)) $re[]= "asex";
	if (contains("GAY", $o)) $re[]= "gay";
	if (contains("BSX", $o)) $re[]= "bi";
	if (contains("PAN", $o)) $re[]= "pan";
	if (contains("LELL", $o)) $re[]= "lesbian";
	if (contains("OMNI", $o)) $re[]= "omni";
	if (contains("QUEST", $o)) $re[]= "questioning";
	if (contains("ETERO", $o) || contains("HET", $o)) $re[]= "etero";
  //orientation, romantic
  
	//gender-related
		if(contains("MTF",$o) || contains("FTM",$o)  || contains("TRANS",$o)) $re[]="trans";
		return $re;
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
    imagecopyresized($jpg_image, $photo, 800, 0, 0, 0, 500, $hw, $width, $height+150);
}
imagettftext($jpg_image, 25, 0, 720, 435, $white, $font_path, $orient);

$birth = ellipses($birth,20);

imagettftext($jpg_image, 25, 0, 740, 495, $white, $font_path, $birth);

$photo= imagecreatefromjpeg($userPhotoPath);
list($width, $height) = getimagesize($userPhotoPath);
$hw=500*$height/$width;
if($hw>600){$hw=600;}
imagecopyresized($jpg_image, $photo, 10, 100, 0, 0, 500, $hw, $width, $height);

list($wc, $hc) = getimagesize("gdImg/cid.png");
$ho=$hc+floor(count($fln)/6)*100;
$image_out = imagecreatetruecolor($wc,$ho);
 $bg_color = ImageColorAllocate ($image_out, 0, 0, 0);
imagefill($image_out,0,0,$bg_color);
imagecopy($image_out, $jpg_image, 0, 0, 0, 0, $wc,$hc); 
if(count($fln)){
	$i=0;
	foreach($fln as $el){
		$flag = imagecreatefrompng("gdImg/flags/".$el.".png");
	list($width, $height) = getimagesize("gdImg/flags/".$el.".png");
	
	imagecopyresized($image_out, $flag, 10+200*($i%6), $hc+10+100*floor($i/6), 0, 0 , 200, 100, $width, $height);
 $i++;
	}
}
unlink($dest);
// Send Image to Browser
imagejpeg($image_out,$dest);



// Clear Memory
imagedestroy($image_out);
}
?>
