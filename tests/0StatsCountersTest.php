<?php

require_once( __DIR__ . '/IncludesForTests.php' );

use PHPUnit\Framework\TestCase;

final class StatsCountersTest extends TestCase {
	/**
	 * @covers ::vipgoci_counter_report
	 */
	function testCounterReport1() {
		$this->assertEquals(
			vipgoci_counter_report(
				'illegalaction',
				'mycounter1',
				100
			),
			false
		);

		$this->assertEquals(
			array(),
			vipgoci_counter_report(
				VIPGOCI_COUNTERS_DUMP
			)
		);
	}

	/**
	 * @covers ::vipgoci_counter_report
	 */
	function testCounterReport2() {
		$this->assertEquals(
			true,
			vipgoci_counter_report(
				VIPGOCI_COUNTERS_DO,
				'mycounter2',
				100
			)
		);

		$this->assertEquals(
			true,
			vipgoci_counter_report(
				VIPGOCI_COUNTERS_DO,
				'mycounter2',
				1
			)
		);

		$this->assertEquals(
			array(
				'mycounter2' => 101,
			),
			vipgoci_counter_report(
				VIPGOCI_COUNTERS_DUMP
			)
		);
	}


	/*
	 * @covers ::vipgoci_counter_update_with_issues_found
	 */
	function testCounterUpdateWithIssuesFound1() {
		$results = array(
			'stats' => array(
				'unique_issue' => array(
					120 => array(
						'errors' => 1,
						'warnings' => 1,
					),

					121 => array(
						'errors' => 2,
						'warnings' => 1,
					),
				)
			)
		);


		vipgoci_counter_update_with_issues_found(
			$results
		);

		$report = vipgoci_counter_report(
			VIPGOCI_COUNTERS_DUMP
		);


		unset( $report['mycounter2'] );

	
		$this->assertEquals(
			array(
				'github_pr_unique_issue_issues' => 3,
			),
			$report
		);	
	}
}

