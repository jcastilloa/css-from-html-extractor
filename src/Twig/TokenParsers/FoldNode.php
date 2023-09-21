<?php

namespace CSSFromHTMLExtractor\Twig\TokenParsers;

use CSSFromHTMLExtractor\Twig\Extension;
use Twig\Compiler;
use \Twig\Node\Node;

class FoldNode extends Node
{

    public function __construct(Node $body, array $attributes, $lineno, $tag)
    {
        parent::__construct(['body' => $body], $attributes, $lineno, $tag);
    }

    public function compile(Compiler $compiler)
    {
        $compiler
            ->addDebugInfo($this)
            ->write("ob_start();\n")
            ->subcompile($this->getNode('body'))
            ->write("echo \$this->env->getExtension('".Extension::class."')->addCssToExtract(")
            ->raw('trim(ob_get_clean())')
            ->raw(");\n");
    }
}