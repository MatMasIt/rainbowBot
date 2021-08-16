
<!DOCTYPE html> 
<html>
<head>
    <title>Help</title>
     <meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, can-resize=no">
	<link rel="stylesheet" href="https://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.css" />
    <script src="https://code.jquery.com/jquery-1.11.1.min.js"></script>
    <script src="https://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.js"></script>

</head>

<body>
	<div data-role="page" id="main"  class="ui-content">
        <div data-role="header" data-position="fixed">
            <h1 style="font-size:40px;" class="brandFont">Help</h1>
        </div>
        
   <ul data-role="listview" data-filter="true" data-filter-placeholder="Search fruits..." data-inset="true">
   <?php
   $a=json_decode(file_get_contents("help.json"),true);
   foreach($a["commands"]as $e){
   	$tt= "<li>".$e["command"];
   	if(strlen($e["arguments"])>0){
   		$tt.="<hr />".str_replace("\n","<br />",htmlentities($e["arguments"]));
   	}
   		if(strlen($e["description"])>0){
   		$tt.="<hr />".str_replace("\n","<br />",htmlentities($e["description"]));
   	}
   	echo $tt;
   }
   ?>
   </ul>

                
 </div>   
</body>



</html> 