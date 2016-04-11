<?php


namespace ColumbusPHP\OrderedJobs;


/**
 * Class Order completes the Ordered Job kata.
 *4
 * @package ColumbusPHP\OrderedJob
 */
class Order {

  protected $jobs;

  /**
   * Order initialize method.
   *
   * @param string $job_string
   *   A job structure string.
   * @throws \ColumbusPHP\OrderedJobs\CircularDependencyException
   */
  public function __construct($job_string) {

    $this->jobs = $this->parseJobs($job_string);

    // Map all dependencies and find circular dependencies.
    foreach ($this->jobs as $job => $dep) {
      if ($dep) {
        $chain = '';
        $this->jobs[$job] = $this->getDependencyChain($dep, $job, $chain);
      }
    }
  }

  /**
   * @param string $value
   * @return array
   *   The jobs array.
   * @throws SelfReferenceException
   */
  protected function parseJobs($value) {
    $jobs = [];
    $matches = [];

    if (preg_match_all('/^([a-z])\s=>\s?([a-z]?)$/m', $value, $matches, PREG_SET_ORDER)) {
      foreach ($matches as $match) {
        if ($match[1] === $match[2]) {
          throw new SelfReferenceException('A job cannot depend on itself.');
        }
        $jobs[$match[1]] = $match[2];
      }
    }

    return $jobs;
  }

  /**
   * Get the sequence of the jobs array
   *
   * @return string
   *   Return the ordered sequence.
   */
  public function getSequence() {

    uksort($this->jobs, [$this, 'sort']);

    return array_reduce(array_keys($this->jobs), function (&$result, $item) {
      return $result .= $item;
    }, '');
  }

  /**
   * Sort two jobs by key.
   *
   * @param string $a
   * @param string $b
   * @return integer
   */
  public function sort($a, $b) {
    if ($this->isDependencyOf($a, $b) !== FALSE) {
      // $a is a dependency of $b
      return -1;
    }
    elseif ($this->isDependencyOf($b, $a) !== FALSE) {
      // $b is a dependency of $a
      return 1;
    }

    // Jobs without dependencies are weighted lower.
    if (!isset($this->jobs[$a]) && isset($this->jobs[$b])) {
      return 1;
    }
    elseif (!isset($this->jobs[$b]) && isset($this->jobs[$a])) {
      return -1;
    }

    // Otherwise do a string comparison of  the job names.
    return strcasecmp($a, $b);
  }

  /**
   * Is the first parameter a dependency of the second parameter.
   *
   * @param string $a
   * @param string $b
   * @return boolean
   */
  protected function isDependencyOf($a, $b) {
    return isset($this->jobs[$b]) ? strpos($this->jobs[$b], $a) : FALSE;
  }

  /**
   * @param $job
   * @param $initial
   * @param $chain
   * @return string
   * @throws \ColumbusPHP\OrderedJobs\CircularDependencyException
   */
  protected function getDependencyChain($job, $initial, &$chain) {
    $chain .= $job;

    if (strpos($chain, $initial)) {
      throw new CircularDependencyException(print_r($chain, TRUE));
    }

    if (strlen($job) === 1 && $this->jobs[$job]) {
      $chain .= $this->getDependencyChain($this->jobs[$job], $initial, $chain);
    }

    return $chain;
  }
}