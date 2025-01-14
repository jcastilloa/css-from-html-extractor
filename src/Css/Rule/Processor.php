<?php

namespace CSSFromHTMLExtractor\Css\Rule;

//use Sabberworm\CSS\OutputFormat;
//use Sabberworm\CSS\Parser;
use Symfony\Component\CssSelector\Node\Specificity;
use \CSSFromHTMLExtractor\Css\Property\Processor as PropertyProcessor;

class Processor
{
    /**
     * Split a string into seperate rules
     *
     * @param string $rulesString
     *
     * @return array
     */
    public function splitIntoSeparateMediaQueries($rulesString): array
    {
        // Intelligently break up rules, preserving mediaquery context and such

        $mediaQuerySelector = '/@media[^{]+\{([\s\S]+?\})\s*\}/';
        $mediaQueryMatches = [];
        preg_match_all($mediaQuerySelector, $rulesString, $mediaQueryMatches);

        $remainingRuleSet = $rulesString;

        $queryParts = [];
        foreach (reset($mediaQueryMatches) as $mediaQueryMatch) {
            $tokenisedRules = explode($mediaQueryMatch, $remainingRuleSet);

            $queryParts[] = reset($tokenisedRules);
            $queryParts[] = $mediaQueryMatch;

            if (count($tokenisedRules) === 2) {
                $remainingRuleSet = end($tokenisedRules);
            } else {
                $remainingRuleSet = '';
            }
        }

        if (!empty($remainingRuleSet)) {
            $queryParts[] = $remainingRuleSet;
        }

        $indexedRules = [];

        foreach ($queryParts as $part) {
            if (strpos($part, '@media') === false) {
                $indexedRules[][''] = (array)explode('}', $part);
                continue;
            }

            $mediaQueryString = substr($part, 0, strpos($part, '{'));

            // No need for print css
            if (trim($mediaQueryString) === '@media print') {
                continue;
            }

            $mediaQueryRules = substr($part, strpos($part, '{') + 1);

            $mediaQueryRules = substr($mediaQueryRules, 0, -1);


            $indexedRules[][$mediaQueryString] = (array)explode('}', $mediaQueryRules);
        }

        return $indexedRules;
    }

    /**
     * @param string $string
     * @return string
     */
    private function cleanup(string $string): string
    {
        $string = str_replace(array("\r", "\n"), '', $string);
        $string = str_replace(array("\t"), ' ', $string);
        $string = str_replace('"', '\'', $string);
        $string = preg_replace('|/\*.*?\*/|', '', $string);
        $string = preg_replace('/\s\s+/', ' ', $string);

        $string = trim($string);

        return $string;
    }

    /**
     * Convert a rule-string into an object
     *
     * @param string $media
     * @param string $rule
     * @param int    $originalOrder
     * @return array
     */
    public function convertToObjects($media, $rule, $originalOrder): array
    {
        $rule = $this->cleanup($rule);

        $chunks = explode('{', $rule);

        $selectorIdentifier = 0;
        $ruleIdentifier = 1;

        if (!isset($chunks[$ruleIdentifier])) {
            return [];
        }
        $propertiesProcessor = new PropertyProcessor();
        $rules = [];
        $selectors = (array)explode(',', trim($chunks[$selectorIdentifier]));
        $properties = $propertiesProcessor->splitIntoSeparateProperties($chunks[$ruleIdentifier]);

        foreach ($selectors as $selector) {
            $selector = trim($selector);
            $specificity = $this->calculateSpecificityBasedOnASelector($selector);

            $rules[] = new Rule(
                $media,
                $selector,
                $propertiesProcessor->convertArrayToObjects($properties, $specificity),
                $specificity,
                $originalOrder
            );
        }

        return $rules;
    }

    /**
     * Calculate the specificity based on a CSS Selector string,
     * Based on the patterns from premailer/css_parser by Alex Dunae
     *
     * @see https://github.com/premailer/css_parser/blob/master/lib/css_parser/regexps.rb
     * @param string $selector
     * @return Specificity
     */
    public function calculateSpecificityBasedOnASelector($selector): Specificity
    {
        $idSelectorsPattern = "  \#";
        $classAttributesPseudoClassesSelectorsPattern = "  (\.[\w]+)                     # classes
                        |
                        \[(\w+)                       # attributes
                        |
                        (\:(                          # pseudo classes
                          link|visited|active
                          |hover|focus
                          |lang
                          |target
                          |enabled|disabled|checked|indeterminate
                          |root
                          |nth-child|nth-last-child|nth-of-type|nth-last-of-type
                          |first-child|last-child|first-of-type|last-of-type
                          |only-child|only-of-type
                          |empty|contains
                        ))";

        $typePseudoElementsSelectorPattern = "  ((^|[\s\+\>\~]+)[\w]+       # elements
                        |
                        \:{1,2}(                    # pseudo-elements
                          after|before
                          |first-letter|first-line
                          |selection
                        )
                      )";

        return new Specificity(
            preg_match_all("/{$idSelectorsPattern}/ix", $selector, $matches),
            preg_match_all("/{$classAttributesPseudoClassesSelectorsPattern}/ix", $selector, $matches),
            preg_match_all("/{$typePseudoElementsSelectorPattern}/ix", $selector, $matches)
        );
    }

    /**
     * @param array $mediaQueryRules
     * @param array $objects
     *
     * @return Rule[]
     */
    public function convertArrayToObjects(array $mediaQueryRules, array $objects = array()): array
    {
        foreach ($mediaQueryRules as $order => $ruleSet) {
            foreach (reset($ruleSet) as $rule) {
                $objects = array_merge($objects, $this->convertToObjects(key($ruleSet), $rule, $order));
            }
        }

        return $objects;
    }

    /**
     * Sort an array on the specificity element in an ascending way
     * Lower specificity will be sorted to the beginning of the array
     *
     * @return int
     * @param  Rule $e1 The first element.
     * @param  Rule $e2 The second element.
     */
    public static function sortOnSpecificity(Rule $e1, Rule $e2): int
    {
        $e1Specificity = $e1->getSpecificity();
        $value = $e1Specificity->compareTo($e2->getSpecificity());

        // if the specificity is the same, use the order in which the element appeared
        if ($value === 0) {
            $value = $e1->getOrder() - $e2->getOrder();
        }

        return $value;
    }
}
