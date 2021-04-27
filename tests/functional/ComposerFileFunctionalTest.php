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
        $contents_older = __DIR__ . '/../fixtures/composer-1-older.json';
        $contents_before = __DIR__ . '/../fixtures/composer-1.json';
        $contents_after_file = __DIR__ . '/../fixtures/composer-1-result.json';
        $contents_after = file_get_contents($contents_after_file);
        $after = json_decode($contents_after, true, 255, JSON_THROW_ON_ERROR);
        $testInstance = $this->reflector->newInstance($contents_before);
        // add a missing package.
        $testInstance->addRequirement("composer/installers", "^1.0.20");
        $testInstanceArray = $testInstance->__toArray();
        // diff the result.
        $this->assertEmpty(
            static::diff_recursive($testInstanceArray, $after),
            "Adding a single requirement package should generate an exact copy of the result file.");

        $testAlreadyAdded = $this->reflector->newInstance($contents_after);
        $testAlreadyAdded->addRequirement("composer/installers", "^1.0.20");
        $this->assertEmpty(
            static::diff_recursive((string)$testAlreadyAdded, $after),
            "Adding a package already included should not change the output.");

        $testOlderVersion = $this->reflector->newInstance($contents_after);
        $testOlderVersion->addRequirement("composer/installers", "^1.0.19");
        $this->assertEmpty(
            static::diff_recursive((string)$testAlreadyAdded, $after),
            "Adding an older version of the existing package " .
            "should generate an identical file and should ignore " .
            "the older version.");


        $testOlderVersion = $this->reflector->newInstance($contents_older);
        $testOlderVersion->addRequirement("composer/installers", "^1.0.22");
        $item = static::diff_recursive((string)$testAlreadyAdded, $after);
        throw new \Exception(print_r(static::diff_recursive((string)$testAlreadyAdded, $after), true));
        $this->assertEmpty(
            static::diff_recursive((string)$testOlderVersion, $after),
            "Adding a package newer than existing version should simply update the version.");


        // add existing package.
        // Make sure it wasn't added twice.
    }

    public static function diff_recursive($array1, $array2)
    {
        $difference = [];
        foreach ((array)$array1 as $key => $value) {
            if (is_array($value) && isset($array2[$key])) { // it's an array and both have the key
                $new_diff = static::diff_recursive($value, $array2[$key]);
                if (!empty($new_diff)) {
                    $difference[$key] = $new_diff;
                } elseif (is_string($value) && !in_array($value, $array2)) {
                    // the value is a string and it's not in array B
                    $difference[$key] = $value . " is missing from the second array";
                } elseif (!is_numeric($key) && !array_key_exists($key, $array2)) {
                    // the key is not numberic and is missing from array B
                    $difference[$key] = "Missing from the second array";
                } elseif (is_string($value) && !in_array($value, $array1)) {
                    // the value is a string and it's not in array B
                    $difference[$key] = $value . " is missing from the first array";
                } elseif (!is_numeric($key) && !array_key_exists($key, $array1)) {
                    // the key is not numberic and is missing from array B
                    $difference[$key] = "Missing from the first array";
                } elseif (array_key_exists($key, $array2) && array_key_exists($key, $array1) && $array1[$key] !== $array2[$key]) {
                    // The value is not missing from either but differs in value
                    $difference[$key] = sprintf("Value Different in arrays: %s // %s ", $array1[$key], $array2[$key]);
                }
            }
        }
        return $difference;
    }
}
