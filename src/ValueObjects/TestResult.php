<?php

declare(strict_types=1);

namespace Cline\Compliance\ValueObjects;

final readonly class TestResult
{
    public function __construct(
        public string $id,
        public string $file,
        public string $group,
        public string $description,
        public mixed $data,
        public bool $expectedValid,
        public bool $actualValid,
        public bool $passed,
        public ?string $error = null,
    ) {}

    public static function pass(
        string $id,
        string $file,
        string $group,
        string $description,
        mixed $data,
        bool $expectedValid,
    ): self {
        return new self(
            id: $id,
            file: $file,
            group: $group,
            description: $description,
            data: $data,
            expectedValid: $expectedValid,
            actualValid: $expectedValid,
            passed: true,
        );
    }

    public static function fail(
        string $id,
        string $file,
        string $group,
        string $description,
        mixed $data,
        bool $expectedValid,
        bool $actualValid,
        ?string $error = null,
    ): self {
        return new self(
            id: $id,
            file: $file,
            group: $group,
            description: $description,
            data: $data,
            expectedValid: $expectedValid,
            actualValid: $actualValid,
            passed: false,
            error: $error,
        );
    }
}
