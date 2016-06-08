<?php
namespace NYPL\SchemaBuilder\Tests;

use NYPL\SchemaBuilder\Schema;
use NYPL\SchemaBuilder\Outputter;

class JsonLdOutputterTest extends \PHPUnit_Framework_TestCase
{
    protected $itemType = 'Book';

    protected $propertyName = 'name';
    protected $propertyValue = 'Book name';

    /**
     * @var Schema
     */
    protected $schema;

    protected function setUp()
    {
        $schema = new Schema($this->itemType);

        $schema->addProperty($this->propertyName, $this->propertyValue);

        $this->schema = $schema;
    }

    public function testGetAddsScriptTag()
    {
        $this->assertContains('<script', $this->schema->getJsonLd());
    }

    public function testGetAddsType()
    {
        $this->assertContains('@type', $this->schema->getJsonLd());
    }

    public function testGetWithIdAddsId()
    {
        $id = '#test';

        $this->schema->setId($id);

        $this->assertContains('"@id": "' . $id . '"', $this->schema->getJsonLd());
    }

    public function testSchemaWithObjectAddsObject()
    {
        $person = new Schema('Person');
        $person->addProperty('name', 'Name');

        $this->schema->addProperty('person', $person);

        $this->assertContains('"@type": "' . $person->getType() . '"', $this->schema->getJsonLd());
    }

    public function testOutputJsonLd()
    {
        $this->expectOutputString($this->schema->getJsonLd());

        $this->schema->outputJsonLd();
    }
}
