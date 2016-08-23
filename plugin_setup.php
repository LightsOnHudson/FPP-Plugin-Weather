<?php

//$DEBUG=true;

include_once "/opt/fpp/www/common.php";
include_once 'functions.inc.php';
include_once 'commonFunctions.inc.php';


$pluginName = "Weather";

$gitURL = "https://github.com/LightsOnHudson/FPP-Plugin-Weather.git";


$pluginUpdateFile = $settings['pluginDirectory']."/".$pluginName."/"."pluginUpdate.inc";


$logFile = $settings['logDirectory']."/".$pluginName.".log";


logEntry("plugin update file: ".$pluginUpdateFile);


if(isset($_POST['updatePlugin']))
{
	$updateResult = updatePluginFromGitHub($gitURL, $branch="master", $pluginName);

	echo $updateResult."<br/> \n";
}


if(isset($_POST['submit']))
{

//	echo "Writring config fie <br/> \n";
	
	
	//WriteSettingToFile("ENABLED",urlencode($_POST["ENABLED"]),$pluginName);
	WriteSettingToFile("SEPARATOR",urlencode($_POST["SEPARATOR"]),$pluginName);
	WriteSettingToFile("CITY",urlencode($_POST["CITY"]),$pluginName);
	WriteSettingToFile("STATE",urlencode($_POST["STATE"]),$pluginName);
	
	WriteSettingToFile("LAST_READ",urlencode($_POST["LAST_READ"]),$pluginName);
	WriteSettingToFile("API_KEY",urlencode($_POST["API_KEY"]),$pluginName);
	
	WriteSettingToFile("INCLUDE_TEMP",urlencode($_POST["INCLUDE_TEMP"]),$pluginName);
	WriteSettingToFile("INCLUDE_WIND",urlencode($_POST["INCLUDE_WIND"]),$pluginName);
	WriteSettingToFile("INCLUDE_HUMIDITY",urlencode($_POST["INCLUDE_HUMIDITY"]),$pluginName);
	WriteSettingToFile("INCLUDE_LOCALE",urlencode($_POST["INCLUDE_LOCALE"]),$pluginName);
	WriteSettingToFile("PRE_TEXT",urlencode($_POST["PRE_TEXT"]),$pluginName);
	WriteSettingToFile("POST_TEXT",urlencode($_POST["POST_TEXT"]),$pluginName);
	WriteSettingToFile("TEMP_TYPE",urlencode($_POST["TEMP_TYPE"]),$pluginName);
	WriteSettingToFile("INCLUDE_DEGREE_SYMBOL",urlencode($_POST["INCLUDE_DEGREE_SYMBOL"]),$pluginName);
}

	$ENABLED = urldecode($pluginSettings['ENABLED']);
	
	$SEPARATOR = urldecode($pluginSettings['SEPARATOR']);
	$CITY=  urldecode($pluginSettings['CITY']);
	$STATE=  urldecode($pluginSettings['STATE']);
	$API_KEY = urldecode($pluginSettings['API_KEY']);
	
	$INCLUDE_WIND = urldecode($pluginSettings['INCLUDE_WIND']);
	$INCLUDE_TEMP = urldecode($pluginSettings['INCLUDE_TEMP']);
	$INCLUDE_HUMIDITY = urldecode($pluginSettings['INCLUDE_HUMIDITY']);
	$INCLUDE_LOCALE = urldecode($pluginSettings['INCLUDE_LOCALE']);
	$PRE_TEXT = urldecode($pluginSettings['PRE_TEXT']);
	$POST_TEXT = urldecode($pluginSettings['POST_TEXT']);
	$TEMP_TYPE = urldecode($pluginSettings['TEMP_TYPE']);
	$INCLUDE_DEGREE_SYMBOL = urldecode($pluginSettings['INCLUDE_DEGREE_SYMBOL']);
	
	$LAST_READ = urldecode($pluginSettings['LAST_READ']);//("LAST_READ",$pluginName));

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
<li>Visit http://home.openweathermap.org/ to sign up for an API KEY</li>
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

//if($ENABLED== 1 || $ENABLED == "on") {
	//	echo "<input type=\"checkbox\" checked name=\"ENABLED\"> \n";
	//just use the name of the plugin. Because it will pop up with 'enabled / disabled ' suffix
PrintSettingCheckbox($pluginName." Plugin", "ENABLED", $restart = 0, $reboot = 0, "ON", "OFF", $pluginName = $pluginName, $callbackName = "");
	//} else {
		//echo "<input type=\"checkbox\"  name=\"ENABLED\"> \n";
//}

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

echo "Pre Text (Text to display ahead of Weather data): \n";

echo "<input type=\"text\" name=\"PRE_TEXT\" size=\"16\" value=\"".$PRE_TEXT."\"> \n";
//PrintSettingText("CITY", $restart = 0, $reboot = 0, $maxlength = 32, $size = 32, $pluginName);
//PrintSettingSave("CITY", "CITY", $restart = 1, $reboot = 0, $pluginName, $callbackName = "");
//PrintSettingSave($title, $setting, $restart = 1, $reboot = 0, $pluginName = "", $callbackName = "");
echo "<p/> \n";

echo "Post Text (Text to display after weather data): \n";

