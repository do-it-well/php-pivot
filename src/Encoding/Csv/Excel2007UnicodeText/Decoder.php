<?php
namespace DoItWell\Encoding\Csv\Excel2007UnicodeText;

class Decoder extends \DoItWell\Encoding\Csv\Decoder {
	/**
	* Create a new ArrayReader based on an Excel 2007 "Unicode Text" input stream
	*
	* Note: due to this implementation, the $handle is modified to convert to UTF8
	*       as a result, subsequent uses of the same handle are not possible.
	*
	* $handle resource The input stream, as output by Excel 2007's "Unicode Text"
	*                  ie: UTF16-LE, Tab-delimited, strings enclosed/escaped by "
	*/
	public function __construct($handle) {
		stream_filter_append($handle,
		                     'convert.iconv.UTF-16LE/UTF-8',
		                     STREAM_FILTER_READ);
		parent::__construct($handle, "\t", '"', '"');
	}

	public function ReadArray() {
		$array = parent::ReadArray();
		if( isset($array[0]) && substr($array[0], 0, 3) === "\xEE\xBB\xBF" ){
			// iconv seems to erroneously convert the UTF16LE BOM to an invalid
			// "little-endian" UTF8 BOM. In either case, the BOM should not be
			// returned in the output array.
			$array[0] = substr($array[0], 3);
		}

		return $array;
	}
}
