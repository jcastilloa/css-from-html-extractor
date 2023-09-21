<?php

namespace CSSFromHTMLExtractor\Css\Rule;

use CSSFromHTMLExtractor\Css\Property\Property;
use Symfony\Component\CssSelector\Node\Specificity;

final class Rule
{
    /**
     * @var string
     */
    private string $selector;

    /**
     * @var array
     */
    private array $properties;

    /**
     * @var Specificity
     */
    private Specificity $specificity;

    /**
     * @var integer
     */
    private int $order;

    /** @var string  */
    private string $media;

    /**
     * Rule constructor.
     *
     * @param string $media
     * @param string $selector
     * @param Property[] $properties
     * @param Specificity $specificity
     * @param int $order
     */
    public function __construct(string $media, string $selector, array $properties, Specificity $specificity, int $order)
    {
        $this->media = $media;
        $this->selector = $selector;
        $this->properties = $properties;
        $this->specificity = $specificity;
        $this->order = $order;
    }

    /**
     * @return string
     */
    public function getMedia(): string
    {
        return $this->media;
    }


    /**
     * Get selector
     *
     * @return string
     */
    public function getSelector(): string
    {
        return $this->selector;
    }

    /**
     * Get properties
     *
     * @return array
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * Get specificity
     *
     * @return Specificity
     */
    public function getSpecificity(): Specificity
    {
        return $this->specificity;
    }

    /**
     * Get order
     *
     * @return int
     */
    public function getOrder(): int
    {
        return $this->order;
    }
}
