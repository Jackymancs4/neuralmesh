<?php
require("lib/controller.class.php");
$app = new Controller;
$app->inc("nmesh");
$data = $app->model->network->get($_GET['n']);
$nn = $app->model->network->nn;
set_time_limit(0);

if($_POST) {	
	$app->model->val->run("run",$_POST);
	
	$data = model::getCache($_GET['n'].$_POST['input']);
	
	if($data === null) {
		$input = str_split($_POST['input']);
		if(count($input) != $nn->inputs) die("Incorrect number of entries!");
		$outputs = $nn->run($input);
		//save into cache
		model::saveCache($_GET['n'].$_POST['input'],$_GET['n'],implode("|",$outputs));
	} else {
		$outputs = explode("|",$data); //get from cache
	}
	
	$return = "";
	$count = 1;	
	foreach($outputs as $output) {
		$return .= "<tr><td>Output $count </td><td><strong>".$output."</strong></td><td>".round($output)."</td></tr>";
		$count++;
	}
}

$app->display("header");
?>

<div id="tools">
	<fieldset>
	<legend>New Validation Set</legend>
	<form action="nm-manage-set.php?action=new" method="post">
	<input type="text" name="label" />
	<input type="hidden" name="n" value="<?php echo $_GET['n']; ?>" />
	<input type="hidden" name="type" value="v" />
	<input type="submit" value="Add" />
	</form>
	</fieldset>
</div>

<form action="nm-run.php?n=<?php echo $_GET['n']; ?>" method="post">
<table>
<tr><th>Input:</th><td><input type="text" name="input" maxlength="<?php echo $nn->inputs; ?>" size="40" /><input type="submit" value="Run" /></td></tr>
</table>
</form>
<?php
if(isset($return) && strlen($return)) {
?>
<p><strong>Input:</strong> <?php echo $_POST['input']; ?></p>
<table>
<?php
echo $return;
}
?>
</table>
<table id="tabdata">
<tr><th>Validation Set Name</th><th></th></tr>
<?php 
$app->model->train->listTrainingSets($_GET['n'], "v"); //list sets in network
?>
</table>

<?php
$app->display("footer");
?>
