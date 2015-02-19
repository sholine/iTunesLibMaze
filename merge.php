<?php

/**
	USED LIBRARIES
**/

include("./lib/PlistParser.inc");
include("./lib/PropertyList.inc");

include("./include/OneTrack.php");

/**
	GLOBAL VARIABLES
**/

$DATA_DIR = dirname(__FILE__)."/data/";

$COUNTERS_FILE = $DATA_DIR."counters.xml";
$STARS_FILE = $DATA_DIR."stars.xml";
$LIBRARY_FILE = $DATA_DIR."everything.xml";
$OUTPUT_FILE = $DATA_DIR."final.xml";

/**
	FUNCTIONS
**/

function getPlistFromLibrary($file) {
	if (!file_exists($file)) {
		exit('Echec lors de l\'ouverture du fichier '.$file.'.\n');
	}

	$parser = new plistParser();
	return $parser->parseFile($file);
}

function getTracksFromLibrary($file) {
	if (!file_exists($file)) {
		exit('Echec lors de l\'ouverture du fichier '.$file.'.\n');
	}

	$parser = new plistParser();
	$plist = $parser->parseFile($file);
	return $plist["Tracks"];
}

function buildKey($value) {
	$key = "";
	$key .= (isset($value["Artist"]) ? $value["Artist"] : "");
	$key .= "|";
	$key .= (isset($value["Album"]) ? $value["Album"] : "");
	$key .= "|";
	$key .= (isset($value["Name"]) ? $value["Name"] : "");
	//echo "[".$key."]\n";
	return $key;
}

/**
	LOADING COUNTERS AND STARS
**/

$COUNTERS_AND_STARS = array();

// Dealing with counters
echo "============ LOADING COUNTERS... ============\n";
$tracks_list_counters = getTracksFromLibrary($COUNTERS_FILE);
foreach ($tracks_list_counters as $key => $value) {
	$my_key = buildKey($value);
	if (isset($value["Play Count"])) {
		$my_value = $value["Play Count"];
		if (isset($COUNTERS_AND_STARS[$my_key])) {
			$thisTrack = $COUNTERS_AND_STARS[$my_key];
			$thisTrack->setCount($thisTrack->getCount() + $my_value);
			//echo "Etrange ! La valeur existe pour [".$my_key."] : ";
			//echo "AVANT(".$COUNTERS_AND_STARS[$my_key].") + (".$value["Play Count"].") = APRES(".$my_value.")\n";
			//var_dump($COUNTERS_AND_STARS[$my_key]); exit;
		} else {
			$COUNTERS_AND_STARS[$my_key] = new OneTrack($my_key, $my_value, 0);
		}
	}
}
echo "\tNb loaded => ".count($tracks_list_counters)."\n";

// Dealing with stars
echo "============== LOADING STARS... =============\n";
$tracks_list_stars = getTracksFromLibrary($STARS_FILE);
foreach ($tracks_list_stars as $key => $value) {
	$my_key = buildKey($value);
	if (isset($value["Rating"])) {
		$my_value = $value["Rating"];
		if (isset($COUNTERS_AND_STARS[$my_key])) {
			$thisTrack = $COUNTERS_AND_STARS[$my_key];
			$thisTrack->setStars(max($thisTrack->getCount(), $my_value));
			//echo "Etrange ! La valeur existe pour [".$my_key."] : ";
			//echo "AVANT(".$COUNTERS_AND_STARS[$my_key].") + (".$value["Play Count"].") = APRES(".$my_value.")\n";
			//var_dump($COUNTERS_AND_STARS[$my_key]); exit;
		} else {
			$COUNTERS_AND_STARS[$my_key] = new OneTrack($my_key, 0, $my_value);
		}
	}
}
echo "\tNb loaded => ".count($tracks_list_stars)."\n";

echo "============== MERGING INFOS... =============\n";
echo "\tNb total => ".count($COUNTERS_AND_STARS)."\n";

/**
	LOADING LIBRARY
**/

echo "============= LOADING LIBRARY...=============\n";
$LIBRARY = getPlistFromLibrary($LIBRARY_FILE);
$Library_tracks = $LIBRARY["Tracks"];
echo "\tNb total tracks => ".count($Library_tracks)."\n";
echo "== ADDING COUNTERS & STARS INFORMATIONS... ==\n";
$cpt = 0;
foreach ($Library_tracks as $key => $value) {
	$track_key = buildKey($value);

	if (isset($COUNTERS_AND_STARS[$track_key])) {
		$cpt++;
		$obj = $COUNTERS_AND_STARS[$track_key];
		$log = "Key[".$key."] [".$track_key."] =>";
		if ($obj->getCount() > 0) {
			$Library_tracks[$key]["Play Count"] = $obj->getCount();
			$log .= " COUNT(".$obj->getCount().")";
		}
		if ($obj->getStars() > 0) {
			$Library_tracks[$key]["Rating"] = $obj->getStars();
			$log .= " STARS(".$obj->getStars().")";
		}
		//echo $log."\n";
		/*if (stripos($track_key, "100 Kai") !== false) {
			echo $log."\n";
			print_r($Library_tracks[$key]);
		}*/
	}
}
$LIBRARY["Tracks"] = $Library_tracks;
echo "\tNb modified tracks => ".$cpt."\n";

echo "=========== WRITING OUTPUT FILE...===========\n";
file_put_contents($OUTPUT_FILE, plist_encode_xml($LIBRARY));
echo "\tDone... Enjoy!\n";
?>