<?php
namespace DoItWell\Sheet;

class RangeAxis {
	protected $start, $end;

	public function __construct($start, $end) {
		$this->start = ($start === NULL) ? 1 : intval($start);
		$this->end = ($end === NULL) ? NULL : intval($end);
	}

	public function DoesContain($value) {
		if( $value === NULL ){
			throw new \InvalidArgumentException("Cannot compare NULL value");
		}

		if( $this->end === NULL ){
			if( $value >= $this->start ) return TRUE;
			return FALSE;
		}

		return $value >= $this->start && $value <= $this->end;
	}

	public function DoesContainRangeAxis(self $other) {
		return (
			$other->start >= $this->start &&
			(
				$this->end === NULL ||
				($other->end !== NULL && $other->end <= $this->end)
			)
		);
	}

	public function DoesIntersectRangeAxis(self $other) {
		return (
			($this->end === NULL && $other->end === NULL) ||
			$this->DoesContain($other->start) ||
			($other->end !== NULL && $this->DoesContain($other->end)) ||
			$other->DoesContain($this->start) ||
			($this->end !== NULL && $other->DoesContain($this->end))
		);
	}

	public function IntersectRangeAxis(self $other) {
		if( !$this->DoesIntersectRangeAxis($other) ) return NULL;

		$start = max($this->start, $other->start);

		if( $this->end === NULL ){
			if( $other->end === NULL ){
				$end = NULL;
			} else {
				$end = $other->end;
			}
		} else {
			if( $other->end === NULL ){
				$end = $this->end;
			} else {
				$end = min($this->end, $other->end);
			}
		}

		return new self($start, $end);
	}

	public function Start() {
		return $this->start;
	}

	public function End() {
		return $this->end;
	}
}
