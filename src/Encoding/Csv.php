<?php
namespace DoItWell\Encoding;

class Csv {
	/**
	* Create a new Csv Decoder, based on an input stream handle
	*/
	public static function NewDecoder($handle, $delimiter = ',',
	                           $enclosure = '"', $escape = '"') {
		return new Csv\Decoder($handle, $delimiter, $enclosure, $escape);
	}
}
