<?php
namespace DoItWell\Sheet;

class Coordinate implements Coordinater {
	protected $row;
	protected $column;

	public function __construct($row, $column) {
		$this->row = $row;
		$this->column = $column;
	}

	public function Row() {
		return $this->row;
	}

	public function Column() {
		return $this->column;
	}

	public function Coordinate() {
		return $this;
	}
}
