<?php
namespace NYPL\SchemaBuilder\Tests\Unit;

use NYPL\SchemaBuilder\Schema;

class SchemaTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage \NYPL\SchemaBuilder\Schema::EXCEPTION_SCHEMA_TYPE_REQUIRED
     */
    public function testConstructorWithoutTypeThrowsException()
    {
        new Schema();
    }

    public function testConstructorSetsType()
    {
        $type = 'Book';

        $schema = new Schema($type);

        $this->assertSame($type, $schema->getType());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage \NYPL\SchemaBuilder\Schema::EXCEPTION_SCHEMA_TYPE_INVALID
     */
    public function testTypeInLowercaseThrowsException()
    {
        new Schema('book');
    }

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage \NYPL\SchemaBuilder\Schema::EXCEPTION_PROPERTY_NAME_REQUIRED
     */
    public function testSetPropertyWithoutNameThrowsException()
    {
        $schema = new Schema('Book');

        $schema->addProperty('', 'Value');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage \NYPL\SchemaBuilder\Schema::EXCEPTION_PROPERTY_VALUE_INVALID
     */
    public function testSetPropertyWithNullValueThrowsException()
    {
        $schema = new Schema('Book');

        $schema->addProperty('name', null);
    }

    public function testSetPropertySetsProperty()
    {
        $schema = new Schema('Book');
        $propertyName = 'name';
        $propertyValue = 'Book Name';

        $schema->addProperty($propertyName, $propertyValue);

        $this->assertSame($propertyValue, $schema->getProperty($propertyName));
    }

    /**
     * @expectedException \OutOfBoundsException
     * @expectedExceptionMessage \NYPL\SchemaBuilder\Schema::EXCEPTION_PROPERTY_DOES_NOT_EXIST
     */
    public function testGetPropertyWithoutExistingThrowsException()
    {
        $schema = new Schema('Book');

        $schema->getProperty('invalid');
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage \NYPL\SchemaBuilder\Schema::EXCEPTION_PROPERTY_ALREADY_EXISTS
     */
    public function testAddingExistingPropertyThrowsException()
    {
        $schema = new Schema('Book');

        $schema->addProperty('name', 'Name 1');
        $schema->addProperty('name', 'Name 2');
    }

    public function testAddingSchemaAsPropertySetsParentPropertyName()
    {
        $propertyName = 'offers';

        $schema = new Schema('Book');

        $offer = new Schema('Offer');

        $schema->addProperty($propertyName, $offer);

        $this->assertSame($propertyName, $offer->getParentPropertyName());
    }

    public function testGetSchemaValue()
    {
        $propertyName = 'name';
        $name = 'Author';

        $schema = new Schema('Book');

        $schema->addProperty($propertyName, $name);

        $this->expectOutputString($name);

        $schema->outputProperty($propertyName);
    }
}
