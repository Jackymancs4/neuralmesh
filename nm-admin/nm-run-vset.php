<?php
require("lib/controller.class.php");
$app = new Controller;
$app->inc("nmesh");
if(!train::validate($_GET['s'])) throw new Exception("Training set not found!");
$training_data = $app->model->train->get($_GET['s']);

$nid = $training_data[0]['networkID'];
$_GET['n'] = $nid;
$data = $app->model->network->get($nid);
$nn = $app->model->network->nn;

set_time_limit(0);

$app->display("header");

?>

<table id="tabdata" style="float: left;">
<tr><th>Num</th><th>Input</th><th>Output diretto</th><th>MSE</th><th>Output finale</th><th>Output reale</th><th>Result</th></tr>
<?php

  $np = 0;
  $cerror=0;
  $errors = array();
    
	foreach($training_data as $set) { //loop over training set
  
	$data = model::getCache($nid.$set['pattern']);
	
	if($data === null) {
		$input = str_split($set['pattern']);
		if(count($input) != $nn->inputs) die("Incorrect number of entries!");
		$outputs = $nn->run($input);
		//save into cache
		model::saveCache($nid.$set['pattern'],$nid,implode("|",$outputs));
	} else {
		$outputs = explode("|",$data); //get from cache
	}
	
  echo "<tr><td>".($np+1)."</td><td>".$set['pattern']."<td><table><a name=\"id".($np+1)."\"></a>";

	$count = 0;
  $realout = str_split($set['output']);
  $mse = 0;
  
	foreach($outputs as $output) {		
    //echo "<tr><td><strong>".$output."</strong></td><td>".round(100*abs($output-$realout[$count])/(($output+$realout[$count])/2), 1)."%</td></tr>";
    echo "<tr><td><strong>".$output."</strong></td><td>".round(100*abs($output-$realout[$count]), 1)."%</td></tr>";   
    $mse += ($output-$realout[$count])*($output-$realout[$count]);
    $roundout[$count]=round($output);
		$count++;
	}
  $finalout = implode('', $roundout);
    
  if ($finalout==$set['output']) {
    $correct = "<div style=\"color: green;\">correct</div>";
  } else {
    $correct = "<div style=\"color: red;\">uncorrect</div>";
    $cerror++;
    $errors[]=($np+1);
  }
    
  echo "</table></td><td>".round($mse, 5)."</td><td>".implode('', $roundout)."</td><td>".$set['output']."</td><td>".$correct."</td></tr>";
  $np++;
}


?>
</table>
<div id="tools">
	<fieldset>
	<legend>Report Validation</legend>
  <?php
    echo "N errori: ".$cerror."<br>";
    echo "ID degli errori: ";
    
    foreach($errors as $error) {
      echo "<a href=\"#id".$error."\">".$error."</a> ";
    }
    
  ?>
	</fieldset>
</div>
<?php
$app->display("footer");
?>
