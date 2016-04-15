<?php
namespace DoItWell\Sheet;

class Range implements Ranger {
	protected $columnAxis;
	protected $rowAxis;

	public function __construct($minRow, $maxRow, $minColumn, $maxColumn) {
		$this->columnAxis = new RangeAxis($minColumn, $maxColumn);
		$this->rowAxis = new RangeAxis($minRow, $maxRow);
	}

	public function DoesContainCoordinate(Coordinater $other) {
		$coordinate = $other->Coordinate();

		return (
			$this->rowAxis->DoesContain($coordinate->Row()) &&
			$this->columnAxis->DoesContain($coordinate->Column())
		);
	}

	public function RowAxis() {
		return $this->rowAxis;
	}

	public function ColumnAxis() {
		return $this->columnAxis;
	}

	public function DoesIntersectRange(Ranger $other) {
		$range = $other->Range();

		return (
			$this->rowAxis->DoesIntersectRangeAxis($range->rowAxis) &&
			$this->columnAxis->DoesIntersectRangeAxis($range->columnAxis)
		);
	}

	public function DoesContainRange(Ranger $other) {
		$range = $other->Range();

		return (
			$this->rowAxis->DoesContainRangeAxis($range->rowAxis) &&
			$this->columnAxis->DoesContainRangeAxis($range->columnAxis)
		);
	}

	public function IntersectRange(Ranger $other) {
		$range = $other->Range();

		if( !$this->DoesIntersectRange($range) ) return NULL;

		$RowAxis = $this->rowAxis->IntersectRangeAxis($range->rowAxis);
		$ColumnAxis = $this->columnAxis->IntersectRangeAxis($range->columnAxis);

		return new self(
			$RowAxis->Start(), $RowAxis->End(),
			$ColumnAxis->Start(), $ColumnAxis->End()
		);
	}

	public function ColumnRange() {
		return new self(
			NULL, NULL,
			$this->columnAxis->Start(), $this->columnAxis->End()
		);
	}

	public function RowRange() {
		return new self(
			$this->rowAxis->Start(), $this->rowAxis->End(),
			NULL, NULL
		);
	}

	public function Range() {
		return $this;
	}

	/**
	* $matrix  array  an array of arrays containing sheet data
	* $initial  Coordinate  the coordinates of the "top left" of the matrix
	*                       (whether or not this position "exists")
	*/
	public function MatrixCoordinateIterator($matrix,
	                                         Coordinate $initial = NULL) {
		if( $initial === NULL ) $initial = new Coordinate(1,1);
		$row = $initial->Row();
		$column = $initial->Column();
		$rowAxis = $this->RowAxis();
		$columnAxis = $this->ColumnAxis();

		$numRows = count($matrix);
		$numColumns = NULL;

		return new \DoItWell\Generator(
			function($initialize)
			use ($rowAxis, $columnAxis, $matrix, $numRows, $numColumns, $initial,
			     &$row, &$column) {
				if( $initialize ){
					$row = max($initial->Row(), $rowAxis->Start());
					$column = max($initial->Column(), $columnAxis->Start());
				}

				if (
					!(
						$rowAxis->DoesContain($row) &&
						$row <= ($numRows + $initial->Row() - 1)
					)
				){
					return FALSE;
				}

				if( $numColumns === NULL ){
					$numColumns = count($matrix[$row - $initial->Row()]);
				}

				while (
					!(
						$columnAxis->DoesContain($column) &&
						$column <= ($numColumns + $initial->Column() - 1)
					)
				){
					$row++;
					if (
						!(
							$rowAxis->DoesContain($row) &&
							$row <= ($numRows + $initial->Row() - 1)
						)
					){
						return FALSE;
					}

					$column = max($initial->Column(), $columnAxis->Start());
					if( $numColumns === NULL ){
						$numColumns = count($matrix[$row - $initial->Row()]);
					}
				}

				$coordinate = new Coordinate($row, $column);
				$column++;
				return $coordinate;
		});
	}

	/**
	* $matrix  array  an array of arrays containing sheet data
	* $initial  Coordinate  the coordinates of the "top left" of the matrix
	*                       (whether or not this position "exists")
	*/
	public function MatrixCoordinateIteratorReverse($matrix,
	                                         Coordinate $initial = NULL) {
		if( $initial === NULL ) $initial = new Coordinate(1,1);

		$rowAxis = $this->RowAxis();
		$columnAxis = $this->ColumnAxis();
		$row = min(
			$initial->Row() + count($matrix) - 1,
			$rowAxis->End()
		);
		$column = min(
			$initial->Column() + count($matrix[$row - $initial->Row()]) - 1,
			$columnAxis->End()
		);

		$numRows = count($matrix);
		$numColumns = NULL;

		return new \DoItWell\Generator(
			function($initialize)
			use ($rowAxis, $columnAxis, $matrix, $numRows, $numColumns, $initial,
			     &$row, &$column) {
				if( $initialize ){
					$row = min(
						$initial->Row() + count($matrix) - 1,
						$rowAxis->End()
					);
					$column = min(
						$initial->Column() + count($matrix[$row - $initial->Row()]) - 1,
						$columnAxis->End()
					);
				}

				if (
					!(
						$rowAxis->DoesContain($row) &&
						$row >= $initial->Row()
					)
				){
					return FALSE;
				}

				if( $numColumns === NULL ){
					$numColumns = count($matrix[$row - $initial->Row()]);
				}

				while (
					!(
						$columnAxis->DoesContain($column) &&
						$column >= $initial->Column()
					)
				){
					$row--;
					if (
						!(
							$rowAxis->DoesContain($row) &&
							$row >= $initial->Row()
						)
					){
						return FALSE;
					}

					$column = min(
						$initial->Column() + count($matrix[$row - $initial->Row()]) - 1,
						$columnAxis->End()
					);
					if( $numColumns === NULL ){
						$numColumns = count($matrix[$row - $initial->Row()]);
					}
				}

				$coordinate = new Coordinate($row, $column);
				$column--;
				return $coordinate;
		});
	}
}
