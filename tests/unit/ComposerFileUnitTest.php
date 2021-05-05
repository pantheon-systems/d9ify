<?php


namespace D9ify\tests\unit;


use Composer\IO\BufferIO;
use Composer\IO\ConsoleIO;
use Composer\IO\IOInterface;
use PHPUnit\Framework\TestCase;

class ComposerFileUnitTest extends TestCase
{

    public static $EXAMPLE = '/../fixtures/composer-1.json';
    protected \ReflectionClass $reflector;
    protected IOInterface $ioMock;

    function setUp(): void
    {
        parent::setUp();
        $this->reflector = new \ReflectionClass('\D9ify\Composer\ComposerFile');
        $this->ioMock = new BufferIO();
    }


    /**
     * @test
     * @testdox test schema functions.
     */
    public function testSchemaFunctions()
    {

        $instance = $this->reflector->newInstance(
            __DIR__ . static::$EXAMPLE,
            "r",
            getcwd(),
            null,
            $this->ioMock
        );
        $result = $instance->getSchemaRef(null);
        $this->ioMock->write("INSTANCE" . print_r($instance, true));
        $this->ioMock->write("TOARRAY" . print_r($result, true));
        $this->assertIsArray($result, "getSchemaRef should return an array");
        $this->assertArrayHasKey('$ref', $result,
            sprintf("Array should have single element 'ref' %s", print_r($result, true))
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
    public function testSchemaValidation()
    {
        $instance = $this->reflector->newInstance(
            __DIR__ . static::$EXAMPLE,
            "r",
            getcwd(),
            null,
            $this->ioMock
        );
        $this->ioMock->write("INSTANCE" . print_r($instance, true));
        $this->assertTrue($instance->validateSchema(), "Known schema should validate.");
    }

    /**
     * @test
     * @testdox Test adding/setting "extra" property on the composer array.
     *
     * @throws \ReflectionException
     */
    public function testExtraProperty()
    {
        $instance = $this->reflector->newInstance(
            __DIR__ . static::$EXAMPLE,
            "r",
            getcwd(),
            null,
            $this->ioMock
        );
        $installerPaths = $instance->getExtraProperty('installer-paths');

        $installerPaths['web/libraries/{$name}'] = array_unique(
            array_merge($installerPaths['web/libraries/{$name}'] ?? [], [
                "type:bower-asset",
                "type:npm-asset",
            ])
        );
        $instance->setExtraProperty('installer-paths', $installerPaths);
        $result = $instance->__toArray();
        $this->assertArrayHasKey(
            'extra', $result,
            "Resulting array should have the extra property" . print_r($result['extra'], true));
        $this->assertArrayHasKey('web/libraries/{$name}',
            $result['extra']['installer-paths'],
            "Extra section should have installer paths property" . print_r($result['extra'], true));

        $this->assertArrayHasKey('web/libraries/{$name}',
            $result['extra']['installer-paths'],
            "Extra section should have installer paths property" . print_r($result['extra'], true));
        $this->assertEquals(
            3, count($result['extra']['installer-paths']['web/libraries/{$name}']),
            "Installer Paths should have 3 values" . print_r($result['extra'], true));
        $this->assertTrue(in_array("type:bower-asset", $result['extra']['installer-paths']['web/libraries/{$name}']),
            "test array for known value: " . print_r($result['extra'], true));
    }

    /**
     * @test
     * @testdox Add/set list of repositories from which to pull.
     *
     */
    public function testNormalizeRepositoriesProperty() {
        $instance = $this->reflector->newInstance(
            __DIR__ . static::$EXAMPLE,
            "r",
            getcwd(),
            null,
            $this->ioMock
        );
        $result = $instance->__toArray();
        // $this->ioMock->write("INSTANCE" . print_r($instance, true));
        // $this->ioMock->write("TOARRAY" . print_r($result, true));

        $this->assertTrue(count($result['repositories']) == 3,
            "Test normalization against known values.");

        $this->assertArrayHasKey('type', $result['repositories'][array_keys($result['repositories'])[0]],
            "Known repositories should have Key => value pairs"
            . print_r($result['repositories'], true));
        $this->assertArrayHasKey('type', $result['repositories'][array_keys($result['repositories'])[1]],
            "Known repositories should have Key => value pairs"
            . print_r($result['repositories'], true));
        $this->assertTrue(in_array($result['repositories'][array_keys($result['repositories'])[0]]['type'], ['path', 'composer']),
            "Test against known value: " . print_r($result['repositories'], true));

        $this->assertTrue(in_array($result['repositories'][array_keys($result['repositories'])[0]]['type'], ['path', 'composer']),
            "Known repositories should have Key => value pairs"
            . print_r($result['repositories'], true));


    }

    protected function tearDown(): void
    {
        if ($this->hasFailed()) {
            fwrite(STDERR, $this->ioMock->getOutput());
        }
    }
}
