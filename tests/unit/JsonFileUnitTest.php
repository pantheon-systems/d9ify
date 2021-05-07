<?php

namespace D9ify\tests\unit;

use PHPUnit\Framework\TestCase;

/**
 * Class JsonFileUnitTest
 * @package D9ify\tests
 */
class JsonFileUnitTest extends TestCase {

    /**
     * @var \ReflectionClass
     */
    protected \ReflectionClass $reflector;
    /**
     * @var string
     */
    public static $EXAMPLE = 'tests/fixtures/composer-1.json';

    /**
     * @setup
     */
    function setUp(): void
    {
        parent::setUp();
        $this->reflector = new \ReflectionClass('\D9ify\Utility\JsonFile');
    }

    /**
     * @test
     * @testdox Test functions against a known value for the original.
     */
    public function testOriginal() {
        $testInstance = $this->reflector->newInstance(static::$EXAMPLE);
        $this->assertIsArray($testInstance->getOriginal(), "Original should be an array");
        $this->assertIsString($testInstance->getOriginal()['name'], "Original should have a name value");
    }

    /**
     * @testdox Test basic file parsing.
     * @test
     * @throws \ReflectionException
     */
    public function testComposerFileRead()
    {
        $testInstance = $this->reflector->newInstance(static::$EXAMPLE);

        $this->assertEquals(
            $testInstance->getName(),
            "pantheon-upstreams/drupal-project",
            "Test property with known value."

        );
        $this->assertEquals(
            $testInstance->getDescription(),
            "Install Drupal 9 with Composer on Pantheon.",
            "Test property with known value."
        );
    }


    /**
     * @testdox Test the ability to normalize property names into a setter function name.
     * @test
     */
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
     * @testdox Test ability to normalize property names into a getter name.
     *
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

    /**
     * @testdox Test arrayification.
     *
     * @test
     */
    public function testArrayification() {

        $arrayificationator = $this->reflector->newInstance(getcwd() . "/tests/fixtures/composer-1.json", "r");
        $arrayificationator->setFoo("foo");
        $arrayificationator->setBar("bar");
        $result = $arrayificationator->__toArray();
        $this->assertIsArray($result, "Result should be an array.");
        $this->assertArrayHasKey('foo', $result, "Result should have known key.");
        $this->assertEquals('foo', $result['foo'], "Result should have known value.");
        $this->assertArrayHasKey('bar', $result, "Result should have known key.");
        $this->assertEquals('bar', $result['bar'], "Result should have known value.");
    }

}
