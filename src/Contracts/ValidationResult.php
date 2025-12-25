<?php

declare(strict_types=1);

namespace Cline\Compliance\Contracts;

interface ValidationResult
{
    /**
     * Check if validation passed.
     */
    public function isValid(): bool;

    /**
     * Get validation errors.
     *
     * @return array<int, string>
     */
    public function getErrors(): array;
}
