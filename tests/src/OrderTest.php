<?php

namespace ColumbusPHP\Tests\OrderedJobs;

use ColumbusPHP\OrderedJobs\Order;

/**
 * Test for Order class.
 *
 * @coversDefaultClass ColumbusPHP\OrderedJobs\Order
 * @group orderedjobs
 */
class OrderTest extends \PHPUnit_Framework_TestCase {


  /**
   * Assert that for a given sequence of jobs the output is ordered by
   * dependencies.
   *
   * @param array $jobs
   *   An array of jobs.
   * @param string $sequence
   *   The expected sequence of jobs.
   * @dataProvider jobsProvider
   */
  public function testOrder($jobs, $sequence) {

    $order = new Order($jobs);

    $this->assertEquals($sequence, $order->getSequence());
  }

  /**
   * Assert that an error is thrown for a self-referencing dependency.
   *
   * @expectedException \ColumbusPHP\OrderedJobs\SelfReferenceException
   */
  public function testSelfReferenceException() {
    $order = new Order("a =>\nb =>\nc => c");

    $order->getSequence();
  }

  /**
   * Assert that an error is thrown for a circular dependency chain.
   *
   * @expectedException \ColumbusPHP\OrderedJobs\CircularDependencyException
   */
  public function testCircularDependencyException() {
    $order = new Order("a =>\nb => c\nc => f\nd => a\ne =>\nf => b");

    $order->getSequence();
  }

  /**
   * Provide test cases for test.
   *
   * @return array
   *   An array of parameters for the test.
   */
  public function jobsProvider() {
    return [
      ['', ''],
      ['a =>', 'a'],
      ["a =>\nb =>\nc =>", 'abc'],
      ["a =>\nb => c\nc =>", 'acb'],
      ["a => c\nb => a\nc =>", 'cab'],
      ["a =>\nb => c\nc => f\nd => a\ne => b\nf =>", 'afcbde']
    ];
  }
}