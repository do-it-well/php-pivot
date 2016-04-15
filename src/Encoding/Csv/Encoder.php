<?php
namespace DoItWell\Encoding\Csv;

class Encoder implements \DoItWell\ArrayWriter {
	protected $handle; // the already-open (using fopen, for example) stream
	private $delimiter; // field separator
	private $enclosure; // text enclosure
	private $escape; // escape within text enclosure

	private $_oldLocale; // the "old" locale, prior to readArray

	/**
	* $handle resource The input stream, utf8-encoded
	*/
	public function __construct($handle, $delimiter = ',', $enclosure = '"',
	                            $escape = '"') {
		$this->handle = $handle;
		$this->delimiter = $delimiter;
		$this->enclosure = $enclosure;
		$this->escape = $escape;
	}

	protected function ReadArray_before(){
		// Ensure the "LANG" is utf8, as per the note at:
		//  http://php.net/manual/en/function.fgetcsv.php
		//
		// As the fgetcsv function produces inconsistent results based on the
		// locale, we ensure the locale is set consistently. It is up to the caller
		// to ensure that input data arrives encoded in utf8 format.
		$this->_oldLocale = \setlocale(LC_CTYPE, 0);
		if( setlocale(LC_CTYPE, 'en_US.UTF-8') === FALSE ){
			throw new \RuntimeException('Unable to bind to en_US.UTF-8 locale');
		}
	}

	protected function ReadArray_after(){
		if( setlocale(LC_CTYPE, $this->_oldLocale) === FALSE ){
			throw new \RuntimeException('Unable to restore previous locale');
		}
	}

	public function ReadArray() {
		$this->ReadArray_before();
		$skip_after = FALSE;
		try {

			$array = fgetcsv($this->handle, 0, $this->delimiter, $this->enclosure,
			                 $this->escape);

			$skip_after = TRUE; $this->ReadArray_after();
		} catch( \Exception $e ){
			if( !$skip_after ) $this->ReadArray_after();
			throw $e;
		}

		return $array;
	}
}
