<?php
namespace DoItWell;

interface ArrayReader {
	/**
	* return: a flat, indexed array, or FALSE if no data is available
	* May throw implementation-specific Exceptions
	*/
	public function ReadArray();
}
