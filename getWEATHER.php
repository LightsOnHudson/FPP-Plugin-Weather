#!/usr/bin/php
<?
error_reporting(0);

//$pluginName ="Weather";
$pluginName = basename(dirname(__FILE__));  //pjd 7-14-2019   added per dkulp
$myPid = getmypid();


// Fix for backward compatibility added this for 2.7 path 7/17/2019
//$messageQueue_Plugin = "MessageQueue";
if (strpos($pluginName, "FPP-Plugin") !== false) {
   $messageQueue_Plugin = "FPP-Plugin-MessageQueue";
}

$MESSAGE_QUEUE_PLUGIN_ENABLED=false;

$DEBUG=false;
$WeatherVersion = "2.0";

$skipJSsettings = 1;
include_once("/opt/fpp/www/config.php");
include_once("/opt/fpp/www/common.php");
include_once("functions.inc.php");
include_once("commonFunctions.inc.php");
require ("lock.helper.php");

define('LOCK_DIR', '/tmp/');
define('LOCK_SUFFIX', $pluginName.'.lock');

$MATRIX_MESSAGE_PLUGIN_NAME = "MatrixMessage"; 
if (strpos($pluginName, "FPP-Plugin") !== false) {
    $MATRIX_MESSAGE_PLUGIN_NAME = "FPP-Plugin-Matrix-Message";
}
//page name to run the matrix code to output to matrix (remote or local);
$MATRIX_EXEC_PAGE_NAME = "matrix.php"; 
$MATRIX_LOCATION = "127.0.0.1"; 

$logFile = $settings['logDirectory']."/".$pluginName.".log";

// added some logging pjd 7/15/2019
logEntry("Weather_PLUGIN: MessageQueue Plugin: ".$messageQueue_Plugin);


$WeatherVersion = "2.0";

$WEATHER_URL="http://api.openweathermap.org/data/2.5/weather?";

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

$ENABLED = urldecode($pluginSettings['ENABLED']);		//("ENABLED",$pluginName));

//echo "ENABLED: ".$ENABLED."\n";


