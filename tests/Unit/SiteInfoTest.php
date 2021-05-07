<?php

namespace D9ify\tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Class SiteInfoTest
 *
 * @package D9ify\tests\unit
 */
class SiteInfoTest extends TestCase {

  /**
   * @var \ReflectionClass
   */
  protected \ReflectionClass $reflector;

  /**
   * Set up class Tester
   */
  protected function setUp(): void
  {
    parent::setUp();
    $this->reflector = new \ReflectionClass('\D9ify\Site\Info');
  }

  /**
   * Test basic site functions.
   *
   * @test
   */
  function testSiteInfoClass() {
    $instance = $this->reflector->newInstance();
    $this->assertInstanceOf('\D9ify\Site\Info', $instance, "Newly created class should instantiate without error");
  }

  /**
   * Test "getRef" function.
   *
   * @test
   */
  function testGetRef() {

    $instance = $this->reflector->newInstance('new-site-id');
    $this->assertEquals($instance->getRef(), 'new-site-id', "New instance with site name should return site name ref");
    $this->assertEquals($instance->getName(), 'new-site-id', "New instance with site name should return site name");

    $instance2 = $this->reflector->newInstance('fb9908f5-e717-4144-ac38-cd03b201b66b');
    $this->assertEquals($instance2->getRef(), 'fb9908f5-e717-4144-ac38-cd03b201b66b', "New instance with site id should return site uuid");
    $this->assertEquals($instance2->getId(), 'fb9908f5-e717-4144-ac38-cd03b201b66b', "New instance with site name should return site uuid");

  }

}

