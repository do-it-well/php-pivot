<?php
namespace DoItWell\Sheet;

class RangeTest extends \PHPUnit_Framework_TestCase {
	public function testDoesContainCoordinate() {
		$range = new Range(5, 10, 5, 10);
		foreach(
			array(
				'BeforeRow' => array(1,7, FALSE),
				'BeforeColumn' => array(7,1, FALSE),
				'AfterRow' => array(12,7, FALSE),
				'AfterColumn' => array(7,12, FALSE),
				'Within' => array(7,7, TRUE)
			) as $label => $spec
		){
			$this->assertEquals(
				$spec[2],
				$range->DoesContainCoordinate(new Coordinate($spec[0], $spec[1])),
				"Failure in '$label' test"
			);
		}
	}

	public function testAxis() {
		$range = new Range(1, 2, 3, 4);
		$this->assertEquals(1, $range->RowAxis()->Start());
		$this->assertEquals(2, $range->RowAxis()->End());
		$this->assertEquals(3, $range->ColumnAxis()->Start());
		$this->assertEquals(4, $range->ColumnAxis()->End());

		$range = new Range(1, NULL, 2, NULL);
		$this->assertEquals(1, $range->RowAxis()->Start());
		$this->assertEquals(NULL, $range->RowAxis()->End());
		$this->assertEquals(2, $range->ColumnAxis()->Start());
		$this->assertEquals(NULL, $range->ColumnAxis()->End());
	}

	public function testDoesIntersectRange() {
		$range = new Range(5, 10, 5, 10);

		foreach(
			array(
				'BeforeRow' => array(new Range(1,4,7,8), FALSE),
				'BeforeColumn' => array(new Range(7,8,1,4), FALSE),
				'AfterRow' => array(new Range(12,15,7,8), FALSE),
				'AfterColumn' => array(new Range(7,8,12,15), FALSE),
				'Contained' => array(new Range(7,8,7,8), TRUE),
				'Containing' => array(new Range(4,11,4,11), TRUE),
				'FromBeforeRowToWithin' => array(new Range(1,6,7,8), TRUE),
				'FromBeforeColumnToWithin' => array(new Range(7,8,1,6), TRUE),
				'FromWithinToAfterRow' => array(new Range(7,12,7,8), TRUE),
				'FromWithinToAfterColumn' => array(new Range(7,8,7,12), TRUE)
			) as $label => $spec
		){
			$this->assertEquals(
				$spec[1],
				$range->DoesIntersectRange($spec[0]),
				"Failure in '$label' test"
			);
		}
	}

	public function testDoesContainRange() {
		$range = new Range(5, 10, 5, 10);

		foreach(
			array(
				'BeforeRow' => array(new Range(1,4,7,8), FALSE),
				'BeforeColumn' => array(new Range(7,8,1,4), FALSE),
				'AfterRow' => array(new Range(12,15,7,8), FALSE),
				'AfterColumn' => array(new Range(7,8,12,15), FALSE),
				'Contained' => array(new Range(7,8,7,8), TRUE),
				'Containing' => array(new Range(4,11,4,11), FALSE),
				'FromBeforeRowToWithin' => array(new Range(1,6,7,8), FALSE),
				'FromBeforeColumnToWithin' => array(new Range(7,8,1,6), FALSE),
				'FromWithinToAfterRow' => array(new Range(7,12,7,8), FALSE),
				'FromWithinToAfterColumn' => array(new Range(7,8,7,12), FALSE)
			) as $label => $spec
		){
			$this->assertEquals(
				$spec[1],
				$range->DoesContainRange($spec[0]),
				"Failure in '$label' test"
			);
		}
	}

	public function testIntersectRange() {
		$range = new Range(5, 10, 5, 10);

		foreach(
			array(
				'BeforeRow' => array(new Range(1,4,7,8), NULL),
				'BeforeColumn' => array(new Range(7,8,1,4), NULL),
				'AfterRow' => array(new Range(12,15,7,8), NULL),
				'AfterColumn' => array(new Range(7,8,12,15), NULL),
				'Contained' => array(new Range(7,8,7,8), new Range(7,8,7,8)),
				'Containing' => array(new Range(4,11,4,11), $range),
				'FromBeforeRowToWithin' => array(new Range(1,6,7,8), new Range(5,6,7,8)),
				'FromBeforeColumnToWithin' => array(new Range(7,8,1,6), new Range(7,8,5,6)),
				'FromWithinToAfterRow' => array(new Range(7,12,7,8), new Range(7,10,7,8)),
				'FromWithinToAfterColumn' => array(new Range(7,8,7,12), new Range(7,8,7,10))
			) as $label => $spec
		){
			$this->assertEquals(
				$spec[1],
				$range->IntersectRange($spec[0]),
				"Failure in '$label' test"
			);
		}
	}

	public function testRowRange() {
		$range = new Range(5, 10, 5, 10);
		$this->assertEquals(
			new Range(5, 10, NULL, NULL),
			$range->RowRange()
		);
	}

