<?php
namespace DoItWell\Encoding\Csv;

class Excel2007UnicodeText extends \DoItWell\ArrayReader\Csv {
	/**
	* Create a new ArrayReader based on an Excel 2007 "Unicode Text" input stream
	*
	* Note: due to this implementation, the $handle is modified to convert to UTF8
	*       as a result, subsequent uses of the same handle are not possible.
	*
	* $handle resource The input stream, as output by Excel 2007's "Unicode Text"
	*                  ie: UTF16-LE, Tab-delimited, strings enclosed/escaped by "
	*/
	protected function __construct(resource $handle) {
		stream_filter_append($this->handle,
		                     'convert.mbstring.encoding.UTF-16LE:UTF-8',
		                     STREAM_FILTER_READ);
		parent::__construct($handle, "\t", '"', '"');
	}
}
