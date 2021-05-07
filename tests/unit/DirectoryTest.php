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

  function testDirectoryInstance() {
    $outputMock = $this->getMockBuilder(BufferedOutput::class)->getMock();
    $testInstance = $this->reflector->newInstance('newsite', $outputMock);
    $this->assertNull($testInstance->getComposerObject(),
        "New Directory should be null.");
    $this->expectException("Exception");
    $testInstance->ensure(false);

  }


}
