<?php


namespace D9ify\tests\Functional;


use Composer\Semver\Comparator;
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
        $comparison = static::diffAssocRecursive($testInstance->__toArray(), $after);
        // diff the result.
        $this->assertEmpty(
            $comparison,
            "Adding a single requirement package should generate an exact copy of the result file."
            . print_r($comparison, true));


        $testAlreadyAdded = $this->reflector->newInstance($contents_after);
        $testAlreadyAdded->addRequirement("composer/installers", "^1.0.20");
        $comparison2 = static::diffAssocRecursive($testAlreadyAdded->__toArray(), $after);
        $this->assertEmpty(
            $comparison2,
            "Adding a package already included should not change the output."
            . print_r($comparison2, true));


        $testOlderVersion = $this->reflector->newInstance($contents_after);
        $testOlderVersion->addRequirement("composer/installers", "^1.0.19");
        $comparison3 = static::diffAssocRecursive($testAlreadyAdded->__toArray(), $after);
        $this->assertEmpty(
            $comparison3,
            "Adding an older version of the existing package " .
            "should generate an identical file and should ignore " .
            "the older version.");


        $testOlderVersion = $this->reflector->newInstance($contents_older);
        $testOlderVersion->addRequirement("composer/installers", "^1.0.22");
        $comparison4 = static::diffAssocRecursive($testOlderVersion->__toArray(), $after);

        $this->assertArrayHasKey(
            "require",
            $comparison4,
            "Adding a package newer than existing version should simply update the version."
            . print_r($comparison4, true));

        $this->assertArrayHasKey(
            "composer/installers",
            $comparison4['require'],
            "Adding a package newer than existing version should simply update the version."
            . print_r($comparison4, true));

        $this->assertEquals(
            substr($comparison4['require']['composer/installers'], 0, 2),
            "^1",
            "Adding a package newer than existing version should simply update the version."
            . print_r($comparison4, true));

        $testOlderVersion->addRequirement("composer/installers", "^4.0");
        $comparison5 = static::diffAssocRecursive($testOlderVersion->__toArray(), $after);

        $this->assertArrayHasKey(
            "require",
            $comparison5,
            "Adding a package newer than existing version should simply update the version."
            . print_r($comparison4, true));

        $this->assertArrayHasKey(
            "composer/installers",
            $comparison5['require'],
            "Adding a package newer than existing version should simply update the version."
            . print_r($comparison4, true));

        $this->assertEquals(
            substr($comparison5['require']['composer/installers'], 0, 2),
            "^4",
            "Adding a package newer than existing version should simply update the version."
            . print_r($comparison4, true));

    }

    public static function diffAssocRecursive(array $array1, array $array2)
    {
        $difference = array();
        foreach ($array1 as $key => $value) {
            if (is_array($value)) {
                if (!array_key_exists($key, $array2) || !is_array($array2[$key])) {
                    $difference[$key] = $value;
                } else {
                    $new_diff = static::diffAssocRecursive($value, $array2[$key]);
                    if (!empty($new_diff)) {
                        $difference[$key] = $new_diff;
                    }
                }
            } elseif (!array_key_exists($key, $array2) || $array2[$key] !== $value) {
                if (strpos($key, "/")) {
                    $difference[$key] = Comparator::greaterThan($array2[$key], $value) ? $array2[$key] : $value;
                    continue;
                }
                $difference[$key] = $value;
            }
        }
        return $difference;
    }
}
