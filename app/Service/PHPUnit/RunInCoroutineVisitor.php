<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace App\Service\PHPUnit;

use PhpParser\Node;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\NodeVisitorAbstract;

class RunInCoroutineVisitor extends NodeVisitorAbstract
{
    public function afterTraverse(array $nodes)
    {
        $result = [];
        $callable = [];
        $runInCoroutine = false;
        foreach ($nodes as $node) {
            if (! $runInCoroutine) {
                $result[] = $node;
            } else {
                $callable[] = $node;
            }

            if ($node instanceof Declare_) {
                $result[] = new Node\Stmt\Expression(new Node\Expr\Assign(
                    new Node\Expr\Variable('code'),
                    new Node\Scalar\LNumber(0)
                ));
                $runInCoroutine = true;
            }
        }

        /** @var Node\Stmt\Expression $main */
        $main = array_pop($callable);
        $main = $main->expr;

        $main->args[] = new Node\Arg(new Node\Expr\ConstFetch(new Node\Name('false')));
        $callable[] = new Node\Stmt\Expression(new Node\Expr\Assign(
            new Node\Expr\Variable('code'),
            $main
        ));
        // $callable[] = $this->main;

        $expresion = new Node\Stmt\Expression(
            new Node\Expr\FuncCall(
                new Node\Name('Swoole\Coroutine\run'),
                [
                    new Node\Arg(new Node\Expr\Closure([
                        'stmts' => $callable,
                        'uses' => [
                            new Node\Expr\ClosureUse(new Node\Expr\Variable('code'), true),
                        ],
                    ])),
                ]
            )
        );
        $result[] = $expresion;
        $result[] = new Node\Stmt\Expression(new Node\Expr\Exit_(new Node\Expr\Variable('code')));
        return $result;
    }
}
