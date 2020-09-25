<?php

//$DEBUG=true;

include_once "/opt/fpp/www/common.php";
include_once 'functions.inc.php';
include_once 'commonFunctions.inc.php';


//$pluginName = "Weather";
$pluginName = basename(dirname(__FILE__));  //pjd 7-14-2019   added per dkulp

$gitURL = "https://github.com/FalconChristmas/FPP-Plugin-Weather.git";

$pluginUpdateFile = $settings['pluginDirectory']."/".$pluginName."/"."pluginUpdate.inc";
$ScriptPath= "/home/fpp/media/scripts/";
$PluginPath= $settings['pluginDirectory']."/".$pluginName."/";
$logFile = $settings['logDirectory']."/".$pluginName.".log";

logEntry("$pluginUpdateFile = ".$pluginUpdateFile);

clearstatcache();

if (!(file_exists($ScriptPath."getLocalWeather.sh"))){
	logEntry("GetLocalWeather.sh does not exist, copying scripts");
	copy ($PluginPath."getLocalWeather.sh", "/home/fpp/media/scripts/getLocalWeather.sh");
	
}
if (!(file_exists($ScriptPath."RUN-MATRIX.sh"))){
	logEntry("RUN-MATRIX.sh does not exist, copying scripts");
	copy ($PluginPath."RUN-MATRIX.sh", "/home/fpp/media/scripts/RUN-MATRIX.sh");
}


if(isset($_POST['updatePlugin']))
{
	$updateResult = updatePluginFromGitHub($gitURL, $branch="master", $pluginName);

	echo $updateResult."<br/> \n";
}


if(isset($_POST['submit']))
{

//	"Writing config file 
		
	
	WriteSettingToFile("SEPARATOR",urlencode($_POST["SEPARATOR"]),$pluginName); 
	WriteSettingToFile("CITY",trim(urlencode($_POST["CITY"])),$pluginName);           
	WriteSettingToFile("STATE",trim(urlencode($_POST["STATE"])),$pluginName);
	WriteSettingToFile("LAST_READ",urlencode($_POST["LAST_READ"]),$pluginName);
	WriteSettingToFile("API_KEY",urlencode($_POST["API_KEY"]),$pluginName);
	WriteSettingToFile("PRE_TEXT",urlencode($_POST["PRE_TEXT"]),$pluginName);
	WriteSettingToFile("POST_TEXT",urlencode($_POST["POST_TEXT"]),$pluginName);
	WriteSettingToFile("TEMP_TYPE",urlencode($_POST["TEMP_TYPE"]),$pluginName);
	WriteSettingToFile("COUNTRY",urlencode($_POST["country"]),$pluginName);
	
	$pluginConfigFile = $settings['configDirectory'] . "/plugin." . $pluginName;
    	if (file_exists($pluginConfigFile)) {
        	$pluginSettings = parse_ini_file($pluginConfigFile);
    	}
}

	$ENABLED = urldecode($pluginSettings['ENABLED']);
	
	$SEPARATOR = urldecode($pluginSettings['SEPARATOR']);
	$CITY=  trim(urldecode($pluginSettings['CITY']));
	$STATE=  trim(urldecode($pluginSettings['STATE']));
	$API_KEY = urldecode($pluginSettings['API_KEY']);
	
	$INCLUDE_WIND = urldecode($pluginSettings['INCLUDE_WIND']);
	$INCLUDE_TEMP = urldecode($pluginSettings['INCLUDE_TEMP']);
	$INCLUDE_HUMIDITY = urldecode($pluginSettings['INCLUDE_HUMIDITY']);
	$INCLUDE_LOCALE = urldecode($pluginSettings['INCLUDE_LOCALE']);
	$PRE_TEXT = urldecode($pluginSettings['PRE_TEXT']);
	$POST_TEXT = urldecode($pluginSettings['POST_TEXT']);
	$TEMP_TYPE = urldecode($pluginSettings['TEMP_TYPE']);
	$INCLUDE_DEGREE_SYMBOL = urldecode($pluginSettings['INCLUDE_DEGREE_SYMBOL']);
	$COUNTRY= urldecode($pluginSettings['COUNTRY']);
	if (strlen($COUNTRY)<1){ //empty setting, setting default
		$COUNTRY="US";
	}
	$IMMEDIATE_OUTPUT= urldecode($pluginSettings['IMMEDIATE_OUTPUT']);
	if (strlen($IMMEDIATE_OUTPUT)<1){ //empty setting, setting default
		$IMMEDIATE_OUTPUT="ON";
	}
	$LATITUDE= GetSettingValue("Latitude");
	$LONGITUDE= GetSettingValue("Longitude");
	$LAST_READ = urldecode($pluginSettings['LAST_READ']);//("LAST_READ",$pluginName));

	//set hide/unhide value for country <div>
	if ($COUNTRY == "US") {
		$USDIV = "display:block";
		$OTHDIV = "display:none";
	} else {
		$USDIV = "display:none";
		$OTHDIV = "display:block";
	}
	if(strlen($SEPARATOR)>1) { //change to allow for no separator
		$SEPARATOR="|";
	}
	
	
	if((int)$LAST_READ == 0 || $LAST_READ == "") {
		$LAST_READ=0;
		
	}
	
