<?php

declare(strict_types=1);

namespace Hydra\Validation;

/**
 * The outcome of validating a set of fields.
 *
 * Immutable. Carries at most one message per field — the first rule that failed
 * for that field, since the validator short-circuits. {@see errors()} returns
 * the field => message map directly, which is the shape view models consume.
 *
 * On a passing result, {@see validated()} returns the vetted subset of the
 * input, so controllers consume that instead of reaching back into the raw
 * request.
 */
final readonly class Result
{
    /**
     * @param array<string, string> $errors    field => first error message
     * @param array<string, mixed>  $validated field => input value, only for
     *                                         fields listed in the rules AND
     *                                         present in the input AND free of
     *                                         errors
     */
    public function __construct(
        private array $errors = [],
        private array $validated = [],
    ) {
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

    /**
     * The vetted subset of the input — exactly the fields that were listed in
     * the rules AND present in the input, nothing else. Consume this instead
     * of reaching back into the raw request:
     *
     *   - Input keys that had no rules never appear, no matter what the client
     *     sent — only what was vetted comes out.
     *   - A field listed in the rules but absent from the input does not
     *     appear either: absent stays absent, it is not invented as null.
     *     Reach for `$result->validated()['field'] ?? $default` when a field
     *     is optional.
     *   - Listing a field with an empty rule list is still an explicit opt-in,
     *     so such a field appears when present in the input.
     *
     * Asking a FAILED result for validated data is a programming error — there
     * is no safe subset to hand out — so this throws rather than returning
     * partial data. Check {@see passes()} first.
     *
     * @return array<string, mixed> field => input value
     *
     * @throws \LogicException when the result failed
     */
    public function validated(): array
    {
        if ($this->errors !== []) {
            throw new \LogicException(
                'Cannot read validated data from a failed validation result; check passes() first.',
            );
        }

        return $this->validated;
    }
}
