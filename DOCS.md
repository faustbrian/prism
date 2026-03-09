## Table of Contents

1. [Overview](#doc-docs-readme) (`docs/README.md`)
2. [Advanced Features](#doc-docs-advanced-features) (`docs/advanced-features.md`)
3. [Configuration](#doc-docs-configuration) (`docs/configuration.md`)
4. [Custom Assertions](#doc-docs-custom-assertions) (`docs/custom-assertions.md`)
5. [Filtering](#doc-docs-filtering) (`docs/filtering.md`)
6. [Output Formats](#doc-docs-output-formats) (`docs/output-formats.md`)
7. [Performance](#doc-docs-performance) (`docs/performance.md`)
<a id="doc-docs-readme"></a>

Prism is a comprehensive testing CLI for PHP 8.5+ that provides a beautiful, feature-rich interface for running validation test suites with Termwind-powered output.

## Installation

```bash
composer require cline/prism
```

## Basic Usage

Prism executes validation tests defined in JSON test files and provides detailed reporting of results.

### 1. Create a Configuration File

Create a `prism.php` file in your project root:

```php
<?php

use Cline\Prism\Contracts\PrismTestInterface;

return [
    new class implements PrismTestInterface {
        public function getName(): string
        {
            return 'my-validator';
        }

        public function getTestDirectory(): string
        {
            return __DIR__ . '/tests/validation';
        }

        public function shouldIncludeFile(string $filePath): bool
        {
            return true; // Include all JSON files
        }

        public function decodeJson(string $json): mixed
        {
            return json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        }

        public function validate(mixed $data, mixed $schema = null): object
        {
            // Your validation logic here
            $isValid = /* your validation */;

            return new class($isValid) {
                public function __construct(private bool $isValid) {}
                public function isValid(): bool { return $this->isValid; }
            };
        }
    },
];
```

### 2. Create Test Files

Create JSON test files in your test directory:

```json
[
  {
    "description": "String validation tests",
    "schema": {
      "type": "string"
    },
    "tests": [
      {
        "description": "valid string",
        "data": "hello",
        "valid": true,
        "tags": ["string", "basic"]
      },
      {
        "description": "invalid type",
        "data": 123,
        "valid": false,
        "tags": ["string", "type-error"]
      }
    ]
  }
]
```

### 3. Run Tests

```bash
vendor/bin/prism test
```

## Output Modes

### Enhanced Terminal Output (Default)

Beautiful, color-coded output with Termwind:

```bash
vendor/bin/prism test
```

### CI-Friendly Output

Plain text output suitable for continuous integration:

```bash
vendor/bin/prism test --ci
```

### Detailed Failures

Show detailed information with syntax-highlighted JSON diff:

```bash
vendor/bin/prism test --failures
```

### Verbose Mode

See each test as it runs with real-time progress:

```bash
vendor/bin/prism test --verbose
# or shorthand
vendor/bin/prism test -v
```

Displays:
- ✓/✗ icon for pass/fail
- Test group and description
- Execution time per test
- Error messages in red

### Version Information

Display the Prism version:

```bash
vendor/bin/prism --version
```

## Examples

Check the `examples/` directory in the Prism repository for complete working examples:

- **`basic/`** - JSON schema validation with type checking, required fields, and format validation
- **`filtering/`** - Tag-based filtering and test organization (coming soon)
- **`custom-assertions/`** - Domain-specific validation rules (coming soon)
- **`comparison/`** - Multi-validator comparison (coming soon)

Each example includes:
- Complete `prism.php` configuration
- Test cases with expected results
- README with usage instructions

## Quick Examples

### Filter Tests by Name Pattern

```bash
vendor/bin/prism test --filter "string.*"
```

### Run Tests for Specific Draft

```bash
vendor/bin/prism test --draft draft-7
```

### Run Tests in Parallel

```bash
vendor/bin/prism test --parallel 4
```

### Watch Mode

Automatically re-run tests when files change:

```bash
vendor/bin/prism test --watch
```

## Next Steps

- Learn about [configuration options](#doc-docs-configuration)
- Explore [filtering capabilities](#doc-docs-filtering)
- Discover [performance features](#doc-docs-performance)
- See [advanced features](#doc-docs-advanced-features)

<a id="doc-docs-advanced-features"></a>

Prism provides advanced features for comprehensive test validation and analysis.

## Snapshot Testing

Capture and compare test results against saved snapshots.

### Update Snapshots

Save current test results as the expected baseline:

```bash
vendor/bin/prism test --update-snapshots
```

### Check Against Snapshots

Compare current results against saved snapshots:

```bash
vendor/bin/prism test --check-snapshots
```

### Snapshot Output

```
Snapshot Comparison: my-validator

Changes Detected:
  ✓ New passing tests: 12
  ✗ New failing tests: 3
  ~ Changed tests: 7

Detailed Changes:
  + test-123: null → PASS
  - test-456: PASS → FAIL
  ~ test-789: PASS → PASS (duration: 0.12s → 0.34s)

Recommendation: Review changes before updating snapshots
```

### Use Cases

- **Regression detection**: Identify unexpected behavior changes
- **Refactoring safety**: Ensure validator behavior unchanged
- **CI validation**: Verify test results match expectations

### Workflow Example

```bash
# Establish baseline
vendor/bin/prism test --update-snapshots

# Make changes to validator
vim src/Validator.php

# Verify no regressions
vendor/bin/prism test --check-snapshots
```

## Fuzzing and Test Generation

Generate random test cases to discover edge cases:

```bash
vendor/bin/prism test --fuzz 1000
```

Generates and runs 1000 random test cases per validator.

### What Gets Generated

1. **Edge Cases**
   - null, true, false
   - Integer boundaries (PHP_INT_MAX, PHP_INT_MIN, 0, -1, 1)
   - Float edge cases (0.0, -0.0)
   - String edge cases (empty, whitespace, single char)
   - Large strings (1000, 10000 characters)
   - Array edge cases (empty, nested, mixed types)
   - Object structures

2. **Random Data**
   - Random integers (-1000 to 1000)
   - Random floats
   - Random strings (0-100 characters)
   - Random arrays (0-10 elements)
   - Random objects (0-5 properties)

### Fuzzing Output

```
Fuzzing Test Results

Total Fuzzed Tests: 1,000
Passed: 967
Failed: 33

Found 33 failure(s) during fuzzing:

1. fuzz-234
   Description: Fuzzed test with array data
   Error: Maximum nesting depth exceeded
   Duration: 0.0234s

2. fuzz-567
   Description: Fuzzed test with string data
   Error: Invalid UTF-8 sequence
   Duration: 0.0123s
```

### Use Cases

- **Edge case discovery**: Find unexpected failures
- **Robustness testing**: Verify validator handles random input
- **Security testing**: Identify potential vulnerabilities

### Example Workflow

```bash
# Run fuzzing to discover issues
vendor/bin/prism test --fuzz 5000

# Fix discovered issues
vim src/Validator.php

# Re-run fuzzing to verify fixes
vendor/bin/prism test --fuzz 5000
```

## Validator Comparison

Compare results across multiple validator implementations:

```bash
vendor/bin/prism test --compare-validators
```

### Configuration

Define multiple validators in `prism.php`:

```php
<?php

return [
    new OpisJsonSchemaValidator(),
    new JustValidateValidator(),
    new SwaggestValidator(),
];
```

### Comparison Output

```
Validator Comparison Report

Validators: opis-json-schema, justvalidate, swaggest
Total Tests: 1,247
Discrepancies: 23

Found 23 test(s) with differing results:

1. draft7/strings/pattern-01
   Description: Pattern validation with anchors
   Agreement: 66.7%
   Results:
     opis-json-schema: PASS (expected: valid, got: valid)
     justvalidate: FAIL (expected: valid, got: invalid)
     swaggest: PASS (expected: valid, got: valid)

2. draft7/numbers/multipleOf-02
   Description: Multiple of with floating point
   Agreement: 33.3%
   Results:
     opis-json-schema: FAIL (expected: valid, got: invalid)
     justvalidate: FAIL (expected: valid, got: invalid)
     swaggest: PASS (expected: valid, got: valid)
```

### Use Cases

- **Spec compliance**: Verify implementations agree
- **Edge case identification**: Find interpretation differences
- **Migration validation**: Compare old vs new validators

## Coverage Analysis

Analyze test coverage across groups, files, and tags:

```bash
vendor/bin/prism test --coverage
```

### Coverage Output

```
Test Coverage Analysis

Total Tests: 1,247
Passed: 1,198
Failed: 49
Pass Rate: 96.1%
Coverage Score: 87.3/100

Groups Coverage: 45 unique groups
  properties: 234 tests
  items: 187 tests
  required: 156 tests
  type: 143 tests
  anyOf: 98 tests
  ... and 40 more groups

Files Coverage: 78 unique files
  properties.json: 234 tests
  items.json: 187 tests
  required.json: 156 tests
  type.json: 143 tests
  anyOf.json: 98 tests
  ... and 73 more files

Tags Coverage: 12 unique tags
  required: 687 tests
  optional: 312 tests
  edge-case: 156 tests
  regression: 89 tests
  performance: 3 tests
  ... and 7 more tags
```

### Coverage Score Calculation

Weighted formula:
- 60% pass rate
- 20% group diversity (unique test groups)
- 20% file diversity (unique test files)

### Use Cases

- **Completeness check**: Verify comprehensive coverage
- **Gap identification**: Find untested areas
- **Quality metrics**: Track test suite health

## Interactive Mode

Menu-driven test configuration and execution:

```bash
vendor/bin/prism test --interactive
```

### Interactive Menu

```
Interactive Mode - Configure and run tests

Current Configuration:
  Filter: <none>
  Tag: <none>
  Parallel: 1 worker(s)
  Incremental: disabled
  Watch: disabled

Select action:
  > Run tests with current configuration
    Set name filter (regex)
    Set tag filter
    Configure parallel workers
    Toggle incremental mode
    Toggle watch mode
    Clear all filters
    Exit
```

### Features

- **Real-time configuration**: Adjust settings before running
- **Filter preview**: See current filter configuration
- **Immediate execution**: Run with configured options
- **Persistent session**: Keep testing until exit

### Use Cases

- **Exploratory testing**: Try different filter combinations
- **Learning**: Understand filter behavior interactively
- **Quick iteration**: Adjust and re-run easily

## Multiple Output Formats

Generate test results in various formats:

### JSON Output

```bash
vendor/bin/prism test --format json
```

### JUnit XML Output

```bash
vendor/bin/prism test --format xml
```

See [Output Formats](#doc-docs-output-formats) for detailed format specifications.

## Combining Advanced Features

### Comprehensive CI Pipeline

```bash
vendor/bin/prism test \
    -j 8 \
    --profile \
    --coverage \
    --check-snapshots \
    --format json > results.json
```

### Development Workflow

```bash
vendor/bin/prism test \
    --watch \
    --incremental \
    --interactive \
    --failures
```

### Quality Assurance

```bash
vendor/bin/prism test \
    --compare-validators \
    --fuzz 1000 \
    --coverage \
    --profile
```

## Next Steps

- See [output formats](#doc-docs-output-formats) for result formats
- Learn about [custom assertions](#doc-docs-custom-assertions)
- Explore [filtering options](#doc-docs-filtering)

<a id="doc-docs-configuration"></a>

Prism uses a `prism.php` configuration file to define test suites and validation logic.

## Configuration File Location

By default, Prism looks for `prism.php` in the current directory. You can specify a different path:

```bash
vendor/bin/prism test path/to/custom-prism.php
```

## PrismTestInterface

Each test suite implements `PrismTestInterface`:

```php
<?php

use Cline\Prism\Contracts\PrismTestInterface;

return [
    new class implements PrismTestInterface {
        public function getName(): string
        {
            return 'validator-name';
        }

        public function getTestDirectory(): string
        {
            return __DIR__ . '/tests/validation';
        }

        public function shouldIncludeFile(string $filePath): bool
        {
            // Filter which test files to include
            return !str_contains($filePath, 'optional');
        }

        public function decodeJson(string $json): mixed
        {
            return json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        }

        public function validate(mixed $data, mixed $schema = null): object
        {
            // Implement your validation logic
            $validator = new MyValidator($schema);
            $isValid = $validator->validate($data);

            return new class($isValid) {
                public function __construct(private bool $isValid) {}
                public function isValid(): bool { return $this->isValid; }
            };
        }
    },
];
```

## Required Methods

### getName(): string

Returns the name of this test suite. Used in test identifiers and output.

```php
public function getName(): string
{
    return 'json-schema-draft-7';
}
```

### getTestDirectory(): string

Returns the absolute path to the directory containing JSON test files.

```php
public function getTestDirectory(): string
{
    return __DIR__ . '/tests/json-schema/draft-7';
}
```

### shouldIncludeFile(string $filePath): bool

Filters which test files to include from the test directory.

```php
public function shouldIncludeFile(string $filePath): bool
{
    // Exclude optional tests
    if (str_contains($filePath, '/optional/')) {
        return false;
    }

    // Only include specific subdirectories
    return str_contains($filePath, '/required/');
}
```

### decodeJson(string $json): mixed

Decodes JSON test file contents. Allows custom JSON parsing logic.

```php
public function decodeJson(string $json): mixed
{
    return json_decode(
        json: $json,
        associative: true,
        depth: 512,
        flags: JSON_THROW_ON_ERROR
    );
}
```

### validate(mixed $data, mixed $schema = null): object

Executes validation logic and returns a result object with `isValid(): bool` method.

```php
public function validate(mixed $data, mixed $schema = null): object
{
    $validator = new JsonSchema\Validator();
    $validator->validate($data, $schema);

    return new class($validator->isValid()) {
        public function __construct(private bool $isValid) {}
        public function isValid(): bool { return $this->isValid; }
    };
}
```

## Multiple Test Suites

Configure multiple validators in one file:

```php
<?php

return [
    new Draft7Validator(),
    new Draft2020Validator(),
    new CustomValidator(),
];
```

Run tests for all suites:

```bash
vendor/bin/prism test
```

Or filter by suite name:

```bash
vendor/bin/prism test --draft draft-7
```

## Test File Structure

JSON test files follow this structure:

```json
[
  {
    "description": "Test group description",
    "schema": { "type": "string" },
    "tests": [
      {
        "description": "Test case description",
        "data": "test data",
        "valid": true,
        "tags": ["optional", "tag", "array"],
        "assertion": "custom-assertion-name"
      }
    ]
  }
]
```

### Test File Fields

- **description** (string, required): Description of the test group
- **schema** (mixed, optional): Schema to validate against
- **tests** (array, required): Array of individual test cases

### Test Case Fields

- **description** (string, required): Description of this test case
- **data** (mixed, required): Data to validate
- **valid** (boolean, required): Expected validation result
- **tags** (array, optional): Tags for filtering and organization
- **assertion** (string, optional): Custom assertion name to use

## Example: JSON Schema Validator

```php
<?php

use Cline\Prism\Contracts\PrismTestInterface;
use Opis\JsonSchema\Validator;

return [
    new class implements PrismTestInterface {
        public function getName(): string
        {
            return 'opis-json-schema';
        }

        public function getTestDirectory(): string
        {
            return __DIR__ . '/tests/JSON-Schema-Test-Suite/tests/draft7';
        }

        public function shouldIncludeFile(string $filePath): bool
        {
            // Exclude optional tests and format tests
            return !str_contains($filePath, '/optional/')
                && !str_contains($filePath, '/format/');
        }

        public function decodeJson(string $json): mixed
        {
            return json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        }

        public function validate(mixed $data, mixed $schema = null): object
        {
            $validator = new Validator();
            $result = $validator->validate($data, $schema);

            return new class($result->isValid()) {
                public function __construct(private bool $isValid) {}
                public function isValid(): bool { return $this->isValid; }
            };
        }
    },
];
```

## Next Steps

- Learn about [filtering options](#doc-docs-filtering)
- Explore [performance features](#doc-docs-performance)
- See [output formats](#doc-docs-output-formats)

<a id="doc-docs-custom-assertions"></a>

Prism supports custom assertion logic through a pluggable assertion interface, enabling complex validation rules beyond simple pass/fail checks.

## AssertionInterface

Custom assertions implement the `AssertionInterface`:

```php
<?php

namespace Cline\Prism\Contracts;

interface AssertionInterface
{
    /**
     * Execute the assertion against test data.
     *
     * @param  mixed $data          Data to validate
     * @param  mixed $expectedValid Expected validation result
     * @param  mixed $actualValid   Actual validation result from validator
     * @return bool  True if assertion passes, false otherwise
     */
    public function assert(mixed $data, mixed $expectedValid, mixed $actualValid): bool;

    /**
     * Get human-readable name for this assertion.
     */
    public function getName(): string;

    /**
     * Get failure message when assertion fails.
     *
     * @param  mixed  $data          Data that failed assertion
     * @param  mixed  $expectedValid Expected validation result
     * @param  mixed  $actualValid   Actual validation result
     * @return string Descriptive failure message
     */
    public function getFailureMessage(mixed $data, mixed $expectedValid, mixed $actualValid): string;
}
```

## Built-in Assertions

### StrictEqualityAssertion

Default assertion - validates exact equality:

```php
use Cline\Prism\Assertions\StrictEqualityAssertion;

$assertion = new StrictEqualityAssertion();

// Passes if expectedValid === actualValid
$assertion->assert($data, true, true);   // true
$assertion->assert($data, false, false); // true
$assertion->assert($data, true, false);  // false
```

**Use when**: Exact pass/fail validation required.

### AnyOfAssertion

Validates if actual result matches any of multiple expected values:

```php
use Cline\Prism\Assertions\AnyOfAssertion;

$assertion = new AnyOfAssertion();

// Passes if actualValid matches any value in expectedValid array
$assertion->assert($data, [true, false], true);  // true
$assertion->assert($data, [true, false], false); // true
$assertion->assert($data, [true], false);        // false
```

**Use when**: Multiple outcomes are acceptable (implementation-dependent edge cases).

## Creating Custom Assertions

### 1. Implement Interface

```php
<?php

namespace App\Assertions;

use Cline\Prism\Contracts\AssertionInterface;

final readonly class LenientBooleanAssertion implements AssertionInterface
{
    public function assert(mixed $data, mixed $expectedValid, mixed $actualValid): bool
    {
        // Treat any truthy/falsy value as matching boolean expectation
        $expectedBool = (bool) $expectedValid;
        $actualBool = (bool) $actualValid;

        return $expectedBool === $actualBool;
    }

    public function getName(): string
    {
        return 'LenientBoolean';
    }

    public function getFailureMessage(mixed $data, mixed $expectedValid, mixed $actualValid): string
    {
        return sprintf(
            'Expected boolean %s, but got %s',
            $expectedValid ? 'true' : 'false',
            $actualValid ? 'true' : 'false'
        );
    }
}
```

### 2. Register Assertion

Configure in `prism.php` by passing to `PrismRunner`:

```php
<?php

use Cline\Prism\Services\CustomAssertionService;
use App\Assertions\LenientBooleanAssertion;

$assertionService = new CustomAssertionService([
    'lenient-boolean' => new LenientBooleanAssertion(),
]);

// Pass to PrismRunner constructor
$runner = new PrismRunner($filterService, $assertionService);
```

### 3. Use in Tests

Reference assertion by name in test files:

```json
{
  "description": "Boolean validation",
  "schema": { "type": "boolean" },
  "tests": [
    {
      "description": "truthy value",
      "data": 1,
      "valid": true,
      "assertion": "lenient-boolean"
    },
    {
      "description": "falsy value",
      "data": 0,
      "valid": false,
      "assertion": "lenient-boolean"
    }
  ]
}
```

## Advanced Assertion Examples

### Range Tolerance Assertion

Allow validation results within a tolerance range:

```php
<?php

final readonly class RangeToleranceAssertion implements AssertionInterface
{
    public function __construct(
        private float $tolerance = 0.1
    ) {}

    public function assert(mixed $data, mixed $expectedValid, mixed $actualValid): bool
    {
        // For numeric comparisons, allow small differences
        if (is_numeric($expectedValid) && is_numeric($actualValid)) {
            return abs($expectedValid - $actualValid) <= $this->tolerance;
        }

        // Fall back to strict equality for non-numeric
        return $expectedValid === $actualValid;
    }

    public function getName(): string
    {
        return 'RangeTolerance';
    }

    public function getFailureMessage(mixed $data, mixed $expectedValid, mixed $actualValid): string
    {
        return sprintf(
            'Expected %s ±%s, but got %s',
            $expectedValid,
            $this->tolerance,
            $actualValid
        );
    }
}
```

### Error Type Assertion

Validate specific error types are thrown:

```php
<?php

final readonly class ErrorTypeAssertion implements AssertionInterface
{
    public function assert(mixed $data, mixed $expectedValid, mixed $actualValid): bool
    {
        // expectedValid contains expected error type
        // actualValid contains actual error type from validator

        if ($expectedValid === null && $actualValid === null) {
            return true; // Both succeeded
        }

        if ($expectedValid === null || $actualValid === null) {
            return false; // One succeeded, one failed
        }

        // Compare error types
        return $expectedValid === $actualValid;
    }

    public function getName(): string
    {
        return 'ErrorType';
    }

    public function getFailureMessage(mixed $data, mixed $expectedValid, mixed $actualValid): string
    {
        return sprintf(
            'Expected error type "%s", but got "%s"',
            $expectedValid ?? 'success',
            $actualValid ?? 'success'
        );
    }
}
```

### Schema Version Assertion

Different behavior for different schema versions:

```php
<?php

final readonly class SchemaVersionAssertion implements AssertionInterface
{
    public function assert(mixed $data, mixed $expectedValid, mixed $actualValid): bool
    {
        // expectedValid is array: ['draft-7' => true, 'draft-2020' => false]
        // actualValid is validation result for current schema version

        if (!is_array($expectedValid)) {
            return $expectedValid === $actualValid;
        }

        // Get current schema version from test context
        $currentVersion = $this->getCurrentSchemaVersion();

        $expected = $expectedValid[$currentVersion] ?? $expectedValid['default'] ?? true;

        return $expected === $actualValid;
    }

    public function getName(): string
    {
        return 'SchemaVersion';
    }

    public function getFailureMessage(mixed $data, mixed $expectedValid, mixed $actualValid): string
    {
        $version = $this->getCurrentSchemaVersion();

        return sprintf(
            'For schema version %s, expected %s but got %s',
            $version,
            $expectedValid[$version] ?? 'default',
            $actualValid ? 'valid' : 'invalid'
        );
    }

    private function getCurrentSchemaVersion(): string
    {
        // Implementation to detect current schema version
        return 'draft-7';
    }
}
```

## Using Assertions in Tests

### Default Assertion

Without specifying assertion field, uses `StrictEqualityAssertion`:

```json
{
  "description": "valid string",
  "data": "hello",
  "valid": true
}
```

### Named Assertion

Specify custom assertion by name:

```json
{
  "description": "lenient validation",
  "data": 1,
  "valid": true,
  "assertion": "lenient-boolean"
}
```

### Multiple Expected Values

Use `AnyOfAssertion` for multiple acceptable outcomes:

```json
{
  "description": "implementation dependent",
  "data": {"foo": "bar"},
  "valid": [true, false],
  "assertion": "any-of"
}
```

## Assertion Service

### Configuration

Create and configure assertion service:

```php
<?php

use Cline\Prism\Services\CustomAssertionService;
use App\Assertions\LenientBooleanAssertion;
use App\Assertions\RangeToleranceAssertion;

$assertionService = new CustomAssertionService([
    'lenient-boolean' => new LenientBooleanAssertion(),
    'range-tolerance' => new RangeToleranceAssertion(0.05),
    'error-type' => new ErrorTypeAssertion(),
]);
```

### Checking Available Assertions

```php
// Check if assertion registered
$assertionService->hasAssertion('lenient-boolean'); // true

// Get all assertion names
$names = $assertionService->getAssertionNames();
// ['lenient-boolean', 'range-tolerance', 'error-type']
```

### Listing Assertions

List registered assertions from CLI:

```bash
vendor/bin/prism test --list-assertions
```

Output:

```
Available Custom Assertions

Total: 3

  • lenient-boolean
  • range-tolerance
  • error-type

Use these assertions in your test files with the 'assertion' field.
```

## Best Practices

### 1. Descriptive Names

Use clear, descriptive assertion names:

```php
// Good
'lenient-boolean'
'range-tolerance'
'error-type-match'

// Bad
'custom1'
'my-assertion'
'test'
```

### 2. Meaningful Error Messages

Provide detailed failure messages:

```php
public function getFailureMessage(mixed $data, mixed $expectedValid, mixed $actualValid): string
{
    return sprintf(
        'Expected %s to validate as %s, but validator returned %s. Data: %s',
        $this->getName(),
        $expectedValid ? 'valid' : 'invalid',
        $actualValid ? 'valid' : 'invalid',
        json_encode($data)
    );
}
```

### 3. Type Safety

Use type hints and validation:

```php
public function assert(mixed $data, mixed $expectedValid, mixed $actualValid): bool
{
    if (!is_numeric($expectedValid) || !is_numeric($actualValid)) {
        throw new InvalidArgumentException('RangeTolerance requires numeric values');
    }

    return abs($expectedValid - $actualValid) <= $this->tolerance;
}
```

### 4. Fallback Behavior

Provide sensible fallbacks:

```php
public function assert(mixed $data, mixed $expectedValid, mixed $actualValid): bool
{
    // Try custom logic first
    if ($this->canUseCustomLogic($expectedValid, $actualValid)) {
        return $this->customAssert($expectedValid, $actualValid);
    }

    // Fall back to strict equality
    return $expectedValid === $actualValid;
}
```

### 5. Documentation

Document assertion behavior in code and docs:

```php
/**
 * Lenient boolean assertion allowing truthy/falsy value comparison.
 *
 * Treats any truthy value (1, "yes", true, [1]) as boolean true,
 * and any falsy value (0, "", false, null, []) as boolean false.
 *
 * Use for validators with flexible boolean interpretation.
 */
final readonly class LenientBooleanAssertion implements AssertionInterface
{
    // ...
}
```

## Next Steps

- Explore [filtering options](#doc-docs-filtering)
- Learn about [performance features](#doc-docs-performance)
- See [advanced features](#doc-docs-advanced-features)

<a id="doc-docs-filtering"></a>

Prism provides powerful filtering capabilities to run specific subsets of your test suite.

## Name Filter

Filter tests by name using regex patterns:

```bash
vendor/bin/prism test --filter "string.*"
```

Matches test descriptions against the provided regex pattern.

### Examples

```bash
# Run only string-related tests
vendor/bin/prism test --filter "^string"

# Run tests containing "validation"
vendor/bin/prism test --filter "validation"

# Run tests matching multiple patterns
vendor/bin/prism test --filter "string|number|boolean"
```

## Path Filter

Filter test files by path using glob patterns:

```bash
vendor/bin/prism test --path-filter "tests/required/**/*.json"
```

Only runs tests from files matching the glob pattern.

### Examples

```bash
# Run tests only from the 'required' directory
vendor/bin/prism test --path-filter "**/required/**"

# Run tests from specific subdirectories
vendor/bin/prism test --path-filter "tests/{draft7,draft2020}/**"

# Run only top-level test files
vendor/bin/prism test --path-filter "tests/*.json"
```

## Exclusion Filter

Exclude tests by name using regex patterns:

```bash
vendor/bin/prism test --exclude "optional|deprecated"
```

Excludes tests whose descriptions match the provided regex.

### Examples

```bash
# Exclude optional tests
vendor/bin/prism test --exclude "optional"

# Exclude multiple patterns
vendor/bin/prism test --exclude "optional|edge-case|experimental"

# Exclude tests with specific keywords
vendor/bin/prism test --exclude "slow|flaky"
```

## Tag Filter

Filter tests by tags defined in test files:

```bash
vendor/bin/prism test --tag "required"
```

Only runs tests that have the specified tag.

### Tag Definition

Define tags in your test files:

```json
{
  "description": "String validation",
  "schema": { "type": "string" },
  "tests": [
    {
      "description": "valid string",
      "data": "hello",
      "valid": true,
      "tags": ["string", "required", "basic"]
    },
    {
      "description": "edge case",
      "data": "",
      "valid": true,
      "tags": ["string", "edge-case"]
    }
  ]
}
```

### Examples

```bash
# Run only required tests
vendor/bin/prism test --tag "required"

# Run edge case tests
vendor/bin/prism test --tag "edge-case"

# Run regression tests
vendor/bin/prism test --tag "regression"
```

## Draft Filter

When using multiple validators, filter by validator name:

```bash
vendor/bin/prism test --draft "draft-7"
```

Runs tests only for the specified draft/validator.

### Examples

```bash
# Test only Draft 7 implementation
vendor/bin/prism test --draft "draft-7"

# Test only Draft 2020-12 implementation
vendor/bin/prism test --draft "draft-2020-12"
```

## Combining Filters

All filters can be combined for precise test selection:

```bash
vendor/bin/prism test \
    --filter "string" \
    --tag "required" \
    --exclude "optional" \
    --path-filter "**/core/**"
```

This runs tests that:
- Match "string" in the name
- Have the "required" tag
- Don't match "optional" in the name
- Are in files under a "core" directory

## Incremental Mode

Run only tests from files that changed since the last run:

```bash
vendor/bin/prism test --incremental
```

Prism tracks file modification times and only runs tests from changed files.

### Use Cases

- **Fast iteration**: Only run tests affected by recent changes
- **CI optimization**: Skip unchanged tests in continuous integration
- **Development workflow**: Quickly verify local changes

### How It Works

1. First run: All tests execute, file timestamps cached
2. Subsequent runs: Only changed files tested
3. Cache location: `.prism/incremental-cache.json`

### Example Workflow

```bash
# Initial run - all tests
vendor/bin/prism test --incremental

# Make changes to test files
vim tests/validation/strings.json

# Only changed tests run
vendor/bin/prism test --incremental
```

## Interactive Filter Configuration

Use interactive mode to configure filters through a menu:

```bash
vendor/bin/prism test --interactive
```

Interactive mode provides:
- Name filter configuration
- Tag selection
- Parallel worker configuration
- Real-time filter preview
- Test execution with configured filters

### Interactive Menu

```
Current Configuration:
  Filter: <none>
  Tag: <none>
  Parallel: 1 worker(s)

Select action:
  > Run tests with current configuration
    Set name filter (regex)
    Set tag filter
    Configure parallel workers
    Clear all filters
    Exit
```

## Performance Tips

### Optimize Filter Performance

1. **Use path filters for large test suites**
   ```bash
   vendor/bin/prism test --path-filter "tests/critical/**"
   ```

2. **Combine with parallel execution**
   ```bash
   vendor/bin/prism test --filter "api" --parallel 4
   ```

3. **Use incremental mode during development**
   ```bash
   vendor/bin/prism test --incremental --watch
   ```

### Filter Precedence

Filters are applied in this order:

1. **Path filter** - Files are filtered first
2. **File-level filters** - `shouldIncludeFile()` in config
3. **Test execution** - Tests run from filtered files
4. **Name filter** - Test names filtered
5. **Tag filter** - Tests filtered by tags
6. **Exclusion filter** - Matching tests excluded

## Next Steps

- Explore [performance features](#doc-docs-performance)
- Learn about [advanced features](#doc-docs-advanced-features)
- See [output formats](#doc-docs-output-formats)

<a id="doc-docs-output-formats"></a>

Prism supports multiple output formats for test results, suitable for different use cases.

## Text Format (Default)

Beautiful, color-coded terminal output with Termwind:

```bash
vendor/bin/prism test
```

### Enhanced Terminal Output

```
┌─────────────────────────────────────────────┐
│ Test Suite: my-validator                   │
├─────────────────────────────────────────────┤
│ Total Tests: 1,247                          │
│ Passed: 1,198 ✓                             │
│ Failed: 49 ✗                                │
│ Pass Rate: 96.1%                            │
│ Duration: 45.234s                           │
└─────────────────────────────────────────────┘

✓ String validation tests (234/234)
✓ Number validation tests (187/187)
✗ Object validation tests (156/162)
✓ Array validation tests (143/143)
```

### Show Detailed Failures

Display failures with syntax-highlighted JSON diff:

```bash
vendor/bin/prism test --failures
```

```
1. additional properties should not be valid
   Test ID: draft7:properties:3:2
   File: properties.json
   Group: properties validation
   Expected Validation: INVALID
   Actual Validation: VALID

   Error: Validation should have failed

   Duration: 23.50ms

   Test Data:
      {
        "foo": 1,
        "bar": "extra"
      }
────────────────────────────────────────────────────────────────────────────────
```

**Enhanced Failure Display Features:**
- **Syntax highlighting**: JSON values color-coded by type (strings=yellow, numbers=cyan, booleans/nulls=magenta)
- **Test metadata**: ID, file, group, expected vs actual validation state
- **Error messages**: Clear description of validation failures
- **Performance metrics**: Duration in milliseconds
- **Structured layout**: Easy to scan and debug

## CI-Friendly Output

Plain text output without Termwind formatting:

```bash
vendor/bin/prism test --ci
```

### CI Output Format

```
Test Suite: my-validator
Total: 1247
Passed: 1198
Failed: 49
Pass Rate: 96.07%
Duration: 45.234s

PASS draft7:strings:0:0
PASS draft7:strings:0:1
FAIL draft7:properties:3:2
PASS draft7:numbers:1:0
```

### Use Cases

- **Continuous Integration**: GitHub Actions, GitLab CI, Jenkins
- **Logging**: Structured logs for parsing
- **Automation**: Scripts processing test results

## JSON Format

Machine-readable JSON output:

```bash
vendor/bin/prism test --format json
```

### JSON Structure

```json
{
  "suites": [
    {
      "name": "my-validator",
      "total_tests": 1247,
      "passed_tests": 1198,
      "failed_tests": 49,
      "pass_rate": 96.07,
      "duration": 45.234,
      "results": [
        {
          "id": "draft7:strings:0:0",
          "file": "strings.json",
          "group": "string validation",
          "description": "valid string",
          "data": "hello",
          "expected_valid": true,
          "actual_valid": true,
          "passed": true,
          "error": null,
          "duration": 0.012,
          "tags": ["string", "basic"]
        },
        {
          "id": "draft7:properties:3:2",
          "file": "properties.json",
          "group": "properties validation",
          "description": "additional properties invalid",
          "data": {"foo": 1, "bar": "extra"},
          "expected_valid": false,
          "actual_valid": true,
          "passed": false,
          "error": null,
          "duration": 0.023,
          "tags": ["object", "properties"]
        }
      ]
    }
  ]
}
```

### Use Cases

- **API Integration**: Send results to monitoring systems
- **Data Analysis**: Process results programmatically
- **Storage**: Archive test results in databases

### Processing JSON Output

```bash
# Save to file
vendor/bin/prism test --format json > results.json

# Parse with jq
vendor/bin/prism test --format json | jq '.suites[0].pass_rate'

# Count failures
vendor/bin/prism test --format json | jq '[.suites[].results[] | select(.passed == false)] | length'
```

## JUnit XML Format

Standards-compliant JUnit XML output for CI/CD integration:

```bash
vendor/bin/prism test --format xml
```

### XML Structure

```xml
<?xml version="1.0" encoding="UTF-8"?>
<testsuites>
  <testsuite name="my-validator" tests="1247" failures="49" time="45.234">
    <testcase
      name="draft7:strings:0:0"
      classname="string validation"
      time="0.012">
    </testcase>
    <testcase
      name="draft7:properties:3:2"
      classname="properties validation"
      time="0.023">
      <failure type="ValidationFailure">
        Expected: invalid
        Actual: valid
        Data: {"foo":1,"bar":"extra"}
      </failure>
    </testcase>
  </testsuite>
</testsuites>
```

### Use Cases

- **JUnit Integration**: Compatible with JUnit XML format
- **CI Systems**: Jenkins, Bamboo, TeamCity
- **IDE Integration**: PhpStorm, VS Code test runners

## Combining Output with Features

### JSON with Profiling

```bash
vendor/bin/prism test --format json --profile > results.json
```

### XML for CI with Failures

```bash
vendor/bin/prism test --format xml --ci --failures > junit.xml
```

## Redirecting Output

### Save to File

```bash
vendor/bin/prism test > test-results.txt
vendor/bin/prism test --format json > results.json
vendor/bin/prism test --format xml > junit.xml
```

### Pipe to Other Tools

```bash
# Filter with grep
vendor/bin/prism test --ci | grep FAIL

# Parse with jq
vendor/bin/prism test --format json | jq '.suites[].pass_rate'

# Parse XML with xmllint
vendor/bin/prism test --format xml | xmllint --format -
```

## Output Customization

### Show Only Failures

Combine format with failure filter:

```bash
vendor/bin/prism test --failures --ci
```

### Detailed JSON Output

Include failure details in JSON:

```bash
vendor/bin/prism test --format json --failures
```

### Profile with Text Output

Combine profiling with default format:

```bash
vendor/bin/prism test --profile
```

## Integration Examples

### GitHub Actions

```yaml
- name: Run Tests
  run: vendor/bin/prism test --format json --ci > results.json

- name: Upload Results
  uses: actions/upload-artifact@v3
  with:
    name: test-results
    path: results.json
```

### GitLab CI

```yaml
test:
  script:
    - vendor/bin/prism test --format junit > junit.xml
  artifacts:
    reports:
      junit: junit.xml
```

### Jenkins

```groovy
stage('Test') {
    steps {
        sh 'vendor/bin/prism test --format xml > junit.xml'
    }
    post {
        always {
            junit 'junit.xml'
        }
    }
}
```

### Custom Monitoring

```bash
#!/bin/bash
# Send results to monitoring service

vendor/bin/prism test --format json | \
  jq '{
    pass_rate: .suites[0].pass_rate,
    duration: .suites[0].duration,
    failures: .suites[0].failed_tests
  }' | \
  curl -X POST https://monitoring.example.com/api/metrics \
    -H "Content-Type: application/json" \
    -d @-
```

## Next Steps

- Learn about [custom assertions](#doc-docs-custom-assertions)
- Explore [filtering options](#doc-docs-filtering)
- See [performance features](#doc-docs-performance)

<a id="doc-docs-performance"></a>

Prism provides comprehensive performance features for fast test execution and performance analysis.

## Parallel Execution

Run tests across multiple worker processes:

```bash
vendor/bin/prism test --parallel 4
```

Or use the short flag:

```bash
vendor/bin/prism test -j 4
```

### How It Works

1. Test files divided into batches
2. Each batch runs in separate PHP process
3. Results collected and merged
4. Total execution time significantly reduced

### Optimal Worker Count

```bash
# Use CPU core count
vendor/bin/prism test -j $(nproc)

# Conservative (50% of cores)
vendor/bin/prism test -j $(($(nproc) / 2))

# Maximum performance
vendor/bin/prism test -j $(nproc --all)
```

### Performance Comparison

```bash
# Sequential execution
vendor/bin/prism test
# Time: 45.2s

# Parallel with 4 workers
vendor/bin/prism test -j 4
# Time: 12.8s (3.5x faster)

# Parallel with 8 workers
vendor/bin/prism test -j 8
# Time: 7.1s (6.4x faster)
```

## Performance Profiling

Identify slowest tests with performance profiling:

```bash
vendor/bin/prism test --profile
```

### Profile Output

```
Test Performance Profile

Slowest Tests:
  1. complex-nested-validation (2.145s)
  2. large-array-validation (1.832s)
  3. recursive-schema-test (1.456s)
  4. deep-object-validation (0.987s)
  5. pattern-matching-test (0.654s)

Total Tests: 1,247
Average Duration: 0.023s
Median Duration: 0.012s
```

### Use Cases

- **Identify bottlenecks**: Find slow tests to optimize
- **Monitor performance**: Track test suite speed over time
- **CI optimization**: Prioritize fast tests in pipelines

## Baseline Benchmarks

Save and compare performance baselines:

### Save Baseline

```bash
vendor/bin/prism test --baseline
```

Save with custom name:

```bash
vendor/bin/prism test --baseline "pre-optimization"
```

### Compare Against Baseline

```bash
vendor/bin/prism test --compare
```

Compare against named baseline:

```bash
vendor/bin/prism test --compare "pre-optimization"
```

### Benchmark Output

```
Benchmark Comparison: default

Performance Changes:
  Total Duration: 45.2s → 38.7s (14.4% faster)
  Average Test: 0.036s → 0.031s (13.9% faster)

Significant Changes:
  ✓ complex-validation: 2.145s → 1.234s (42.5% faster)
  ✓ array-processing: 1.832s → 1.123s (38.7% faster)
  ✗ string-validation: 0.234s → 0.387s (65.4% slower)

Tests Analyzed: 1,247
Improved: 892 (71.5%)
Degraded: 123 (9.9%)
Unchanged: 232 (18.6%)
```

### Workflow Example

```bash
# Save baseline before optimization
vendor/bin/prism test --baseline "before-cache"

# Make optimizations to validator
vim src/Validator.php

# Compare performance
vendor/bin/prism test --compare "before-cache"
```

## Incremental Testing

Run only changed tests for fast iteration:

```bash
vendor/bin/prism test --incremental
```

### How It Works

1. Tracks file modification times
2. Identifies changed test files
3. Only runs tests from changed files
4. Significantly faster during development

### Example Workflow

```bash
# First run - all tests execute
vendor/bin/prism test --incremental
# Time: 45.2s

# Make small change to one test file
vim tests/validation/strings.json

# Only changed file tests run
vendor/bin/prism test --incremental
# Time: 0.8s (56x faster)
```

### Cache Management

Cache stored in `.prism/incremental-cache.json`:

```json
{
  "tests/validation/strings.json": 1703001234,
  "tests/validation/numbers.json": 1703001156,
  "tests/validation/objects.json": 1703000987
}
```

Clear cache to force full run:

```bash
rm .prism/incremental-cache.json
```

## Combining Performance Features

Maximize performance by combining features:

### Development Workflow

Fast iteration with incremental mode and watch:

```bash
vendor/bin/prism test --incremental --watch
```

### CI Optimization

Parallel execution with profiling:

```bash
vendor/bin/prism test -j 8 --profile
```

### Performance Monitoring

Baseline comparison with profiling:

```bash
vendor/bin/prism test --compare "main" --profile
```

## Watch Mode

Automatically re-run tests when files change:

```bash
vendor/bin/prism test --watch
```

### How It Works

1. Runs initial test suite
2. Monitors test files for changes
3. Automatically re-runs tests on change
4. Continues until interrupted (Ctrl+C)

### Watch Output

```
[12:34:56] Initial run completed (45.2s)
[12:35:12] Watching for changes...
[12:36:08] Change detected in tests/validation/strings.json
[12:36:08] Re-running tests...
[12:36:09] Tests completed (0.8s)
[12:36:09] Watching for changes...
```

### Best Practices

Combine watch mode with other features:

```bash
# Watch with incremental testing
vendor/bin/prism test --watch --incremental

# Watch with specific filter
vendor/bin/prism test --watch --filter "string"

# Watch with failures only
vendor/bin/prism test --watch --failures
```

## Performance Best Practices

### 1. Use Parallel Execution in CI

```yaml
# .github/workflows/tests.yml
- name: Run Tests
  run: vendor/bin/prism test -j ${{ steps.cpu.outputs.count }}
```

### 2. Profile Regularly

Monitor test suite performance:

```bash
# Weekly baseline
vendor/bin/prism test --baseline "$(date +%Y-%m-%d)"

# Compare against last week
vendor/bin/prism test --compare "$(date -d '7 days ago' +%Y-%m-%d)"
```

### 3. Optimize Slow Tests

Use profiling to identify and optimize:

```bash
vendor/bin/prism test --profile | grep -A 10 "Slowest Tests"
```

### 4. Incremental During Development

Always use incremental mode when developing:

```bash
alias prism-dev="vendor/bin/prism test --incremental --watch"
```

### 5. Filter Before Profiling

Profile specific test subsets:

```bash
vendor/bin/prism test --filter "validation" --profile
```

## Performance Metrics

### Execution Time

Total time from start to finish:

```bash
vendor/bin/prism test
# Total execution time: 45.234s
```

### Test Duration

Individual test execution times:

```bash
vendor/bin/prism test --profile
# Shows per-test durations
```

### Throughput

Tests executed per second:

```bash
# 1,247 tests in 45.2s = 27.6 tests/second
```

## Next Steps

- Learn about [advanced features](#doc-docs-advanced-features)
- Explore [output formats](#doc-docs-output-formats)
- See [custom assertions](#doc-docs-custom-assertions)
