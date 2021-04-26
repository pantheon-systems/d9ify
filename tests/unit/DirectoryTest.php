<?php


namespace D9ify\tests;


use D9ify\Site\Info;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;

class DirectoryTest extends TestCase {

  protected $reflector;

  protected function setUp(): void
  {
    parent::setUp();
    $this->reflector = new \ReflectionClass('\D9ify\Site\Directory');
  }

  function testDirectoryInstance() {
    $outputMock = $this->getMockBuilder(BufferedOutput::class)->getMock();
    $siteInfoMock = $this->getMockBuilder(Info::class)->setConstructorArgs(['newSite'])->getMock();
    $testInstance = $this->reflector->newInstance($siteInfoMock, $outputMock);
    $this->assertInstanceOf('\D9ify\Site\Directory', $testInstance, "New instance should be of directory class");
  }


}
