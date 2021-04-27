<?php


namespace D9ify\tests\unit;


use PHPUnit\Framework\TestCase;

class ComposerFileUnitTest extends TestCase
{

    protected \ReflectionClass $reflector;

    function setUp(): void
    {
        parent::setUp();
        $this->reflector = new \ReflectionClass('\D9ify\Composer\ComposerFile');
    }

    public function testComposerFileRead()
    {
        $testInstance = $this->reflector->newInstance(__DIR__ . '/../fixtures/composer-1.json');
        $this->assertInstanceOf(
            '\D9ify\Composer\ComposerFile',
            $testInstance,
            "New Instance should be of composer class.");
    }



    public function testNormalizeSetters()
    {

        $setter = $this->reflector->getMethod("normalizeComposerPropertyToSetterName");
        $this->assertEquals(
            $setter->invokeArgs(
                null,
                ['require-dev']
            ),
            "setRequireDev",
            "Normalization Functions");
        $this->assertEquals(
            $setter->invokeArgs(
                null,
                ['require']
            ),
            "setRequire",
            "Normalization Functions");
        $this->assertEquals(
            $setter->invokeArgs(
                null,
                ['name']
            ),
            "setName",
            "Normalization Functions");
        $this->assertEquals(
            $setter->invokeArgs(
                null,
                ['autoload-dev']
            ),
            "setAutoloadDev",
            "Normalization Functions");
    }

    /**
     * @throws \ReflectionException
     * @test
     */
    public function testNormalizeGetters()
    {
        $getter = $this->reflector->getMethod("normalizeComposerPropertyToGetterName");
        $this->assertEquals(
            $getter->invokeArgs(
                null,
                ['require-dev']
            ),
            "getRequireDev",
            "Normalization Functions");
        $this->assertEquals(
            $getter->invokeArgs(
                null,
                ['require']
            ),
            "getRequire",
            "Normalization Functions");
        $this->assertEquals(
            $getter->invokeArgs(
                null,
                ['name']
            ),
            "getName",
            "Normalization Functions");
        $this->assertEquals(
            $getter->invokeArgs(
                null,
                ['autoload-dev']
            ),
            "getAutoloadDev",
            "Normalization Functions");
    }



}
