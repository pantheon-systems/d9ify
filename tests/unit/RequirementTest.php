<?php


namespace D9ify\tests;

use PHPUnit\Framework\TestCase;

class RequirementTest extends TestCase
{

    protected \ReflectionClass $reflector;

    public function testRequirement()
    {
        /* var D9ify\Composer\Requirement $newInstance */
        $newInstance = $this->reflector->newInstance("composer/semver", "^3");
        $this->assertInstanceOf(
            '\D9ify\Composer\Requirement',
            $newInstance,
            "Class should instantiate with proper params.");
        $this->assertEquals(
            $newInstance->getName(),
            "composer/semver",
            "Name should be exactly as initialized.");
        $this->assertEquals(
            $newInstance->getName(),
            "composer/semver",
            "Version should be as initialized.");
        $this->assertTrue(
            $newInstance->isGreaterThan("^2"),
            "Should correctly determine age.");
        $this->assertTrue(
            $newInstance->isLessThan("^4.0"),
            "Should correctly determine age.");
        $this->assertFalse(
            $newInstance->isLessThan("^2"),
            "Should correctly determine age.");
        $this->assertFalse(
            $newInstance->isGreaterThan("^4"),
            "Should correctly determine age.");
    }

    public function testSetIfVersionGreater()
    {
        /* var D9ify\Composer\Requirement $newInstance */
        $newInstance = $this->reflector->newInstance("composer/semver", "^3");
        $newInstance->setVersionIfGreater("^3.0.1");
        $this->assertEquals(substr($newInstance->getVersion(), 0, 2), "^3",
            "As long as it's some variation of 3 I don't care.");
        $newInstance->setVersionIfGreater("^1.0");
        $this->assertEquals($newInstance->getVersion(), "^3.0.1",
            "required version is lower and versions should not be altered.");
        $newInstance->setVersionIfGreater("^2.0");
        $this->assertEquals($newInstance->getVersion(), "^3.0.1",
            "required version is lower and versions should not be altered.");


        $newInstance2 = $this->reflector->newInstance("composer/semver", "^1.0");
        $newInstance2->setVersionIfGreater("^2.0.34");
        $this->assertEquals(substr($newInstance2->getVersion(), 0, 2), "^2",
            "Versions should now be at some version of 2");
        $newInstance2->setVersionIfGreater("^3.4");
        $this->assertEquals($newInstance2->getVersion(), "^3.4",
            "Version should be updated to 3.4");
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->reflector = new \ReflectionClass('D9ify\Composer\Requirement');
    }

}
