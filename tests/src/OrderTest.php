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
   * @param string $jobs
   *   A set of jobs.
   * @param integer $position
   * @dataProvider jobsProvider
   */
  public function testOrder($jobs, $position) {

    $order = new Order($jobs);

    $this->assertGreaterThan($position, strpos($order->getSequence(), 'b'));
  }

  /**
   * Assert that an empty set of jobs and 1 job return appropriately.
   */
  public function testEmptyOrder() {
    $order = new Order('');
    $this->assertEquals('', $order->getSequence());
  }

  /**
   * Assert that a set of 1 job returns that 1 job.
   */
  public function testOneJob() {
    $order = new Order('a => ');
    $this->assertEquals('a', $order->getSequence());
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
      ["a =>\nb =>\nc =>", 0],
      ["a =>\nb => c\nc =>", 0],
      ["a => c\nb => a\nc =>", 1],
      ["a =>\nb => c\nc => f\nd => a\ne => b\nf =>", 1]
    ];
  }
}