<?php

declare(strict_types=1);

namespace Cline\Compliance;

use Cline\Compliance\Commands\TestCommand;
use Symfony\Component\Console\Application as SymfonyApplication;

final class Application extends SymfonyApplication
{
    private const VERSION = '1.0.0';

    public function __construct()
    {
        parent::__construct('Compliance', self::VERSION);

        $this->add(new TestCommand());
        $this->setDefaultCommand('test', true);
    }
}
