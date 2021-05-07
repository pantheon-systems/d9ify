<?php


namespace D9ify\tests\unit;


use D9ify\Site\Info;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;

class DirectoryTest extends TestCase {

  protected \ReflectionClass $reflector;

  protected function setUp(): void
  {
    parent::setUp();
    $this->reflector = new \ReflectionClass('\D9ify\Site\Directory');
  }

    /**
     * @test
     * @testdox Test directory instance.
     *
     * @throws \ReflectionException
     */
  function testDirectoryInstance() {
    $outputMock = $this->getMockBuilder(BufferedOutput::class)->getMock();
    $testInstance = $this->reflector->newInstance('newsite', $outputMock);
    $this->expectException("Exception");
    $testInstance->ensure(false);
  }

    /**
     * @test
     * @testdox Test directory supporting objects.
     * @throws \ReflectionException
     */
  function testDirectorySupportingObjects() {
      $outputMock = $this->getMockBuilder(BufferedOutput::class)->getMock();
      $testInstance = $this->reflector->newInstance('newsite', $outputMock);
      $this->assertFalse($testInstance->getInfo()->valid(), "Get Info should return null");
      $this->assertNull($testInstance->getComposerObject(),
          "New Directory should be null.");
  }


}
