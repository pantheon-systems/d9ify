<?php

namespace D9ify\Composer;

use Composer\IO\IOInterface;
use D9ify\Utility\JsonFile;
use Exception;
use Rogervila\ArrayDiffMultidimensional;
use JsonSchema\Validator;

/**
 * Class ComposerFile
 *
 * @package D9ify\Site
 */
class ComposerFile extends JsonFile
{

    /**
     * @var string|null
     */
    protected ?string $name = null;
    /**
     * @var string|null
     */
    protected ?string $description = null;
    /**
     * @var string|null
     */
    protected ?string $version = null;
    /**
     * @var string|null
     */
    protected ?string $type = null;
    /**
     * @var array
     */
    protected array $keywords = [];
    /**
     * @var string|null
     */
    protected ?string $homepage;
    /**
     * @var string|null
     */
    protected ?string $readme = null;
    /**
     * @var DateTime|null
     */
    protected ?DateTime $time = null;
    /**
     * @var string|null
     */
    protected ?string $license;
    /**
     * @var array
     */
    protected array $authors;
    /**
     * @var
     */
    protected $support;
    /**
     * @var
     */
    protected $autoload;
    /**
     * @var
     */
    protected $autoloadDev;
    /**
     * @var
     */
    protected $includePath;
    /**
     * @var array
     */
    protected array $requirements = [];
    /**
     * @var array
     */
    protected array $devRequirements = [];
    /**
     * @var array
     */
    protected array $conflict = [];
    /**
     * @var array
     */
    protected array $replace = [];
    /**
     * @var array
     */
    protected array $provide = [];
    /**
     * @var array
     */
    protected array $suggest = [];

    /**
     * @var IOInterface|null
     */
    protected ?IOInterface $io;

    /**
     * ComposerFile constructor.
     *
     * @param $filename
     * @param string $mode
     * @param false $useIncludePath
     * @param null $context
     *
     */
    public function __construct(
        $filename,
        $openMode = "r",
        $use_include_path = false,
        $context = null,
        IOInterface $io = null
    ) {
        parent::__construct($filename, $openMode, $use_include_path, $context);
        $this->io = $io;
    }


    /**
     * @param array $values
     */
    public function setRequire(array $values)
    {
        $this->requirements = $values;
    }

    /**
     * @param array $values
     */
    public function setRequireDev(array $values)
    {
        $this->devRequirements = $values;
    }

    /**
     * @return array
     */
    public function getRequire(): array
    {
        return array_combine(array_keys($this->requirements), array_map(function ($item) {
            return (string) $item;
        }, $this->requirements));
    }

    /**
     * @return array
     */
    public function getRequireDev(): array
    {
        return array_combine(array_keys($this->devRequirements), array_map(function ($item) {
            return (string) $item;
        }, $this->devRequirements));
    }



    /**
     * @return bool|void
     * @throws \Composer\Json\JsonValidationException
     * @throws \Seld\JsonLint\ParsingException
     */
    public function valid()
    {
        return $this->validateSchema();
    }

    /**
     * @param int $schema
     * @param null $schemaFile
     * @return bool|void
     * @throws \Seld\JsonLint\ParsingException
     */
    public function validateSchema()
    {
        $schema = static::getSchema();
        $schema->additionalProperties = true;
        $validator = new Validator();
        return $validator->check($this->__toArray(), $schema);
    }

    /**
     * @throws Exception
     */
    public function getDiff()
    {
        return ArrayDiffMultidimensional::compare($this->original, $this->__toArray());
    }

    /**
     * @throws JsonException
     */
    public function __toArray(): array
    {
        $toReturn = [];
        $schema = $this->getSchema();
        foreach ($schema->properties as $key => $value) {
            $array_value = call_user_func([
                $this,
                $this->normalizeComposerPropertyToGetterName($key)
            ]);
            if (!empty($array_value)) {
                $toReturn[$key] = $array_value;
            }
        }
        return $toReturn;
    }

    /**
     * @return string|void
     */
    public function __toString(): string
    {
        return json_encode($this->__toArray(), JSON_PRETTY_PRINT) ?? "{}";
    }

    /**
     * @param $package
     * @param $version
     */
    public function addRequirement($package, $version)
    {
        if (isset($this->requirements[$package])
            && $this->requirements[$package] instanceof Requirement) {
            $this->requirements[$package]->setVersionIfGreater($version);
            return;
        }
        $this->requirements[$package] = new Requirement($package, $version);
    }

    /**
     * @param $package
     * @param $version
     */
    public function addDevRequirement($package, $version) : void
    {
        if (isset($this->devRequirements[$package])
            && $this->devRequirements[$package] instanceof Requirement) {
            $this->devRequirements[$package] = $this->devRequirements[$package]->greaterThan($version) ?
                $this->devRequirements[$package] :  new Requirement($package, $version);
            return;
        }
        $this->devRequirements[$package] = new Requirement($package, $version);
    }

    /**
     * @return null|array
     */
    public static function getSchemaRef(): array
    {
        return ['$ref' => static::getSchemaFile()];
    }

    /**
     * @param string|null $schemaFile
     * @return string|null
     */
    public static function getSchemaFile(string $schemaFile = null)
    {
        if (null === $schemaFile) {
            $schemaFile = getcwd() . "/vendor/composer/composer/res/composer-schema.json";
        }

        // Prepend with file:// only when not using a special schema already (e.g. in the phar)
        if (false === strpos($schemaFile, '://')) {
            $schemaFile = 'file://' . $schemaFile;
        }

        return $schemaFile;
    }

    /**
     * @param string|null $schemaFile
     * @return \stdClass
     * @throws \JsonException
     */
    public static function getSchema(string $schemaFile = null): \stdClass
    {
        return json_decode(file_get_contents(static::getSchemaFile()), false, 512, JSON_THROW_ON_ERROR);
    }
    /**
     * @return IOInterface|null
     */
    public function getIo(): ?IOInterface
    {
        return $this->io;
    }

    /**
     * @param IOInterface|null $io
     */
    public function setIo(?IOInterface $io): void
    {
        $this->io = $io;
    }
}
