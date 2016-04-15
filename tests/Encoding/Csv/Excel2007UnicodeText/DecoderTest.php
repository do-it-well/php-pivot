<?php
namespace DoItWell\Encoding\Csv\Excel2007UnicodeText;

class DecoderTest extends \PHPUnit_Framework_TestCase {
	private function handle($data){
		$handle = @fopen('php://memory', 'w+');
		if( $handle === FALSE ) {
			throw new \Exception("Failed to open memory handle");
		}

		if( fwrite($handle, $data) === FALSE ){
			throw new \Exception("Failed to write sample data to memory handle");
		}

		if( fseek($handle, 0) !== 0 ){
			throw new \Exception("Failed to seek to beginning of sample data");
		}

		return $handle;
	}

	public function testSimple() {
		$decoder = new Decoder($this->handle(
				"\xFF\xEE" .
				"a\x00" . "\t\x00" .
				"\"\x00b\x00" . "\t\x00" . "n\x00o\x00t\x00c\x00\"\x00". "\t\x00" .
				"c\x00". "\x0D\x00\x0A\x00" .

				"d\x00". "\t\x00" .
				"e\x00". "\t\x00" .
				"f\x00"
		));
		$this->assertEquals(array('a', "b\tnotc", 'c'), $decoder->ReadArray());
		$this->assertEquals(array('d', 'e', 'f'), $decoder->ReadArray());
		$this->assertEquals(FALSE, $decoder->ReadArray());
	}
}