echo "<input type=\"text\" name=\"POST_TEXT\" size=\"16\" value=\"".$POST_TEXT."\"> \n";
//PrintSettingText("CITY", $restart = 0, $reboot = 0, $maxlength = 32, $size = 32, $pluginName);
//PrintSettingSave("CITY", "CITY", $restart = 1, $reboot = 0, $pluginName, $callbackName = "");
//PrintSettingSave($title, $setting, $restart = 1, $reboot = 0, $pluginName = "", $callbackName = "");

	echo "<p/> \n";
	
	echo "City: \n";
	
	echo "<input type=\"text\" name=\"CITY\" size=\"16\" value=\"".$CITY."\"> \n";
	//PrintSettingText("CITY", $restart = 0, $reboot = 0, $maxlength = 32, $size = 32, $pluginName);
	//PrintSettingSave("CITY", "CITY", $restart = 1, $reboot = 0, $pluginName, $callbackName = "");
	//PrintSettingSave($title, $setting, $restart = 1, $reboot = 0, $pluginName = "", $callbackName = "");
	echo "<p/> \n";
	
	
	echo "State: \n";
	
	echo "<input type=\"text\" name=\"STATE\" size=\"2\" value=\"".$STATE."\"> \n";
	//PrintSettingText("STATE", $restart = 0, $reboot = 0, $maxlength = 5, $size = 5, $pluginName);
	
	echo "<p/>\n";
	
	echo "Temperature type: \n";
	
	echo "<select name=\"TEMP_TYPE\"> \n";
	
		switch ($TEMP_TYPE)
		{
			case "F":
				echo "<option selected value=\"".$TEMP_TYPE."\">Farenheit</option> \n";
				 		echo "<option value=\"C\">Celcius</option> \n";
				break;
				
			case "C":
				echo "<option selected value=\"".$TEMP_TYPE."\">Celcius</option> \n";
				echo "<option value=\"F\">Farenheit</option> \n";
				break;
					
			 default:
				echo "<option value=\"C\">Celcius</option> \n";
				echo "<option value=\"F\">Farenheit</option> \n";
				break;
	
		}
	

	
	echo "</select> \n";
	
	
	echo "<p/> \n";
	
	echo "Include Locale in output: \n";
	
	PrintSettingCheckbox("Include Locale", "INCLUDE_LOCALE", $restart = 0, $reboot = 0, "1", "0", $pluginName = $pluginName, $callbackName = "");
	
	// PrintSettingText("SEPARATOR", $restart = 0, $reboot = 0, $maxlength = 3, $size = 3, $pluginName);
	
	echo "<p/> \n";
 echo "Separator: \n";

       echo "<input type=\"text\" name=\"SEPARATOR\" size=\"2\" value=\"".$SEPARATOR."\"> \n";	
       // PrintSettingText("SEPARATOR", $restart = 0, $reboot = 0, $maxlength = 3, $size = 3, $pluginName);
        
        echo "<p/> \n";
        
        echo "Include Temp: ";
        
        //if($INCLUDE_TEMP== 1 || $INCLUDE_TEMP == "on") {
        	//echo "<input type=\"checkbox\" checked name=\"INCLUDE_TEMP\"> \n";
        	PrintSettingCheckbox("Include Temp", "INCLUDE_TEMP", $restart = 0, $reboot = 0, "1", "0", $pluginName = $pluginName, $callbackName = "");
        //} else {
        	//echo "<input type=\"checkbox\"  name=\"INCLUDE_TEMP\"> \n";
        //}
        
        echo "<p/> \n";
        
        echo "Include Wind: ";
        
      //  if($INCLUDE_WIND == 1 || $INCLUDE_WIND == "on") {
        //	echo "<input type=\"checkbox\" checked name=\"INCLUDE_WIND\"> \n";
        	PrintSettingCheckbox("Include Wind", "INCLUDE_WIND", $restart = 0, $reboot = 0, "1", "0", $pluginName = $pluginName, $callbackName = "");
        //} else {
        //	echo "<input type=\"checkbox\"  name=\"INCLUDE_WIND\"> \n";
        //}
        
        echo "<p/> \n";
        
        
        echo "Include Humidity: ";
        
       // if($INCLUDE_HUMIDITY == 1 || $INCLUDE_HUMIDITY == "on") {
        //	echo "<input type=\"checkbox\" checked name=\"INCLUDE_HUMIDITY\"> \n";
        	PrintSettingCheckbox("Include Humidity", "INCLUDE_HUMIDITY", $restart = 0, $reboot = 0, "1", "0", $pluginName = $pluginName, $callbackName = "");
        //} else {
        	//echo "<input type=\"checkbox\"  name=\"INCLUDE_HUMIDITY\"> \n";
        //}
        
        echo "<p/> \n";

        echo "Include Degree Symbol (&deg): ";
        
        // if($INCLUDE_HUMIDITY == 1 || $INCLUDE_HUMIDITY == "on") {
        //	echo "<input type=\"checkbox\" checked name=\"INCLUDE_HUMIDITY\"> \n";
        PrintSettingCheckbox("Include Degree Symbol", "INCLUDE_DEGREE_SYMBOL", $restart = 0, $reboot = 0, "1", "0", $pluginName = $pluginName, $callbackName = "");
        //} else {
        //echo "<input type=\"checkbox\"  name=\"INCLUDE_HUMIDITY\"> \n";
        //}
        
        echo "<p/> \n";
        
        echo "API KEY: \n";
        
        echo "<input type=\"text\" name=\"API_KEY\" size=\"64\" value=\"".$API_KEY."\"> \n";
       // PrintSettingText("API_KEY", $restart = 1, $reboot = 0, $maxlength = 64, $size = 64, $pluginName);
        
        echo "<p/> \n";
?>
<p/>
<input id="submit_button" name="submit" type="submit" class="buttons" value="Save Config">
<?
 if(file_exists($pluginUpdateFile))
 {
 	//echo "updating plugin included";
	include $pluginUpdateFile;
}
?>
</form>

<p>To report a bug, please file it against the sms Control plugin project on Git: https://github.com/LightsOnHudson/FPP-Plugin-Weather

</fieldset>
</div>
<br />
</html>
