<?php
namespace DoItWell\Encoding\Csv;

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
		$decoder = new Decoder($this->handle("a,\"b,notc\",c\r\nd,e,f"));
		$this->assertEquals(array('a', 'b,notc', 'c'), $decoder->ReadArray());
		$this->assertEquals(array('d', 'e', 'f'), $decoder->ReadArray());
		$this->assertEquals(FALSE, $decoder->ReadArray());
	}

	public function testUTF8DecodesCorrectly() {
		$decoder = new Decoder($this->handle("a,b\xE2\x96\x88b,c"));
		$this->assertEquals(array('a', "b\xE2\x96\x88b", 'c'), $decoder->ReadArray());
		$this->assertEquals(FALSE, $decoder->ReadArray());
	}

	/*
	* The encoding of the input is assumed to be utf8, regardless of the locale
	*/
	public function testLocaleOfFileIsAssumedToBeUTF8() {
		$this->markTestSkipped('We have yet to isolate platform-dependent variables which effect this test');
		return;

		$oldLocale = setlocale(LC_CTYPE, 0);

		// you may need to install the pl_PL locale separately, (Windows-1250 code
		// page) for this test to work. On Centos, for example, this can be acquired
		// via: yum install glibc-common
		if( setlocale(LC_CTYPE, 'pl_PL') === FALSE ){
			$this->markTestSkipped(
				'The pl_PL locale is not available (Windows-1250 code page), and is '.
				'required for this test to verify that UTF8 is consistently assumed.'
			);
		}

		$decoder = new Decoder($this->handle("a,\xA3\xF3badval,b,c"));

		// PHP discards unknown characters, rather than using "replacement
		// characters". This test exposes that implementation detail.
		$this->assertEquals(array('a', 'badval', 'b', 'c'), $decoder->ReadArray());

		if( setlocale(LC_CTYPE, $oldLocale) === FALSE ){
			throw new \Exception("Failed to restore locale after test");
		}
	}
}
