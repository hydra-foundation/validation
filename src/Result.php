<?php

declare(strict_types=1);

namespace Hydra\Validation;

/**
 * The outcome of validating a set of fields.
 *
 * Immutable. Carries at most one message per field — the first rule that failed
 * for that field, since the validator short-circuits. {@see errors()} returns
 * the field => message map directly, which is the shape view models consume.
 */
final readonly class Result
{
    /**
     * @param array<string, string> $errors field => first error message
     */
    public function __construct(private array $errors = [])
    {
    }

    public function passes(): bool
    {
        return $this->errors === [];
    }

    public function fails(): bool
    {
        return $this->errors !== [];
    }

    /**
     * @return array<string, string> field => first error message
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * The error message for a single field, or null if that field passed.
     */
    public function first(string $field): ?string
    {
        return $this->errors[$field] ?? null;
    }
}