if(($pid = lockHelper::lock()) === FALSE) {
        exit(0);
}

        if($ENABLED != "ON" && $ENABLED != "1") {
                logEntry("Plugin Status: DISABLED Please enable in Plugin Setup to use & Restart FPPD Daemon");
                lockHelper::unlock();
                exit(0);
        }
        

        
	$SEPARATOR = urldecode($pluginSettings['SEPARATOR']);						//("SEPARATOR",$pluginName)); y
	$CITY= urldecode($pluginSettings['CITY']);									//("CITY",$pluginName)); y
	$STATE= urldecode($pluginSettings['STATE']);								//("STATE",$pluginName); y
	$INCLUDE_WIND = urldecode($pluginSettings['INCLUDE_WIND']);					// 
	$INCLUDE_TEMP = urldecode($pluginSettings['INCLUDE_TEMP']);					// 
	$INCLUDE_HUMIDITY = urldecode($pluginSettings['INCLUDE_HUMIDITY']);			// 
	$INCLUDE_LOCALE = urldecode($pluginSettings['INCLUDE_LOCALE']);				// 
	$INCLUDE_DEGREE_SYMBOL = urldecode($pluginSettings['INCLUDE_DEGREE_SYMBOL']);	// 
	//F or C
	$TEMP_TYPE = urldecode($pluginSettings['TEMP_TYPE']);						// 
	$API_KEY= urldecode($pluginSettings['API_KEY']);							// 
	$PRE_TEXT= urldecode($pluginSettings['PRE_TEXT']);							// 
	$POST_TEXT= urldecode($pluginSettings['POST_TEXT']);						// 
	$LATITUDE=GetSettingValue(Latitude, $default = '', $prefix = '', $suffix = '');
	$LONGITUDE=GetSettingValue(Longitude, $default = '', $prefix = '', $suffix = '');
	$COUNTRY= urldecode($pluginSettings['COUNTRY']);
	$IMMEDIATE_OUTPUT=urldecode($pluginSettings['IMMEDIATE_OUTPUT']);
		
	// set up DB connection
	$MESSAGE_FILE= $settings['configDirectory']."/FPP.".$messageQueue_Plugin.".db";
	logEntry("Weather_PLUGIN: Messsage File: ".$MESSAGE_FILE);					// pjd 7/15/2019 added for debugging

	//echo "PLUGIN DB:NAME: ".$Plugin_DBName;
	
	$db = new SQLite3($MESSAGE_FILE) or die('Unable to open database');
	
	//create the tables if not exist
	createTables();
	
	if ($COUNTRY=="US"){
		$WEATHER_URL .= "q=".$CITY.",".$STATE.",&APPID=".$API_KEY;	//pjd 7/15/2015 added comma before &APPID
	} else{ //Other country
		if (strlen($LONGITUDE)<2){ //If coordinates are not set- set them for the North Pole
			logEntry("*********ERROR No Latitude or Longitude set- Using coordinates for North Pole");
			$LATITUDE="90.000";
			$LONGITUDE="-135.000";
		}
		if ($LATITUDE< -90 || $LATITUDE >90){
			logEntry("**********Error Latitude out of range");
		}
		if ($LONGITUDE< -180 || $LATITUDE >180){
			logEntry("**********Error Latitude out of range");
		}
		$WEATHER_URL .= "lat=".$LATITUDE."&lon=".$LONGITUDE."&APPID=".$API_KEY;
	}
	
	
	logEntry("Weather_PLUGIN: WEATHER_URL: ".$WEATHER_URL);	  // pjd 7/15/2019 added for debugging
        //  Initiate curl
        $ch = curl_init();
        // Disable SSL verification
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        // Will return the response, if false it print the response
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // Set the url
        curl_setopt($ch, CURLOPT_URL,$WEATHER_URL);
        // Execute
        $result=curl_exec($ch);
        // Closing
        curl_close($ch);

        // Will dump a beauty json :3
		
	logEntry("WEATHER_PLUGIN: weatherData: ".$result);	  // pjd 7/15/2019 added for debugging
	$weatherData= json_decode($result,true);
	


	//print_r($weatherData);

	$currentTemp = $weatherData['main']['temp'];
	
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
		$currentTemp .= "°";

	logEntry("Current temp after conversion: ".$currentTemp);
	
	//echo "current temp; ".$currentTemp."\n";
	$currentWind = (int)$weatherData['wind']['speed']." MPH";
	$currentWindDirection = $weatherData['wind']['deg'];

	//$currentWindDirection =349;

	if($currentWindDirection > 348 && $currentWindDirection <=359) {
		$currentWindDirection = "N";

	} elseif($currentWindDirection >=0 && $currentWindDirection <=45) {
		$currentWindDirection = "N";

	} elseif($currentWindDirection > 45 && $currentWindDirection <=68) {
		$currentWindDirection = "NE";

	} elseif($currentWindDirection > 68 && $currentWindDirection <= 112) {
		$currentWindDirection = "E";

	} elseif($currentWindDirection > 112 && $currentWindDirection <=168) {
		$currentWindDirection = "SE";

	} elseif($currentWindDirection > 168 && $currentWindDirection <= 192) {
		$currentWindDirection = "S";

	} elseif($currentWindDirection > 192 && $currentWindDirection <= 258) {
		$currentWindDirection = "SW";
	
	} elseif($currentWindDirection > 258 && $currentWindDirection <= 282) {
		$currentWindDirection = "W";

	} elseif($currentWindDirection > 282 && $currentWindDirection <= 348) {
		$currentWindDirection = "NW";
	} 

	//echo "current wind: ".$currentWind." direction: ".$currentWindDirection."\n";

	$clouds = $weatherData['clouds']['all'];

	if($clouds > 1) {
		$clouds = (int)$clouds."% cloud cover";
	}

	//echo "Clouds: ".$clouds."\n";

	$humidity = $weatherData['main']['humidity'];

	//echo "humidity: ".$humidity."%\n";
	
	//MessageText=""
	$messageText="";
	if (($COUNTRY=="Other" && strlen(GetSettingValue("Latitude"))!=0)|| ($COUNTRY=="US")){ //Valid configuration-use settings
		if(trim($PRE_TEXT) != "") {
			$messageText .= $PRE_TEXT;
		}
	
		if($INCLUDE_LOCALE == 1 || $INCLUDE_LOCALE == "on")
			$messageText .= " ". $SEPARATOR." ". $CITY.",".$STATE;
	
		if($INCLUDE_TEMP == 1 || $INCLUDE_TEMP == "on")
			$messageText .= " ". $SEPARATOR." Temp: ".$currentTemp;

		if($INCLUDE_WIND == 1 || $INCLUDE_WIND == "on")
			$messageText .= " ". $SEPARATOR." Wind: ".$currentWindDirection." ".$currentWind;

		if($INCLUDE_HUMIDITY == 1 || $INCLUDE_HUMIDITY == "on") 
			$messageText .= " ".$SEPARATOR." Humidity: ".$humidity."\%";
	
		if(trim($POST_TEXT) != "") {
			$messageText .= " ".$SEPARATOR." " . $POST_TEXT;
		}
	}else{ //Latitude and Longitude are not set so use the North Pole instead.
		logEntry("************ERROR No Latitude ot Longitude set, use North Pole");
		$messageText= "It is Temp: ".$currentTemp." at the North Pole";
	}
	//$messageText = "Temp: ".$currentTemp." ".$SEPARATOR." Wind: ".$currentWindDirection." ".$currentWind." ".$SEPARATOR." Humidity: ".$humidity."%";
	//echo "messageText: ".$messageText."\n";

	//	$messageText = $currentTemp;
		
	logEntry("Weather string: ".$messageText);
	
	logEntry("Adding message ".$messageText. " to message queue: " . $pluginName);//---------------
	
	if($MESSAGE_QUEUE_PLUGIN_ENABLED) {
		addNewMessage($messageText,$pluginName,$pluginData=$CITY." ".$STATE, $MESSAGE_FILE);
	} else {
		logEntry("MessageQueue plugin is not enabled/installed: Cannot add message: ".$messageText);
	} //---------------------------------
	if($IMMEDIATE_OUTPUT != "ON") {
		logEntry("NOT immediately outputting to matrix Immediate output ");
	} else {
		logEntry("IMMEDIATE OUTPUT ENABLED" );
	
		// write high water mark, so that if run-matrix is run it will not re-run old messages
	
		$pluginLatest = time ();
	
		// logEntry("message queue latest: ".$pluginLatest);
		// logEntry("Writing high water mark for plugin: ".$pluginName." LAST_READ = ".$pluginLatest);
	
		// file_put_contents($messageQueuePluginPath.$pluginSubscriptions[$pluginIndex].".lastRead",$pluginLatest);
		// WriteSettingToFile("LAST_READ",urlencode($pluginLatest),$pluginName);
	
		// do{
	
		logEntry ( "Matrix location: " . $MATRIX_LOCATION );
		logEntry ( "Matrix Exec page: " . $MATRIX_EXEC_PAGE_NAME );
		$MATRIX_ACTIVE = true;
		WriteSettingToFile ( "MATRIX_ACTIVE", urlencode ( $MATRIX_ACTIVE ), $pluginName );
		logEntry ( "MATRIX ACTIVE: " . $MATRIX_ACTIVE );
	
		$curlURL = "http://" . $MATRIX_LOCATION . "/plugin.php?plugin=" . $MATRIX_MESSAGE_PLUGIN_NAME . "&page=" . $MATRIX_EXEC_PAGE_NAME . "&nopage=1&subscribedPlugin=" . $pluginName . "&onDemandMessage=" . urlencode ( $messageText );
		if ($DEBUG)
			logEntry ( "MATRIX TRIGGER: " . $curlURL );
		
			$ch = curl_init ();
			curl_setopt ( $ch, CURLOPT_URL, $curlURL );
		
			curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
			curl_setopt ( $ch, CURLOPT_WRITEFUNCTION, 'do_nothing' );
			curl_setopt ( $ch, CURLOPT_VERBOSE, false );
		
			$result = curl_exec ( $ch );
			logEntry ( "Curl result: " . $result ); // $result;
			curl_close ( $ch );
		
			$MATRIX_ACTIVE = false;
			WriteSettingToFile ( "MATRIX_ACTIVE", urlencode ( $MATRIX_ACTIVE ), $pluginName );
		

	}

lockHelper::unlock();

?>
