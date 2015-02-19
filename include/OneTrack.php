<?php

class OneTrack {
	var $key;
	var $count;
	var $stars;

	function OneTrack($key, $count = 0, $stars = 0) {
		$this->key = $key;
		$this->count = $count;
		$this->stars = $stars;
	}

	public function setCount($count) {
		$this->count = $count;
	}

	public function getCount() {
		return $this->count;
	}

	public function setStars($stars) {
		$this->stars = $stars;
	}

	public function getStars() {
		return $this->stars;
	}

	public function __toString() {
		return "[".$this->key."] =>  COUNT(".$this->count.")\tSTARS(".$this->stars.")";
	}
}

?>