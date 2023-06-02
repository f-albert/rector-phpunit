<?php

declare(strict_types=1);

namespace Rector\PHPUnit\NodeFactory;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;

final class ExpectExceptionFactory
{
    public function __construct(
        private readonly NodeNameResolver $nodeNameResolver,
        private readonly TestsNodeAnalyzer $testsNodeAnalyzer
    ) {
    }

    public function create(MethodCall $methodCall, Variable $variable): ?MethodCall
    {
        if (! $this->testsNodeAnalyzer->isInPHPUnitMethodCallName($methodCall, 'assertInstanceOf')) {
            return null;
        }

        if ($methodCall->isFirstClassCallable()) {
            return null;
        }

        $argumentVariableName = $this->nodeNameResolver->getName($methodCall->getArgs()[1]->value);
        if ($argumentVariableName === null) {
            return null;
        }

        // is na exception variable
        if (! $this->nodeNameResolver->isName($variable, $argumentVariableName)) {
            return null;
        }

        return new MethodCall($methodCall->var, 'expectException', [$methodCall->getArgs()[0]]);
    }
}
