<?php

namespace CSSFromHTMLExtractor;

class HtmlStore
{
    /** @var array Property objects, grouped by selector */
    private array $snippets = [];

    public function addHtmlSnippet($htmlSnippet): static
    {
        $this->snippets = array_merge($this->snippets, [$htmlSnippet]);
        return $this;
    }

    public function getSnippets(): array
    {
        return $this->snippets;
    }

    /**
     * @return $this
     */
    public function purge(): static
    {
        $this->snippets = [];

        return $this;
    }
}