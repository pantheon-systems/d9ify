<?php


namespace D9ify\tests\Functional;


use PHPUnit\Framework\TestCase;

/**
 * Class ComposerFileFunctionalTest
 * @package D9ify\tests\Functional
 */
class ComposerFileFunctionalTest extends TestCase
{

    /**
     * @var \ReflectionClass
     */
    protected \ReflectionClass $reflector;
    /**
     * @var string
     */
    protected static string $CONTENTS_BEFORE = "tests/fixtures/composer-1.json";
    protected static string $CONTENTS_AFTER = "tests/fixtures/composer-1-result.json";
    protected static string $CONTENTS_OLDER = "tests/fixtures/composer-1-result.json";


    /**
     *
     */
    function setUp(): void
    {
        parent::setUp();
        $this->reflector = new \ReflectionClass('\D9ify\Composer\ComposerFile');
    }

    /**
     * @test
     *
     * @throws \ReflectionException
     */
    public function testComposerAddPackage()
    {
        $testInstance = $this->reflector->newInstance(static::$CONTENTS_BEFORE);
        // add a missing package.
        $testInstance->addRequirement("composer/installers", "^1.0.20");
        $result = $testInstance->__toArray();
        $this->assertArrayHasKey("require", $result, "Require array should be set");
        $this->assertArrayHasKey("composer/installers", $result['require'],
            "Require array should have composer/installer version");

        // diff the result.
        $this->assertEquals(
            "^1",
            substr($result['require']['composer/installers'], 0, 2),
            "Adding a single requirement package should generate an exact copy of the result file."
            . print_r($result, true));
    }

    /**
     * @test
     *
     * @throws \ReflectionException
     */
    public function testAddExistingPackage()
    {
        $testAlreadyAdded = $this->reflector->newInstance(static::$CONTENTS_AFTER);
        $testAlreadyAdded->addRequirement("composer/installers", "^1.0.20");
        $result = $testAlreadyAdded->__toArray();
        $this->assertEquals(
            "^1",
            substr($result['require']['composer/installers'], 0, 2),
            "Adding a single requirement package should generate an exact copy of the result file."
            . print_r($result, true));
    }

    /**
     * @test
     *
     * @throws \JsonException
     * @throws \ReflectionException
     */
    public function testAddSameMajorVersionOfExisting()
    {
        $contents_after_file = __DIR__ . '/../fixtures/composer-1-result.json';
        $contents_older = __DIR__ . '/../fixtures/composer-1-older.json';
        $contents_after = file_get_contents($contents_after_file);
        $after = json_decode($contents_after, true, 255, JSON_THROW_ON_ERROR);
        $testOlderVersion = $this->reflector->newInstance($contents_older);
        $testOlderVersion->addRequirement("composer/installers", "^1.0.22");
        $result = $testOlderVersion->__toArray();

        $this->assertArrayHasKey(
            "require",
            $result,
            "Adding a package newer than existing version should simply update the version."
            . print_r($result, true));

        $this->assertArrayHasKey(
            "composer/installers",
            $result['require'],
            "Adding a package newer than existing version should simply update the version."
            . print_r($result, true));

        $this->assertEquals(
            "^1",
            substr($result['require']['composer/installers'], 0, 2),
            "Adding a package newer than existing version should simply update the version."
            . print_r($result, true));
    }

    /**
     * @test
     * @throws \JsonException
     * @throws \ReflectionException
     */
    public function testAddNewMajorVersionOfExisting()
    {
        $contents_after = file_get_contents(static::$CONTENTS_AFTER);
        $after = json_decode($contents_after, true, 255, JSON_THROW_ON_ERROR);

        $testOlderVersion = $this->reflector->newInstance(static::$CONTENTS_OLDER);
        $testOlderVersion->addRequirement("composer/installers", "^4.0");
        $result = $testOlderVersion->__toArray();
        $this->assertArrayHasKey(
            "require",
            $result,
            "Adding a package newer than existing version should simply update the version."
            . print_r($result, true));

        $this->assertArrayHasKey(
            "composer/installers",
            $result['require'],
            "Adding a package newer than existing version should simply update the version."
            . print_r($result, true));

        $this->assertEquals(
            "^4",
            substr($result['require']['composer/installers'], 0, 2),
            "Adding a package newer than existing version should simply update the version."
            . print_r($result, true));
    }

