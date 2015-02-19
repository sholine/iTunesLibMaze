<?php

require_once("./include/OneTrack.php");
require_once("./include/Library.php");

class StarsLibrary extends Library
{
	public function processInfosWithLibrary(&$COUNTERS_AND_STARS = array()) {
		$tracks_list_stars = $this->getTracksFromLibrary();
		foreach ($tracks_list_stars as $key => $value) {
			$my_key = $this->buildKey($value);
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
	}
}

?>