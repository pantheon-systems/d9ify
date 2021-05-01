<?php

namespace D9ify\Composer;

use Composer\IO\IOInterface;
use Composer\Json\JsonFile;
use Composer\Util\HttpDownloader;
use Symfony\Component\Console\Output\OutputInterface;
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
     * @var array
     */
    protected array $original = [];

    /**
     * ComposerFile constructor.
     *
     * @param $filename
     * @param string $mode
     * @param false $useIncludePath
     * @param null $context
     *
     * @throws JsonException
     */
    public function __construct(
        $filename,
        HttpDownloader $httpDownloader = null,
        IOInterface $io = null
    ) {
        parent::__construct($filename, $httpDownloader, $io);
        if ($this->exists()) {
            $this->setOriginal();
            $this->parseOriginal();
        }
    }


    /**
     *
     */
    public function parseOriginal()
    {
        foreach ($this->original as $key => $value) {
            $this->{$this->normalizeComposerPropertyToSetterName($key)}($value);
        }
    }

    /**
     * @param $property
     * @return string
     */
    public static function normalizeComposerPropertyToSetterName($property)
    {
        return "set" . str_replace(" ", "", (ucwords(str_replace("-", " ", $property))));
    }

    /**
     * @param $property
     * @return string
     */
    public static function normalizeComposerPropertyToGetterName($property)
    {
        return "get" . str_replace(" ", "", (ucwords(str_replace("-", " ", $property))));
    }

    /**
     * @return array
     */
    public function getOriginal()
    {
        return $this->original;
    }

    /**
     * @throws JsonException
     */
    public function setOriginal()
    {
        $this->original = $this->read();
    }

    /**
     * @return array
     */
    public function getRequire(): array {
        return array_combine(array_keys($this->requirements), array_map(function($item) {
            return (string) $item;
        }, $this->requirements));
    }

    /**
     * @return array
     */
    public function getRequireDev(): array {
        return array_combine(array_keys($this->devRequirements), array_map(function($item) {
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
    public function validateSchema($schema = self::STRICT_SCHEMA, $schemaFile = null)
    {
        $data = (string) $this;
        if (null === $data && 'null' !== $content) {
            self::validateSyntax($content, $this->getPath());
        }
        $schema = static::getSchema();
        $schema->additionalProperties = true;
        $validator = new Validator();
        $validator->check($data, $schema);
    }

    /**
     * @throws Exception
     */
    public function getDiff()
    {
        throw new Exception("TODO: create this function");
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
            ], [$key, $value]);
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
        return json_encode($this->__toArray(), JSON_PRETTY_PRINT);
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
     * @param string $name
     * @param array $arguments
     * @return mixed
     * @throws \Exception
     */
    public function __call(string $name, array $arguments)
    {
        $varName = lcfirst(substr($name, 3));
        switch (substr($name, 0, 3)) {
            case "set":
                if ($varName == "require") {
                    foreach ($arguments[0] as $key => $value) {
                        $this->addRequirement($key, $value);
                    }
                }
                if ($varName == "requireDev") {
                    foreach ($arguments[0] as $key => $value) {
                        $this->addDevRequirement($key, $value);
                    }
                }
                $this->{$varName} = $arguments[0];
                break;
            case "get":
                return $this->{$varName} ?? null;

                break;

            default:
                throw new \Exception('cannot set requested property');
        }
    }

    /**
     * @return null[]|string[]
     */
    public static function getSchemaRef()
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
}
