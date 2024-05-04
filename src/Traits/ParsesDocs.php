<?php

namespace AxeBear\Magic\Traits;

use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;
use ReflectionClass;

trait ParsesDocs
{
    public function getDocNode(mixed $reflection = null): PhpDocNode
    {
        $reflection ??= new ReflectionClass($this);
        $lexer = new Lexer();
        $parser = $this->getDocParser();
        $comment = $reflection->getDocComment();
        $tokens = new TokenIterator($lexer->tokenize($comment));

        return $parser->parse($tokens);
    }

    public function getDocParser(): PhpDocParser
    {
        $const = new ConstExprParser();
        $type = new TypeParser($const);

        return new PhpDocParser($type, $const);
    }
}
