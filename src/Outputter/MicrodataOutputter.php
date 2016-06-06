<?php
namespace NYPL\Schema\Outputter;

use NYPL\Schema\Model\Schema;
use NYPL\Schema\Outputter;
use NYPL\Schema\Wrapper;
use NYPL\Schema\WrapperAttribute;
use Stringy\Stringy;

class MicrodataOutputter extends Outputter
{
    /**
     * @var bool
     */
    protected $objectOutputted = false;

    const EXCEPTION_PROPERTY_OUTPUTTED_BEFORE_OBJECT = 'Object must be outputted before any property is outputted';
    const EXCEPTION_SUB_PROPERTY_OUTPUTTED_BEFORE_OBJECT =
        'Child objects must be outputted before any property is outputted';
    const EXCEPTION_WRAPPER_INVALID = 'Wrapper specified is invalid';

    /**
     * @param string $wrapperName
     * @param WrapperAttribute[] $wrapperAttributes
     *
     * @return string
     */
    protected function getBaseObject($wrapperName = '', array $wrapperAttributes = array())
    {
        $this->setObjectOutputted(true);

        if ($wrapperName) {
            return $this->getBaseObjectWithWrapper($wrapperName, $wrapperAttributes);
        } else {
            if ($this->getSchema()->getParentPropertyName()) {
                return
                    'itemprop="' . $this->getSchema()->getParentPropertyName() . '" ' .
                    'itemscope itemtype="' . $this->getTypeUrl($this->getSchema()->getType()) . '"';
            } else {
                return 'itemscope itemtype="' . $this->getTypeUrl($this->getSchema()->getType()) . '"';
            }
        }
    }

    /**
     * @param string $wrapperName
     * @param WrapperAttribute[] $wrapperAttributes
     *
     * @return string
     */
    protected function getBaseObjectWithWrapper($wrapperName = '', array $wrapperAttributes = array())
    {
        $wrapper = new Wrapper($wrapperName);

        if ($this->getSchema()->getParentPropertyName()) {
            $wrapper->addAttribute('itemprop', $this->getSchema()->getParentPropertyName());
        }

        $wrapper->addAttribute('itemscope');
        $wrapper->addAttribute('itemtype', $this->getTypeUrl($this->getSchema()->getType()));

        return $wrapper->output($wrapperAttributes);
    }

    /**
     * @param string $type
     *
     * @return string
     */
    protected function getTypeUrl($type = '')
    {
        return self::SCHEMA_BASE_URL . '/' . $type;
    }

    /**
     * @param string $wrapperName
     */
    protected function checkWrapper($wrapperName = '')
    {
        if (Stringy::create($wrapperName)->contains('<')) {
            throw new \InvalidArgumentException(self::EXCEPTION_WRAPPER_INVALID . ': ' . $wrapperName);
        }
    }

    /**
     * @param string $propertyName
     * @param string $wrapperName
     * @param WrapperAttribute[] $wrapperAttributes
     *
     * @return string
     */
    protected function getPropertyWithWrapper($propertyName = '', $wrapperName = '', array $wrapperAttributes = [])
    {
        $this->checkWrapper($wrapperName);

        $wrapper = new Wrapper($wrapperName);

        $wrapper->addAttribute('itemprop', $propertyName);

        if ($this->getSchema()->getProperty($propertyName) instanceof Schema) {
            $wrapper->addAttribute('itemscope');
            
            $wrapper->addAttribute(
                'itemtype',
                $this->getTypeUrl($this->getSchema()->getProperty($propertyName)->getType())
            );
        } else {
            switch ($wrapperName) {
                case 'link':
                    $wrapper->addAttribute('href', $this->getSchema()->getProperty($propertyName));
                    break;

                case 'meta':
                    $wrapper->addAttribute('content', $this->getSchema()->getProperty($propertyName));
                    break;

                default:
                    $wrapper->addContent($this->getSchema()->getProperty($propertyName));
                    break;
            }
        }

        return $wrapper->output($wrapperAttributes);
    }

    /**
     * @param string $propertyName
     * @param string $wrapperName
     * @param WrapperAttribute[] $wrapperAttributes
     *
     * @return string
     */
    protected function getProperty($propertyName = '', $wrapperName = '', array $wrapperAttributes = array())
    {
        if (!$this->isObjectOutputted()) {
            if ($this->getSchema()->getParentPropertyName()) {
                throw new \RuntimeException(
                    self::EXCEPTION_SUB_PROPERTY_OUTPUTTED_BEFORE_OBJECT . ': ' . $propertyName
                );
            } else {
                throw new \RuntimeException(self::EXCEPTION_PROPERTY_OUTPUTTED_BEFORE_OBJECT . ': ' . $propertyName);
            }
        }

        if ($wrapperName) {
            return $this->getPropertyWithWrapper($propertyName, $wrapperName, $wrapperAttributes);
        } else {
            return 'itemprop="' . $propertyName . '"';
        }
    }

    /**
     * @param string $propertyName
     * @param string $wrapperName
     * @param WrapperAttribute[] $wrapperAttributes
     *
     * @return string
     */
    public function get($propertyName = '', $wrapperName = '', array $wrapperAttributes = array())
    {
        if (!$propertyName) {
            return $this->getBaseObject($wrapperName, $wrapperAttributes);
        } else {
            return $this->getProperty($propertyName, $wrapperName, $wrapperAttributes);
        }
    }

    /**
     * @param string $propertyName
     * @param string $wrapperName
     * @param WrapperAttribute[] $wrapperAttributes
     *
     * @return string
     */
    public function output($propertyName = '', $wrapperName = '', array $wrapperAttributes = array())
    {
        echo $this->get($propertyName, $wrapperName, $wrapperAttributes);
    }

    /**
     * @return boolean
     */
    protected function isObjectOutputted()
    {
        return $this->objectOutputted;
    }

    /**
     * @param boolean $objectOutputted
     */
    protected function setObjectOutputted($objectOutputted)
    {
        $this->objectOutputted = $objectOutputted;
    }
}