	public function testColumnRange() {
		$range = new Range(5, 10, 5, 10);
		$this->assertEquals(
			new Range(NULL, NULL, 5, 10),
			$range->ColumnRange()
		);
	}

	public function testRange() {
		$range = new Range(5, 10, 5, 10);
		$this->assertEquals(
			$range,
			$range->Range()
		);
	}

	public function testMatrixCoordinateIterator() {
		$range = new Range(2, 3, 4, 5);
		$matrix = array(
			array('a1','a2','a3','a4','a5','a6','a7','a8','a9','a10'),
			array('b1','b2','b3','b4'), // intentionally truncated
			array('c1','c2','c3','c4','c5','c6','c7','c8','c9','c10'),
			array('d1','d2','d3','d4','d5','d6','d7','d8','d9','d10'),
			array('e1','e2','e3','e4','e5','e6','e7','e8','e9','e10')
		);

		$expected = array(
			new Coordinate(2,4),
			new Coordinate(3,4),
			new Coordinate(3,5)
		);

		foreach(
			array(
				'forward' => array('MatrixCoordinateIterator', $expected),
				'reverse' => array(
					'MatrixCoordinateIteratorReverse',
					array_reverse($expected)
				)
			)
			as $label => $spec
		){

			$iterations = 0;
			foreach(
				call_user_func(array($range, $spec[0]), $matrix)
				as $i => $coordinate
			){
				$iterations++;
				$this->assertArrayHasKey(
					$i, $spec[1],
					"Completely unexpected yield ($i) from $label Iterator"
				);
				$this->assertEquals(
					$spec[1][$i], $coordinate,
					"$label Yield #$i did not match expectation"
				);
			}

			$this->assertEquals(
				count($spec[1]), $iterations,
				"Different number of $label iterations than was expected"
			);
		}
	}

	public function testMatrixCoordinateIterator_InitialOffset_WithinRange() {
		$range = new Range(5, 9, 8, 14);
		$matrix = array(
			array('g10','g11','g12','g13','g14','g15','g16','g17','g18','g19'),
			array('h10','h11','h12','h13'),
			array('i10','i11','i12','i13', 'i14','i15','i16','i17','i18','i19'),
			array('j10','j11','j12','j13','j14','j15','j16','j17','j18','j19')
		);

		$expected = array(
			new Coordinate(7,10),
			new Coordinate(7,11),
			new Coordinate(7,12),
			new Coordinate(7,13),
			new Coordinate(7,14),

			new Coordinate(8,10),
			new Coordinate(8,11),
			new Coordinate(8,12),
			new Coordinate(8,13),

			new Coordinate(9,10),
			new Coordinate(9,11),
			new Coordinate(9,12),
			new Coordinate(9,13),
			new Coordinate(9,14)
		);

		foreach(
			array(
				'forward' => array('MatrixCoordinateIterator', $expected),
				'reverse' => array(
					'MatrixCoordinateIteratorReverse',
					array_reverse($expected)
				)
			)
			as $label => $spec
		){

			$iterations = 0;
			
			foreach(
				call_user_func(array($range, $spec[0]), $matrix, new Coordinate(7,10))
				as $i => $coordinate
			){
				$iterations++;
				$this->assertArrayHasKey(
					$i, $spec[1],
					"Completely unexpected yield ($i) from $label Iterator"
				);
				$this->assertEquals(
					$spec[1][$i], $coordinate,
					"$label Yield #$i did not match expectation"
				);
			}
			$this->assertEquals(
				count($spec[1]), $iterations,
				"Different number of $label iterations than was expected"
			);
		}
	}

	public function testMatrixCoordinateIterator_InitialOffset_BeforeRange() {
		$range = new Range(8, 9, 12, 14);
		$matrix = array(
			array('g10','g11','g12','g13','g14','g15','g16','g17','g18','g19'),
			array('h10','h11','h12','h13'),
			array('i10','i11','i12','i13', 'i14','i15','i16','i17','i18','i19'),
			array('j10','j11','j12','j13','j14','j15','j16','j17','j18','j19')
		);

		$expected = array(
			new Coordinate(8,12),
			new Coordinate(8,13),
			
			new Coordinate(9,12),
			new Coordinate(9,13),
			new Coordinate(9,14)
		);

		foreach(
			array(
				'forward' => array('MatrixCoordinateIterator', $expected),
				'reverse' => array(
					'MatrixCoordinateIteratorReverse',
					array_reverse($expected)
				)
			)
			as $label => $spec
		){
			$iterations = 0;
			foreach(
				call_user_func(array($range, $spec[0]), $matrix, new Coordinate(7,10))
				as $i => $coordinate
			){
				$iterations++;
				$this->assertArrayHasKey(
					$i, $spec[1],
					"Completely unexpected yield ($i) from $label Iterator"
				);
				$this->assertEquals(
					$spec[1][$i], $coordinate,
					"$label Yield #$i did not match expectation"
				);
			}
			$this->assertEquals(
				count($spec[1]), $iterations,
				"Different number of $label iterations than was expected"
			);
		}
	}
}
