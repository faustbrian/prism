<?php

declare(strict_types=1);

namespace Cline\Compliance\Services;

use Cline\Compliance\Contracts\ComplianceTestInterface;

final readonly class ConfigLoader
{
    /**
     * @return array<int, ComplianceTestInterface>
     */
    public function load(): array
    {
        $configPaths = [
            getcwd().'/compliance.php',
            getcwd().'/config/compliance.php',
        ];

        foreach ($configPaths as $path) {
            if (file_exists($path)) {
                $config = require $path;

                if (is_array($config)) {
                    return array_filter($config, fn ($item) => $item instanceof ComplianceTestInterface);
                }

                return [];
            }
        }

        return [];
    }
}
