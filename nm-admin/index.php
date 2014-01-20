<?php

if (!file_exists("nm-settings.php"))
{
 // reindirizzamento alla home page in caso di login mancato
 header("Location: nm-install.php");
}

require("lib/controller.class.php");
$app = new Controller;

if(isset($_POST['user']) && isset($_POST['pass'])) {
	if($app->model->users->login($_POST['user'],$_POST['pass'])) {
		Model::direct("nm-main.php");
	} else throw new Exception("User not found!");
}
$app->assign("label","Login");
$app->display("login");
?>
