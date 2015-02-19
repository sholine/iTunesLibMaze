<?php

require_once("./lib/PlistParser.inc");

abstract class Library {
	var $filename;
	var $type;
	var $plist;
	var $tracksCount;
	
	function __construct($filename = null) {
		$this->filename = $filename;

		if (!file_exists($filename)) {
			exit('Failed openning file ['.$filename.']\n');
		}

		$parser = new plistParser();
		$this->plist = $parser->parseFile($filename);
		$this->tracksCount = count($this->plist["Tracks"]);
	}

	protected function getPlistFromLibrary() {
		return $this->plist;
	}

	protected function getTracksFromLibrary() {
		return $this->plist["Tracks"];
	}

	protected function setTracksToLibrary($libraryTracks) {
		$this->plist["Tracks"] = $libraryTracks;
	}

	protected function buildKey($value) {
		$key = "";
		$key .= (isset($value["Artist"]) ? $value["Artist"] : "");
		$key .= "|";
		$key .= (isset($value["Album"]) ? $value["Album"] : "");
		$key .= "|";
		$key .= (isset($value["Name"]) ? $value["Name"] : "");
		return $key;
	}

	public function getTracksCount() {
		return $this->tracksCount;
	}

	public function writeLibraryToFile($filename) {
		file_put_contents($filename, plist_encode_xml($this->plist));
	}

	abstract public function processInfosWithLibrary(&$COUNTERS_AND_STARS);
}

?>