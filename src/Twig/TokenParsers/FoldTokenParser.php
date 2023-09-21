<?php

namespace CSSFromHTMLExtractor\Twig\TokenParsers;

use Twig\Error\SyntaxError;
use Twig\Node\Node;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;

class FoldTokenParser extends AbstractTokenParser
{

    /**
     * @param Token $token
     * @return Node|FoldNode
     * @throws SyntaxError
     */
    public function parse(Token $token): Node|FoldNode
    {
        $lineno = $token->getLine();
        $this->parser->getStream()->expect(Token::BLOCK_END_TYPE);
        $body = $this->parser->subparse([$this, 'decideFoldEnd'], true);
        $this->parser->getStream()->expect(Token::BLOCK_END_TYPE);
        return new FoldNode($body, [], $lineno, $this->getTag());
    }

    /**
     * Gets the tag name associated with this token parser.
     *
     * @return string The tag name
     */
    public function getTag(): string
    {
        return 'fold';
    }

    public function decideFoldEnd(Token $token): bool
    {
        return $token->test('endfold');
    }
}