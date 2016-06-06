<?php
namespace NYPL\Schema\Tests;

use NYPL\Schema\Model\Schema;
use NYPL\Schema\Outputter;
use NYPL\Schema\WrapperAttribute;

class MicrodataOutputterTest extends \PHPUnit_Framework_TestCase
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

    public function testOutputBaseObject()
    {
        $this->assertSame(
            'itemscope itemtype="' . Outputter::SCHEMA_BASE_URL . '/' . $this->itemType . '"',
            $this->schema->getMicrodata()
        );
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage \NYPL\Schema\Outputter\MicrodataOutputter::EXCEPTION_PROPERTY_OUTPUTTED_BEFORE_OBJECT
     */
    public function testOutputPropertyValueObjectThrowsException()
    {
        $this->schema->getMicrodata($this->propertyName);
    }

    public function testOutputPropertyOnly()
    {
        $this->schema->getMicrodata();

        $this->assertSame(
            'itemprop="' . $this->propertyName . '"',
            $this->schema->getMicrodata($this->propertyName)
        );
    }

    public function testOutputPropertyWithGenericWrapper()
    {
        $wrapper = 'span';

        $this->schema->getMicrodata();

        $this->assertContains('<' . $wrapper, $this->schema->getMicrodata($this->propertyName, $wrapper));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage \NYPL\Schema\Outputter\MicrodataOutputter::EXCEPTION_WRAPPER_INVALID
     */
    public function testOutputWithInvalidWrapperThrowsException()
    {
        $div = '<span>';

        $this->schema->getMicrodata();

        $this->schema->getMicrodata('name', $div);
    }

    public function testOutputIsEncoded()
    {
        $propertyName = 'name';
        $propertyValue = '<Book>';
        $propertyValueEncoded = htmlentities($propertyValue);
        $wrapper = 'div';

        $schema = new Schema('Book');

        $schema->addProperty($propertyName, $propertyValue);

        $schema->getMicrodata();

        $this->assertSame(
            '<div itemprop="' . $propertyName . '">' . $propertyValueEncoded . '</div>' . "\r\n",
            $schema->getMicrodata($this->propertyName, $wrapper)
        );
    }

    public function testOutputPropertyWithLinkWrapper()
    {
        $wrapper = 'link';

        $this->schema->getMicrodata();

        $this->assertContains('<link', $this->schema->getMicrodata($this->propertyName, $wrapper));
        $this->assertContains('href', $this->schema->getMicrodata($this->propertyName, $wrapper));
    }

    public function testOutputPropertyWithMetaWrapper()
    {
        $wrapper = 'meta';

        $this->schema->getMicrodata();

        $this->assertContains('<meta', $this->schema->getMicrodata($this->propertyName, $wrapper));
        $this->assertContains('content', $this->schema->getMicrodata($this->propertyName, $wrapper));
    }

    public function testGetPropertyWithAddedAttributes()
    {
        $wrapper = 'div';
        $addedAttribute = 'addedAttribute';
        $addedValue = 'addedValue';

        $this->schema->getMicrodata();

        $this->assertContains(
            $addedAttribute . '="' . $addedValue . '"',
            $this->schema->getMicrodata(
                $this->propertyName,
                $wrapper,
                [new WrapperAttribute($addedAttribute, $addedValue)]
            )
        );
    }

    public function testGetPropertyWithSchemaAddsParentProperty()
    {
        $itemProperty = 'offers';

        $offer = new Schema('Offer');

        $this->schema->addProperty($itemProperty, $offer);

        $this->schema->getMicrodata();

        $this->assertContains('itemscope', $this->schema->getProperty('offers')->getMicrodata());
        $this->assertContains('itemprop="' . $itemProperty . '"', $this->schema->getProperty('offers')->getMicrodata());
    }

    public function testGetPropertyDivWithSchemaAddsParentProperty()
    {
        $itemProperty = 'offers';
        $wrapper = 'div';

        $offer = new Schema('Offer');

        $this->schema->addProperty($itemProperty, $offer);

        $this->schema->getMicrodata();

        $this->assertContains('<' . $wrapper, $this->schema->getProperty($itemProperty)->getMicrodata('', $wrapper));
        $this->assertContains('itemprop="' . $itemProperty . '"', $this->schema->getProperty($itemProperty)->getMicrodata('', $wrapper));
    }

    public function testGetSchemaOfObjectWithDiv()
    {
        $itemProperty = 'offers';
        $wrapper = 'div';

        $offer = new Schema('Offer');

        $this->schema->addProperty($itemProperty, $offer);

        $this->schema->getMicrodata();

        $this->assertContains('<' . $wrapper, $this->schema->getMicrodata($itemProperty, $wrapper));
        $this->assertContains('itemprop="' . $itemProperty . '"', $this->schema->getMicrodata($itemProperty, $wrapper));
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage \NYPL\Schema\Outputter\MicrodataOutputter::EXCEPTION_SUB_PROPERTY_OUTPUTTED_BEFORE_OBJECT
     */
    public function testParentPropertyThrowsExceptionIfNotOutputter()
    {
        $itemProperty = 'offers';
        $parentItemProperty = 'offeredBy';

        $offer = new Schema('Offer');

        $offer->addProperty($parentItemProperty, 'NYPL');

        $this->schema->addProperty($itemProperty, $offer);

        $this->schema->getMicrodata();

        $this->schema->getProperty('offers')->getMicrodata('offeredBy');
    }

    public function testOutputMicrodata()
    {
        $this->expectOutputString($this->schema->getMicrodata());

        $this->schema->outputMicrodata();
    }
}
