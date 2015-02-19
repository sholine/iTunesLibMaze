<?php

/**
	EXTERNAL LIBRARIES
**/

include("./lib/PlistParser.inc");
include("./lib/PropertyList.inc");

/**
	INTERNAL OBJECTS
**/

require_once("./include/StarsLibrary.php");
require_once("./include/PlaycountLibrary.php");
require_once("./include/FinalLibrary.php");

/**
	GLOBAL VARIABLES
**/

$DATA_DIR = dirname(__FILE__)."/data/";
$COUNTERS_FILE = $DATA_DIR."counters.xml";
$STARS_FILE = $DATA_DIR."stars.xml";
$PREVIOUS_FILE = $DATA_DIR."everything.xml";
$OUTPUT_FILE = $DATA_DIR."final.xml";

/**
	FUNCTIONS
**/

/**
	LOADING COUNTERS AND STARS
**/

$COUNTERS_AND_STARS = array();

// Dealing with counters
echo "============ LOADING COUNTERS... ============\n";
$COUNTERS_LIBRARY = new PlaycountLibrary($COUNTERS_FILE);
$COUNTERS_LIBRARY->processInfosWithLibrary($COUNTERS_AND_STARS);
echo "\tNb loaded => ".$COUNTERS_LIBRARY->getTracksCount()."\n";

// Dealing with stars
echo "============== LOADING STARS... =============\n";
$STARS_LIBRARY = new StarsLibrary($STARS_FILE);
$STARS_LIBRARY->processInfosWithLibrary($COUNTERS_AND_STARS);
echo "\tNb loaded => ".$STARS_LIBRARY->getTracksCount()."\n";

echo "============== MERGING INFOS... =============\n";
echo "\tNb total => ".count($COUNTERS_AND_STARS)."\n";

/**
	CHANGING LIBRARY
**/

echo "============= LOADING LIBRARY...=============\n";
$PREVIOUS_LIBRARY = new FinalLibrary($PREVIOUS_FILE);
echo "\tNb total tracks => ".$PREVIOUS_LIBRARY->getTracksCount()."\n";

echo "== ADDING COUNTERS & STARS INFORMATIONS... ==\n";
$cpt = $PREVIOUS_LIBRARY->processInfosWithLibrary($COUNTERS_AND_STARS);
echo "\tNb modified tracks => ".$cpt."\n";

echo "=========== WRITING OUTPUT FILE...===========\n";
$PREVIOUS_LIBRARY->writeLibraryToFile($OUTPUT_FILE);
echo "\tDone... Enjoy!\n";
?>