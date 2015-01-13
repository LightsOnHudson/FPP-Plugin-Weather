<?php

//$DEBUG=true;

include_once "/opt/fpp/www/common.php";
include_once "functions.inc.php";


$pluginName = "Weather";


$logFile = $settings['logDirectory']."/".$pluginName.".log";


if(isset($_POST['submit']))
{

//	echo "Writring config fie <br/> \n";
	
	
	WriteSettingToFile("ENABLED",urlencode($_POST["ENABLED"]),$pluginName);
	WriteSettingToFile("SEPARATOR",urlencode($_POST["SEPARATOR"]),$pluginName);
	WriteSettingToFile("CITY",urlencode($_POST["CITY"]),$pluginName);
	WriteSettingToFile("STATE",$_POST["STATE"],$pluginName);
	
	WriteSettingToFile("LAST_READ",urlencode($_POST["LAST_READ"]),$pluginName);

}

	
	$ENABLED = urldecode(ReadSettingFromFile("ENABLED",$pluginName));
	$SEPARATOR = urldecode(ReadSettingFromFile("SEPARATOR",$pluginName));
	$CITY= urldecode(ReadSettingFromFile("CITY",$pluginName));
	$STATE= ReadSettingFromFile("STATE",$pluginName);
	
	
	
	$LAST_READ = urldecode(ReadSettingFromFile("LAST_READ",$pluginName));

	if($SEPARATOR == "" || strlen($SEPARATOR)>1) {
		$SEPARATOR="|";
	}
	//echo "sports read: ".$SPORTS."<br/> \n";
	
	if((int)$LAST_READ == 0 || $LAST_READ == "") {
		$LAST_READ=0;
		
	}
	
?>

<html>
<head>
</head>

<div id="<?echo $pluginName;?>" class="settings">
<fieldset>
<legend><?php echo $pluginName;?> Support Instructions</legend>

<p>Known Issues:
<ul>
<li>NONE</li>
</ul>

<p>Configuration:
<ul>
<li>Configure your City & 2 Character State & Separator Character to display</li>
</ul>
<ul>
</ul>



<form method="post" action="http://<? echo $_SERVER['SERVER_NAME']?>/plugin.php?plugin=<?echo $pluginName;?>&page=plugin_setup.php">


<?
echo "<input type=\"hidden\" name=\"LAST_READ\" value=\"".$LAST_READ."\"> \n";
echo "<p/>\n";

$restart=0;
$reboot=0;

echo "ENABLE PLUGIN: ";

if($ENABLED== 1 || $ENABLED == "on") {
		echo "<input type=\"checkbox\" checked name=\"ENABLED\"> \n";
//PrintSettingCheckbox("Radio Station", "ENABLED", $restart = 0, $reboot = 0, "ON", "OFF", $pluginName = $pluginName, $callbackName = "");
	} else {
		echo "<input type=\"checkbox\"  name=\"ENABLED\"> \n";
}

echo "<p/> \n";


if($DEBUG) {
	echo "RESET LAST READ INDEX: ";

		echo "<br/> \n";
	echo "Last read: ".$LAST_READ. ": ";
	echo "<input type=\"checkbox\"  name=\"RESET_LAST_READ\"> \n";
	//PrintSettingCheckbox("Radio Station", "ENABLED", $restart = 0, $reboot = 0, "ON", "OFF", $pluginName = $pluginName, $callbackName = "");


	echo "<p/> \n";
}	

	echo "<p/> \n";
	
	echo "City: \n";
	
	echo "<input type=\"text\" name=\"CITY\" size=\"16\" value=\"".$CITY."\"> \n";
	
	echo "<p/> \n";
	
	echo "State: \n";
	
	echo "<input type=\"text\" name=\"STATE\" size=\"2\" value=\"".$STATE."\"> \n";
	
	
	echo "<p/> \n";
 echo "Separator: \n";

        echo "<input type=\"text\" name=\"SEPARATOR\" size=\"2\" value=\"".$SEPARATOR."\"> \n";	

?>
<p/>
<input id="submit_button" name="submit" type="submit" class="buttons" value="Save Config">

</form>


<p>To report a bug, please file it against the sms Control plugin project on Git: https://github.com/LightsOnHudson/FPP-Plugin-Weather

</fieldset>
</div>
<br />
</html>
