<?php


namespace D9ify\tests\unit;


use PHPUnit\Framework\TestCase;

class ComposerFileTest extends TestCase
{

  protected $reflector;

  function setUp(): void
  {
    parent::setUp();
    $this->reflector = new \ReflectionClass('\D9ify\Composer\ComposerFile');
  }

  public function testComposerFileRead()
  {
    $testInstance = $this->reflector->newInstance(__DIR__ . '/../fixtures/composer-1.json');
    $this->assertInstanceOf('\D9ify\Composer\ComposerFile', $testInstance, "New Instance should be of composer class.");
  }

}
