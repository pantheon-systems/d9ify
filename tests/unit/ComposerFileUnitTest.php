<?php


namespace D9ify\tests\unit;


use PHPUnit\Framework\TestCase;

class ComposerFileUnitTest extends TestCase
{

    protected \ReflectionClass $reflector;
    public static $EXAMPLE = '/../fixtures/composer-1.json';


    function setUp(): void
    {
        parent::setUp();
        $this->reflector = new \ReflectionClass('\D9ify\Composer\ComposerFile');
    }



    /**
     * @test
     * @testdox test schema functions.
     */
    public function testSchemaFunctions() {
        $instance = $this->reflector->newInstance(
            __DIR__ . static::$EXAMPLE
        );
        $result = $instance->getSchemaRef(null);
        $this->assertIsArray($result, "getSchemaRef should return an array");
        $this->assertArrayHasKey('$ref', $result,
            sprintf("Array should have single element 'ref' %s", print_r($result, true) )
        );
        $this->assertIsString(
            $result['$ref'],
            "schema ref should be a string" . print_r($result, true)
        );
    }


    /**
     * @test
     * @testdox Test schema validation.
     */
    public function testSchemaValidation() {
        $instance = $this->reflector->newInstance(
            __DIR__ . static::$EXAMPLE
        );
        $this->assertTrue($instance->validateSchema(), "Known schema should validate.");
        $this->assertArrayHasKey('require', $instance->__toArray());
    }

}
