#!/usr/bin/php
<?
error_reporting(0);

$pluginName ="Weather";
$myPid = getmypid();

$messageQueue_Plugin = "MessageQueue";
$MESSAGE_QUEUE_PLUGIN_ENABLED=false;

$DEBUG=false;

$skipJSsettings = 1;
include_once("/opt/fpp/www/config.php");
include_once("/opt/fpp/www/common.php");
include_once("functions.inc.php");

require ("lock.helper.php");

define('LOCK_DIR', '/tmp/');
define('LOCK_SUFFIX', '.lock');

$logFile = $settings['logDirectory']."/".$pluginName.".log";


$WEATHER_URL="http://api.openweathermap.org/data/2.5/weather?q=";

$messageQueuePluginPath = $pluginDirectory."/".$messageQueue_Plugin."/";

$messageQueueFile = urldecode(ReadSettingFromFile("MESSAGE_FILE",$messageQueue_Plugin));

if(file_exists($messageQueuePluginPath."functions.inc.php"))
        {
                include $messageQueuePluginPath."functions.inc.php";
                $MESSAGE_QUEUE_PLUGIN_ENABLED=true;

        } else {
                logEntry("Message Queue Plugin not installed, some features will be disabled");
        }

        $pluginConfigFile = $settings['configDirectory'] . "/plugin." .$pluginName;
        if (file_exists($pluginConfigFile))
        	$pluginSettings = parse_ini_file($pluginConfigFile);

$ENABLED = urldecode($pluginSettings['ENABLED']);//("ENABLED",$pluginName));

//echo "ENABLED: ".$ENABLED."\n";


if(($pid = lockHelper::lock()) === FALSE) {
        exit(0);
}

        if($ENABLED != "on" && $ENABLED != "1") {
                logEntry("Plugin Status: DISABLED Please enable in Plugin Setup to use & Restart FPPD Daemon");
                lockHelper::unlock();
                exit(0);
        }
        

        
	$SEPARATOR = urldecode($pluginSettings['SEPARATOR']);//("SEPARATOR",$pluginName));
	$CITY= urldecode($pluginSettings['CITY']);//("CITY",$pluginName));
	$STATE= urldecode($pluginSettings['STATE']);//("STATE",$pluginName);
	$INCLUDE_WIND = urldecode($pluginSettings['INCLUDE_WIND']);
	$INCLUDE_TEMP = urldecode($pluginSettings['INCLUDE_TEMP']);
	$INCLUDE_HUMIDITY = urldecode($pluginSettings['INCLUDE_HUMIDITY']);
	$INCLUDE_LOCALE = urldecode($pluginSettings['INCLUDE_LOCALE']);
	$INCLUDE_DEGREE_SYMBOL = urldecode($pluginSettings['INCLUDE_DEGREE_SYMBOL']);
	//F or C
	$TEMP_TYPE = urldecode($pluginSettings['TEMP_TYPE']);
	$API_KEY= urldecode($pluginSettings['API_KEY']);

	$PRE_TEXT= urldecode($pluginSettings['PRE_TEXT']);
	$POST_TEXT= urldecode($pluginSettings['POST_TEXT']);

$weatherText = getNorthPole();

	
	logEntry("Current temp before conversion: ".$currentTemp);
	
	switch($TEMP_TYPE) {
		
		case "F":
			$currentTemp = round((($currentTemp-273.15)*1.8)+32,1);
			break;
			
		case "C":
			
			$currentTemp = round(($currentTemp-273.15),1);
			break;
	}

	if($INCLUDE_DEGREE_SYMBOL == 1 || $INCLUDE_DEGREE_SYMBOL == "on")
		$currentTemp .= htmlentities("&deg");

	logEntry("Current temp after conversion: ".$currentTemp);

	
	//MessageText=""
	$messageText="";
	
	if(trim($PRE_TEXT) != "") {
		$messageText .= $PRE_TEXT;
	}
	
	$messageText .= $weatherText;
	
		if(trim($POST_TEXT) != "") {
			$messageText .= " ".$SEPARATOR." " . $POST_TEXT;
		}
	//$messageText = "Temp: ".$currentTemp." ".$SEPARATOR." Wind: ".$currentWindDirection." ".$currentWind." ".$SEPARATOR." Humidity: ".$humidity."%";
	//echo "messageText: ".$messageText."\n";

	logEntry("Weather string: ".$messageText);
	
	addNewMessage($messageText,$pluginName,$pluginData=$CITY." ".$STATE);

lockHelper::unlock();

?>
