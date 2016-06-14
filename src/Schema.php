<?php
namespace NYPL\SchemaBuilder;

use NYPL\SchemaBuilder\Outputter\JsonLdOutputter;
use NYPL\SchemaBuilder\Outputter\MicrodataOutputter;
use Stringy\Stringy;

class Schema extends Model
{
    const EXCEPTION_SCHEMA_TYPE_REQUIRED = 'Type is required for Schema.org object';
    const EXCEPTION_SCHEMA_TYPE_INVALID = 'Schema.org type does not appear to be valid';
    const EXCEPTION_PROPERTY_NAME_REQUIRED = 'Property name is required';
    const EXCEPTION_PROPERTY_VALUE_EMPTY = 'Property value cannot be null';
    const EXCEPTION_PROPERTY_VALUE_INVALID = 'Property value does not appear to be a valid type';
    const EXCEPTION_PROPERTY_DOES_NOT_EXIST = 'Property specified does not exist';
    const EXCEPTION_PROPERTY_ALREADY_EXISTS = 'Property specified already exists';

    /**
     * @var string
     */
    protected $type = '';

    /**
     * @var string
     */
    protected $schemaId = '';

    /**
     * @var array
     */
    protected $properties = array();

    /**
     * @var MicrodataOutputter
     */
    protected $microdataOutputter;

    /**
     * @var JsonLdOutputter
     */
    protected $jsonLdOutputter;

    /**
     * @var string
     */
    protected $parentPropertyName = '';

    /**
     * @param string $type
     */
    public function __construct($type = '')
    {
        $this->checkType($type);

        $this->setType($type);
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    protected function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @param string $propertyName
     * @param mixed $propertyValue
     */
    protected function appendProperty($propertyName = '', $propertyValue = null)
    {
        $this->properties[$propertyName] = $propertyValue;
    }

    /**
     * @param string $type
     */
    protected function checkType($type = '')
    {
        if (!$type) {
            throw new \BadMethodCallException(self::EXCEPTION_SCHEMA_TYPE_REQUIRED);
        }

        if (Stringy::create(substr($type, 0, 1))->isLowerCase()) {
            throw new \InvalidArgumentException(self::EXCEPTION_SCHEMA_TYPE_INVALID . ': ' . $type);
        }
    }

    /**
     * @param string $propertyName
     */
    protected function checkPropertyName($propertyName = '')
    {
        if (!$propertyName) {
            throw new \BadMethodCallException(self::EXCEPTION_PROPERTY_NAME_REQUIRED);
        }
    }

    /**
     * @param mixed $propertyValue
     *
     * @return bool
     */
    protected function checkPropertyValue($propertyValue = null)
    {
        if ($propertyValue === null) {
            throw new \InvalidArgumentException(self::EXCEPTION_PROPERTY_VALUE_EMPTY . ': '  . $propertyValue);
        }
    }

    /**
     * @param string $propertyName
     *
     * @return bool
     */
    public function isPropertyExists($propertyName = '')
    {
        if (!array_key_exists($propertyName, $this->properties)) {
            return false;
        }

        return true;
    }

    /**
     * @param string $propertyName
     * @param mixed $propertyValue
     */
    public function addProperty($propertyName = '', $propertyValue = null)
    {
        $this->checkPropertyName($propertyName);
        $this->checkPropertyValue($propertyValue);

        if ($this->isPropertyExists($propertyName)) {
            throw new \RuntimeException(self::EXCEPTION_PROPERTY_ALREADY_EXISTS);
        }

        if ($propertyValue instanceof Schema) {
            $propertyValue->setParentPropertyName($propertyName);
        }

        $this->appendProperty($propertyName, $propertyValue);
    }

    /**
     * @param string $propertyName
     *
     * @return string|Schema
     */
    public function getProperty($propertyName = '')
    {
        $this->checkPropertyName($propertyName);

        if (!$this->isPropertyExists($propertyName)) {
            throw new \OutOfBoundsException(self::EXCEPTION_PROPERTY_DOES_NOT_EXIST . ': ' . $propertyName);
        } else {
            return $this->properties[$propertyName];
        }
    }

    /**
     * @param string $propertyName
     * @param string $wrapper
     * @param WrapperAttribute[] $wrapperAttributes
     *
     * @return string
     */
    public function getMicrodata($propertyName = '', $wrapper = '', array $wrapperAttributes = array())
    {
        return $this->getMicrodataOutputter()->get($propertyName, $wrapper, $wrapperAttributes);
    }

    /**
     * @param string $propertyName
     * @param string $wrapper
     * @param WrapperAttribute[] $wrapperAttributes
     *
     * @return string
     */
    public function outputMicrodata($propertyName = '', $wrapper = '', array $wrapperAttributes = array())
    {
        echo $this->getMicrodata($propertyName, $wrapper, $wrapperAttributes);
    }

    public function getJsonLd()
    {
        return $this->getJsonLdOutputter()->get();
    }

    public function outputJsonLd()
    {
        echo $this->getJsonLd();
    }

    /**
     * @param string $propertyName
     *
     * @return string
     */
    public function outputProperty($propertyName = '')
    {
        echo $this->getProperty($propertyName);
    }

    /**
     * @return MicrodataOutputter
     */
    protected function getMicrodataOutputter()
    {
        if (!$this->microdataOutputter) {
            $this->setMicrodataOutputter(new MicrodataOutputter($this));
        }

        return $this->microdataOutputter;
    }

    /**
     * @param MicrodataOutputter $microdataOutputter
     */
    protected function setMicrodataOutputter(MicrodataOutputter $microdataOutputter)
    {
        $this->microdataOutputter = $microdataOutputter;
    }

    /**
     * @return string
     */
    public function getParentPropertyName()
    {
        return $this->parentPropertyName;
    }

    /**
     * @param string $parentPropertyName
     */
    protected function setParentPropertyName($parentPropertyName)
    {
        $this->parentPropertyName = $parentPropertyName;
    }

    /**
     * @return JsonLdOutputter
     */
    protected function getJsonLdOutputter()
    {
        if (!$this->jsonLdOutputter) {
            $this->setJsonLdOutputter(new JsonLdOutputter($this));
        }

        return $this->jsonLdOutputter;
    }

    /**
     * @param JsonLdOutputter $jsonLdOutputter
     */
    protected function setJsonLdOutputter($jsonLdOutputter)
    {
        $this->jsonLdOutputter = $jsonLdOutputter;
    }

    /**
     * @return array
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @return string
     */
    public function getSchemaId()
    {
        return $this->schemaId;
    }

    /**
     * @param string $schemaId
     */
    public function setSchemaId($schemaId)
    {
        $this->schemaId = $schemaId;
    }
}
