<?php

namespace CSSFromHTMLExtractor\Css\Property;

use Symfony\Component\CssSelector\Node\Specificity;

final class Property
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $value;

    /**
     * @var Specificity
     */
    private $originalSpecificity;

    /**
     * Property constructor.
     * @param string           $name
     * @param string           $value
     * @param Specificity|null $specificity
     */
    public function __construct($name, $value, Specificity $specificity = null)
    {
        $this->name = $name;
        $this->value = $value;
        $this->originalSpecificity = $specificity;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Get originalSpecificity
     *
     * @return Specificity
     */
    public function getOriginalSpecificity(): ?Specificity
    {
        return $this->originalSpecificity;
    }

    /**
     * Is this property important?
     *
     * @return bool
     */
    public function isImportant(): bool
    {
        return (stripos($this->value, '!important') !== false);
    }

    /**
     * Get the textual representation of the property
     *
     * @return string
     */
    public function toString(): string
    {
        return sprintf(
            '%1$s: %2$s;',
            $this->name,
            $this->value
        );
    }
}
