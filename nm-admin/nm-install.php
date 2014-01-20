<?php

$settingfile=file_get_contents("nm-settings-pre.php");

if(isset($_POST) && isset($_POST['nomehost']) && isset($_POST['nomeuser']) && isset($_POST['password']) && isset($_POST['nomedb'])) {

  $patterns[0] = "/define\(\"DB_HOST\"\,\"(.+?)\"\)\;/";
  $patterns[1] = "/define\(\"DB_USER\"\,\"(.+?)\"\)\;/";
  $patterns[2] = "/define\(\"DB_PASS\"\,\"(.+?)\"\)\;/";
  $patterns[3] = "/define\(\"DB_NAME\"\,\"(.+?)\"\)\;/";

  $replacements[0] = 'define("DB_HOST","'.$_POST['nomehost'].'");';
  $replacements[1] = 'define("DB_USER","'.$_POST['nomeuser'].'");';
  $replacements[2] = 'define("DB_PASS","'.$_POST['password'].'");';
  $replacements[3] = 'define("DB_NAME","'.$_POST['nomedb'].'");';

  define("DB_HOST",$_POST['nomehost']); //Your database host
  define("DB_USER",$_POST['nomeuser']); //Your username to the database
  define("DB_PASS",$_POST['password']); //Your password for the account
  define("DB_NAME",$_POST['nomedb']); //The database name

  include("lib/proxy/mysqli.class.php");
  if(mysql::init()) {
  
    $identificatore = fopen("nm-settings.php", "w");

    if (!fwrite($identificatore, preg_replace($patterns, $replacements, $settingfile))){} else {

      fclose($identificatore);

    }
    
    mysql::import_sql_file("sql/install.sql");
    header("Location: index.php");
  }

}

  include("lib/templates/install.template.html");
?>