?>

<!DOCTYPE html>
<head>
</head>
<body>
<div id="<?echo $pluginName;?>" class="settings">
<fieldset>
<legend><?php echo $pluginName;?> Support Instructions</legend>

<p>Known Issues:
<ul>
<li>None</li>
</ul>

<p>Configuration:
<ul>
<li>Configure your City & 2 Character State & Separator Character to display</li>
<li>If you are not in the US, you can enter your Latitude and Longitude settings to get your local weather.<p>
Link to settings: <a href=settings.php?tab=9> System tab</a>
<li>Visit <a href="http://home.openweathermap.org/" target="_blank">http://home.openweathermap.org/</a> to sign up for an API KEY</li>
</ul>
<ul>
</ul>



<form method="post" action="http://<? echo $_SERVER['SERVER_ADDR']?>/plugin.php?plugin=<?echo $pluginName;?>&page=plugin_setup.php">


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
?>
<input type="text" name="PRE_TEXT" size="16" value="<? echo $PRE_TEXT; ?>"> <p>

Post Text (Text to display after weather data): 

<input type="text" name="POST_TEXT" size="16" value="<? echo $POST_TEXT; ?>"> <p>

Select your country 
<input type="radio" id="us" name="country" value="US" onclick="setRadioUS()" <? if ($COUNTRY=="US") echo "checked"; ?>>
<label for="us">US</label>	
<input type="radio" id="other" name="country" value="Other" onclick="setRadioOther()"<? if ($COUNTRY=="Other") echo "checked"; ?>>
<label for="other">Other</label><p/>

<div id="ifUS" style="<? echo "$USDIV"; ?>">
	City: <input type="text" name="CITY" size="16" value="<? echo $CITY; ?>"> <p>
	State: <input type="text" name="STATE" size="2" value="<? echo $STATE; ?>"> <p>
</div>

<div id="ifOther" style="<? echo "$OTHDIV"; ?>">
<h4>YMake sure your Latitude and Longitude settings are correct. To change them, go to  the FPP Settings page on the <a href=settings.php?tab=9> System tab</a></h4> <p>
	Latitude: <input type="text" name="LATITUDE" size="16"  value="<? echo $LATITUDE; ?>" disabled> <p>
	Longitude: <input type="text" name="LONGITUDE" size="16" value="<? echo $LONGITUDE; ?>" disabled> <p>
</div>

<?
	echo "Temperature type: \n";
	
	echo "<select name=\"TEMP_TYPE\"> \n";
	
		switch ($TEMP_TYPE)
		{
			case "F":
				echo "<option selected value=\"".$TEMP_TYPE."\">Fahrenheit</option> \n";
				 		echo "<option value=\"C\">Celcius</option> \n";
				break;
				
			case "C":
				echo "<option selected value=\"".$TEMP_TYPE."\">Celcius</option> \n";
				echo "<option value=\"F\">Fahrenheit</option> \n";
				break;
					
			 default:
				echo "<option value=\"C\">Celcius</option> \n";
				echo "<option value=\"F\">Fahrenheit</option> \n";
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
	echo "Immediately output to Matrix (Run MATRIX plugin): ";

	//if($IMMEDIATE_OUTPUT == "on" || $IMMEDIATE_OUTPUT == 1) {
	//	echo "<input type=\"checkbox\" checked name=\"IMMEDIATE_OUTPUT\"> \n";
	PrintSettingCheckbox("Immediate output to Matrix", "IMMEDIATE_OUTPUT", $restart = 0, $reboot = 0, "ON", "OFF", $pluginName = $pluginName, $callbackName = "");
	//} else {
	//echo "<input type=\"checkbox\"  name=\"IMMEDIATE_OUTPUT\"> \n";
	//}
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

<p>To report a bug, please file it against the project on Git: https://github.com/FalconChristmas/FPP-Plugin-Weather

</fieldset>
</div>
<br />
<script> 
function setRadioUS() {
	document.getElementById('ifUS').style.display = "block";
	document.getElementById('ifOther').style.display = "none";
}
function setRadioOther() {
	document.getElementById('ifUS').style.display = "none";
	document.getElementById('ifOther').style.display = "block";
}
</script> 
</body>
</html>
