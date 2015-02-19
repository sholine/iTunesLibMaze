<?php

require_once("./include/OneTrack.php");
require_once("./include/Library.php");

class PlaycountLibrary extends Library
{
	public function processInfosWithLibrary(&$COUNTERS_AND_STARS = array()) {
		$tracks_list_counters = $this->getTracksFromLibrary();
		foreach ($tracks_list_counters as $key => $value) {
			$my_key = $this->buildKey($value);
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
	}
}

?>