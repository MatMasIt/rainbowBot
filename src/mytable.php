<style>
#customers {
  font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
  border-collapse: collapse;
  width: 100%;
}

#customers td, #customers th {
  border: 1px solid #ddd;
  padding: 8px;
}

#customers tr:nth-child(even){background-color: #f2f2f2;}

#customers tr:hover {background-color: #ddd;}

#customers th {
  padding-top: 12px;
  padding-bottom: 12px;
  text-align: left;
  background-color: #4CAF50;
  color: white;
}
</style>
<?php
require_once("vendor/autoload.php"); 

$people = new \Filebase\Database([
   					 'dir' => 'people'
				]);
 $table=[];
 $i=0;
 foreach($people->findAll() as $element){
	 $el=$element->toArray();
     $el["id"]=$element->getId();
     $el["num"]=$i+1;
	 foreach($el as $key=>$val){
 		$table[$key][$i]=$val;
 	}
 $i++;
 }
$tot=0;
?>
<table id="customers">
<thead>
<tr>
<?php
	$ka=["telegramName"=>"Nome su Telegram","username"=>"Username su Telegram","birthDate"=>"Data di nascita","name"=>"Nome","yearsOld"=>"Età","gender"=>"Genere","orient"=>"Orientamento","where"=>"Viene da","pvtNotice"=>"Avviso sui messaggi privati","bio"=>"Bio"];
	$skip=[];
	$in=0;
foreach(array_keys($table) as $i){
	if(!in_array($i,array_keys($ka))){
		$skip[]=$in;
		$in++;
		continue;
	}
	$in++;
	echo "<th>".$ka[$i]."</th>";
	if(count($table[$i])>$tot){
		$tot=count($table[$i]);
	}
}
?></tr></thead><tbody><?php
for($i=0;$i<$tot;$i++){
	?><tr><?php
	$j=0;
	foreach(array_keys($table) as $k){
		if(in_array($j,$skip)){
			$j++;
			continue;
			}
	?><td><?php echo htmlentities($table[$k][$i]);?></td><?php
	$j++;
	}
	?></tr><?php
}
?>
</tbody>
</table>
