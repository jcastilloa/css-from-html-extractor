<?php

namespace CSSFromHTMLExtractor\Css\Property;

use Symfony\Component\CssSelector\Node\Specificity;

class Processor
{
    /**
     * Split a string into seperate properties
     *
     * @param string $propertiesString
     * @return array
     */
    public function splitIntoSeparateProperties($propertiesString): array
    {
        $propertiesString = $this->cleanup($propertiesString);

        $properties = (array) explode(';', $propertiesString);
        $keysToRemove = array();
        $numberOfProperties = count($properties);

        for ($i = 0; $i < $numberOfProperties; $i++) {
            $properties[$i] = trim($properties[$i]);

            // if the new property begins with base64 it is part of the current property
            if (isset($properties[$i + 1]) && strpos(trim($properties[$i + 1]), 'base64,') === 0) {
                $properties[$i] .= ';' . trim($properties[$i + 1]);
                $keysToRemove[] = $i + 1;
            }
        }

        if (!empty($keysToRemove)) {
            foreach ($keysToRemove as $key) {
                unset($properties[$key]);
            }
        }

        return array_values($properties);
    }

    /**
     * @param string $string
     * @return mixed|string
     */
    private function cleanup($string): mixed
    {
        $string = str_replace(array("\r", "\n"), '', $string);
        $string = str_replace(array("\t"), ' ', $string);
        $string = str_replace('"', '\'', $string);
        $string = preg_replace('|/\*.*?\*/|', '', $string);
        $string = preg_replace('/\s\s+/', ' ', $string);

        $string = trim($string);
        $string = rtrim($string, ';');

        return $string;
    }

    /**
     * Convert a property-string into an object
     *
     * @param string $property
     * @return Property|null
     */
    public function convertToObject($property, Specificity $specificity = null): ?Property
    {
        if (strpos($property, ':') === false) {
            return null;
        }

        list($name, $value) = explode(':', $property, 2);

        $name = trim($name);
        $value = trim($value);

        if ($value === '') {
            return null;
        }

        return new Property($name, $value, $specificity);
    }

    /**
     * Convert an array of property-strings into objects
     *
     * @param array $properties
     * @return Property[]
     */
    public function convertArrayToObjects(array $properties, Specificity $specificity = null): array
    {
        $objects = array();

        foreach ($properties as $property) {
            $object = $this->convertToObject($property, $specificity);
            if ($object === null) {
                continue;
            }

            $objects[] = $object;
        }

        return $objects;
    }

    /**
     * Build the property-string for multiple properties
     *
     * @param array $properties
     * @return string
     */
    public function buildPropertiesString(array $properties): string
    {
        $chunks = array();

        foreach ($properties as $property) {
            $chunks[] = $property->toString();
        }

        return implode(' ', $chunks);
    }
}
