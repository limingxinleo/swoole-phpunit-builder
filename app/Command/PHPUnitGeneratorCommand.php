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
namespace App\Command;

use App\Service\PHPUnitService;
use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * @Command
 */
class PHPUnitGeneratorCommand extends HyperfCommand
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        parent::__construct('gen:phpunit');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('Generate PHPUnit');
        $this->addArgument('file', InputArgument::OPTIONAL, 'PHPUnit 入口文件', BASE_PATH . '/build/phpunit');
    }

    public function handle()
    {
        $file = $this->input->getArgument('file');

        $code = di()->get(PHPUnitService::class)->build($file);

        file_put_contents($file, $code);
    }
}
