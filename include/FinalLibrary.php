<?php

require_once("./include/OneTrack.php");
require_once("./include/Library.php");

class FinalLibrary extends Library
{
	public function processInfosWithLibrary(&$COUNTERS_AND_STARS = array()) {
		$Library_tracks = $this->getTracksFromLibrary();
		$cpt = 0;
		foreach ($Library_tracks as $key => $value) {
			$track_key = $this->buildKey($value);

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
			}
		}
		$this->setTracksToLibrary($Library_tracks);
		return $cpt;
	}
}

?>