    /**
     * @test
     *
     * @throws \ReflectionException
     */
    public function testComposerAddDevPackage()
    {
        $testInstance = $this->reflector->newInstance(static::$CONTENTS_BEFORE);
        // add a missing package.
        $testInstance->addDevRequirement("composer/installers", "^1.0.20");
        $result = $testInstance->__toArray();
        $this->assertArrayHasKey("require-dev", $result, "Require array should be set");
        $this->assertArrayHasKey("composer/installers", $result['require-dev'],
            "Require array should have composer/installer version");

        // diff the result.
        $this->assertEquals(
            "^1",
            substr($result['require-dev']['composer/installers'], 0, 2),
            "Adding a single requirement package should generate an exact copy of the result file."
            . print_r($result, true));
    }

    /**
     * @test
     *
     * @throws \ReflectionException
     */
    public function testAddExistingDevPackage()
    {

        $contents_after = file_get_contents(static::$CONTENTS_AFTER);
        $after = json_decode($contents_after, true, 255, JSON_THROW_ON_ERROR);
        $testAlreadyAdded = $this->reflector->newInstance(static::$CONTENTS_AFTER);
        $testAlreadyAdded->addDevRequirement("composer/installers", "^1.0.20");
        $result = $testAlreadyAdded->__toArray();
        $this->assertEquals(
            "^1",
            substr($result['require-dev']['composer/installers'], 0, 2),
            "Adding a single requirement package should generate an exact copy of the result file."
            . print_r($result, true));
    }

    /**
     * @test
     *
     * @throws \JsonException
     * @throws \ReflectionException
     */
    public function testAddSameMajorVersionOfExistingDev()
    {

        $contents_after = file_get_contents(static::$CONTENTS_AFTER);
        $after = json_decode($contents_after, true, 255, JSON_THROW_ON_ERROR);
        $testOlderVersion = $this->reflector->newInstance(static::$CONTENTS_OLDER);
        $testOlderVersion->addDevRequirement("composer/installers", "^1.0.22");
        $result = $testOlderVersion->__toArray();

        $this->assertArrayHasKey(
            "require-dev",
            $result,
            "Adding a package newer than existing version should simply update the version."
            . print_r($result, true));

        $this->assertArrayHasKey(
            "composer/installers",
            $result['require-dev'],
            "Adding a package newer than existing version should simply update the version."
            . print_r($result, true));

        $this->assertEquals(
            "^1",
            substr($result['require-dev']['composer/installers'], 0, 2),
            "Adding a package newer than existing version should simply update the version."
            . print_r($result, true));
    }

    /**
     * @test
     * @throws \JsonException
     * @throws \ReflectionException
     */
    public function testAddNewMajorVersionOfExistingDev()
    {

        $contents_after = file_get_contents(static::$CONTENTS_AFTER);
        $after = json_decode($contents_after, true, 255, JSON_THROW_ON_ERROR);

        $testOlderVersion = $this->reflector->newInstance(static::$CONTENTS_OLDER);
        $testOlderVersion->addDevRequirement("composer/installers", "^4.0");
        $result = $testOlderVersion->__toArray();
        $this->assertArrayHasKey(
            "require-dev",
            $result,
            "Adding a package newer than existing version should simply update the version."
            . print_r($result, true));

        $this->assertArrayHasKey(
            "composer/installers",
            $result['require-dev'],
            "Adding a package newer than existing version should simply update the version."
            . print_r($result, true));

        $this->assertEquals(
            "^4",
            substr($result['require-dev']['composer/installers'], 0, 2),
            "Adding a package newer than existing version should simply update the version."
            . print_r($result, true));
    }
}
