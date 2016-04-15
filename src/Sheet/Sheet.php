<?php
namespace DoItWell\Sheet;

class Sheet {
	protected $tagBindings;
	protected $reader;
	protected $headers;
	protected $cursor;
	protected $cursorRowNum;

	public function __construct(\DoItWell\ArrayReader $reader) {
		$this->reader = $reader;
		$this->cursorRowNum = 0;
	}

	public function HeaderValue(Coordinate $coordinate){
		if( !isset($this->headers[$coordinate->Row()-1]) ){
			return NULL;
		}

		if( !isset($this->headers[$coordinate->Row()-1][$coordinate->Column()-1]) ){
			return NULL;
		}

		return trim($this->headers[$coordinate->Row()-1][$coordinate->Column()-1]);
	}

	public function CursorValue(Coordinate $coordinate){
		if( $coordinate->Row() !== $this->cursorRowNum ){
			throw new \Exception("coordinate not available (cursor has moved?)");
		}

		return isset($this->cursor[$coordinate->Column() - 1]) ?
			trim($this->cursor[$coordinate->Column() - 1]) :
			NULL;
	}

	public function CoordinateIterator(Ranger $ranger = NULL) {
		$range = $ranger->Range();

		if( $this->HeaderRange()->DoesContainRange($range) ){
			return $range->MatrixCoordinateIterator($this->headers);
		}

		if( $this->CursorRange()->DoesContainRange($range) ){
			return $range->MatrixCoordinateIterator(
				array($this->cursor),
				new Coordinate($this->cursorRowNum, 1)
			);
		}

		throw new \Exception("range not available (cursor has moved?)");
	}

	public function CoordinateIteratorReverse(Ranger $ranger = NULL) {
		$range = $ranger->Range();

		if( $this->HeaderRange()->DoesContainRange($range) ){
			return $range->MatrixCoordinateIteratorReverse($this->headers);
		}

		if( $this->CursorRange()->DoesContainRange($range) ){
			return $range->MatrixCoordinateIteratorReverse(
				array($this->cursor),
				new Coordinate($this->cursorRowNum, 1)
			);
		}

		throw new \Exception("range not available (cursor has moved?)");
	}

	public function RangeValue(Ranger $ranger = NULL) {
		$range = $ranger->Range();

		if( $this->HeaderRange()->DoesContainRange($range) ){
			$first = NULL;
			foreach(
				$range->MatrixCoordinateIterator($this->headers)
				as $coordinate
			){
				if( $first === NULL ){
					$first = $this->HeaderValue($coordinate);
					continue;
				}

				throw new \InvalidArgumentException("Range spans multiple values");
			}

			return $first;
		}

		if( $this->CursorRange()->DoesContainRange($range) ){
			$first = NULL;
			foreach(
				$range->MatrixCoordinateIterator(
					array($this->cursor),
					new Coordinate($this->cursorRowNum, 1)
				)
				as $coordinate
			){
				if( $first === NULL ){
					$first = $this->CursorValue($coordinate);
					continue;
				}

				throw new \InvalidArgumentException("Range spans multiple values");
			}

			return $first;
		}

		throw new \Exception("range not available (cursor has moved?)");
	}

	public function FindInRange(Range $range, $testFunction) {
		if( $this->HeaderRange()->DoesContainRange($range) ){
			foreach( $this->CoordinateIterator($range) as $coordinate ){
				if( call_user_func($testFunction, $coordinate) ){
					return $coordinate;
				}
			}

			return NULL;
		}

		if( $this->CursorRange()->DoesContainRange($range) ){
			foreach( $this->CoordinateIterator($range) as $coordinate ){
				if( call_user_func($testFunction, $coordinate) ){
					return $coordinate;
				}
			}

			return NULL;
		}

		throw new \Exception("search range not available (cursor has moved?)");
	}

	public function FindInRangeReverse(Range $range, $testFunction) {
		if( $this->HeaderRange()->DoesContainRange($range) ){
			foreach( $this->CoordinateIteratorReverse($range) as $coordinate ){
				if( call_user_func($testFunction, $coordinate) ){
					return $coordinate;
				}
			}

			return NULL;
		}

		if( $this->CursorRange()->DoesContainRange($range) ){
			foreach( $this->CoordinateIteratorReverse($range) as $coordinate ){
				if( call_user_func($testFunction, $coordinate) ){
					return $coordinate;
				}
			}

			return NULL;
		}

		throw new \Exception("search range not available (cursor has moved?)");
	}

	public function BindTag($name, $function){
		$this->tagBindings[$name] = $function;
	}

	public function NamedRangeCallback(Range $range){
		$sheet = $this;
		return function(Coordinate $coordinate) use ($sheet, $range) {
			return $sheet->RangeValue($range->IntersectRange(new Range(
				$coordinate->Row(), $coordinate->Row(),
				1, NULL
			)));
		};
	}

	public function Tag($name, Coordinate $coordinate){
		if( !isset($this->tagBindings[$name]) ){
			throw new \InvalidArgumentException("tag '$name' is not bound");
		}

		return call_user_func($this->tagBindings[$name], $coordinate);
	}

	public function ReadHeaders($numRows = 1){
		for( $i = 0; $i < $numRows; $i++ ){
			$this->headers[] = $this->reader->ReadArray();
			$this->cursorRowNum++;
		}
	}

	public function Read(){
		$this->cursor = $this->reader->ReadArray();
		$this->cursorRowNum++;
		return $this->cursor;
	}

	public function HeaderRange(){
		return new Range(1, count($this->headers), NULL, NULL);
	}

	public function CursorRange(){
		return new Range($this->cursorRowNum, $this->cursorRowNum, 1, NULL);
	}
}
