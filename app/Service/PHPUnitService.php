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
namespace App\Service;

use App\Service\PHPUnit\RunInCoroutineVisitor;
use Han\Utils\Service;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;

class PHPUnitService extends Service
{
    public function build(string $file): string
    {
        $parser = (new ParserFactory())->create(ParserFactory::ONLY_PHP7);
        $printer = new Standard();
        $traverser = new NodeTraverser();

        $code = file_get_contents($file);

        $stmts = $parser->parse($code);
        $traverser->addVisitor(new RunInCoroutineVisitor());
        $stmts = $traverser->traverse($stmts);
        return $printer->prettyPrintFile($stmts);
    }
}
