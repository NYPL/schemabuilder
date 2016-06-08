<?php
namespace NYPL\SchemaBuilder\Outputter;

use NYPL\SchemaBuilder\Outputter;
use NYPL\SchemaBuilder\Schema;

class JsonLdOutputter extends Outputter
{
    const INDENT_STRING = '    ';

    /**
     * @var string
     */
    protected $jsonLd = '';

    /**
     * @var bool
     */
    protected $generated = false;

    /**
     * @param string $text
     * @param int $indentLevel
     * @param bool $isExcludeComma
     */
    protected function append($text = '', $indentLevel = 0, $isExcludeComma = false)
    {
        if ($indentLevel) {
            for ($i = 0; $i < $indentLevel; $i++) {
                $this->jsonLd .= self::INDENT_STRING;
            }
        }

        $this->jsonLd .= $text;
        if (!$isExcludeComma) {
            $this->jsonLd .= ',';
        }
        $this->jsonLd .= "\r\n";
    }

    protected function generateJsonLd()
    {
        if (!$this->isGenerated()) {
            $this->append('<script type="application/ld+json">', 0, true);

            $this->append('{', 1, true);

            $this->append('"@context": "' . self::SCHEMA_BASE_URL . '"', 2);
            $this->append('"@type": "' . $this->getSchema()->getType() . '"', 2);

            if ($this->getSchema()->getSchemaId()) {
                $this->append('"@id": "' . $this->getSchema()->getSchemaId() . '"', 2);
            }

            $this->generatePropertiesJsonLd($this->getSchema(), 2);

            $this->append('}', 1, true);

            $this->append('</script>', 0, true);

            $this->setGenerated(true);
        }
    }

    /**
     * @param Schema $schema
     * @param int $indentLevel
     */
    protected function generatePropertiesJsonLd(Schema $schema, $indentLevel = 0)
    {
        $propertyKeys = array_keys($schema->getProperties());
        $lastProperty = end($propertyKeys);

        /**
         * @var string|Schema $value
         */
        foreach ($schema->getProperties() as $property => $value) {
            if ($lastProperty == $property) {
                $isExcludeComma = true;
            } else {
                $isExcludeComma = false;
            }

            if ($value instanceof Schema) {
                $this->append('"' . $property . '": {', $indentLevel, true);

                $this->append(
                    '"@type": "' . $value->getType() . '"',
                    $indentLevel + 1,
                    !(bool) $value->getProperties()
                );

                $this->generatePropertiesJsonLd($value, $indentLevel + 1);

                $this->append('}', $indentLevel, $isExcludeComma);
            } else {
                $value = htmlentities($value);

                $this->append('"' . $property . '": "' . $value . '"', $indentLevel, $isExcludeComma);
            }
        }
    }

    /**
     * @return string
     */
    protected function getJsonLd()
    {
        return $this->jsonLd;
    }

    public function get()
    {
        $this->generateJsonLd();

        return $this->getJsonLd();
    }

    /**
     * @return boolean
     */
    protected function isGenerated()
    {
        return $this->generated;
    }

    /**
     * @param boolean $generated
     */
    protected function setGenerated($generated)
    {
        $this->generated = (bool) $generated;
    }
}
