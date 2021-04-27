<?php


namespace D9ify\tests;


use PHPUnit\Framework\TestCase;

class RequirementTest extends TestCase {

    protected \ReflectionClass $reflector;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reflector = new \ReflectionClass('D9ify\Composer\Requirement');
    }


    public function testRequirement() {
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
            $newInstance->isOlderThan("^2"),
            "Should correctly determine age is older than currently required.");
        $this->assertFalse(
            $newInstance->isOlderThan("^4"),
            "Should correctly determine age is younger than currently required.");
        $this->assertFalse(
            $newInstance->isYoungerThan("^2"),
            "Should correctly determine age is older than currently required.");
        $this->assertTrue(
            $newInstance->isYoungerThan("^4"),
            "Should correctly determine age is younger than currently required.");
    }

}
