<?php

namespace CSSFromHTMLExtractor\Css;

use CSSFromHTMLExtractor\Css\Rule\Processor as RuleProcessor;
use CSSFromHTMLExtractor\Css\Rule\Rule;

class Processor
{
    /**
     * Get the rules from a given CSS-string
     *
     * @param string $css
     * @param array $existingRules
     * @return Rule[]
     */
    public function getRules(string $css, array $existingRules = []): array
    {
        $css = $this->doCleanup($css);
        $rulesProcessor = new RuleProcessor();
        $rulesByMediaQuery = $rulesProcessor->splitIntoSeparateMediaQueries($css);

        return $rulesProcessor->convertArrayToObjects($rulesByMediaQuery, $existingRules);
    }

    public function getCharset($css)
    {
        preg_match_all('/@charset "[^"]++";/', $css, $matches);

        if ($charset = reset($matches)) {
            return reset($charset);
        }

        return null;
    }

    /**
     * Get the CSS from the style-tags in the given HTML-string
     *
     * @param string $html
     * @return string
     */
    public function getCssFromStyleTags($html): string
    {
        $css = '';
        $matches = array();
        preg_match_all('|<style(?:\s.*)?>(.*)</style>|isU', $html, $matches);

        if (!empty($matches[1])) {
            foreach ($matches[1] as $match) {
                $css .= trim($match) . "\n";
            }
        }

        return $css;
    }

    /**
     * @param string $css
     * @return string
     */
    private function doCleanup($css): string
    {
        // remove charset
        $css = preg_replace('/@charset "[^"]++";/', '', $css);

        $css = str_replace(array("\r", "\n"), '', $css);
        $css = str_replace(array("\t"), ' ', $css);
        $css = str_replace('"', '\'', $css);
        $css = preg_replace('|/\*.*?\*/|', '', $css);
        $css = preg_replace('/\s\s++/', ' ', $css);
        $css = trim($css);

        return $css;
    }
}
