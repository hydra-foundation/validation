<?php

declare(strict_types=1);

namespace Hydra\Validation\Rules;

use Hydra\Validation\Contracts\RuleInterface;
use InvalidArgumentException;

/**
 * The value, as a string, must match a PCRE pattern.
 *
 * The general-purpose escape hatch: anything the shipped rules don't cover
 * (email, slug, postal code) is a regex away, without this package growing a
 * rule for every format. The pattern is passed verbatim to preg_match,
 * delimiters and all.
 *
 * A malformed pattern is a developer error, not a validation failure, so it is
 * rejected at construction (fail-fast) rather than silently reporting every
 * value as invalid at match time — `preg_match` returns false, not 0, on a bad
 * pattern, and that false would otherwise be flattened into a misleading
 * "invalid value" plus a runtime warning.
 */
final class Pattern implements RuleInterface
{
    public function __construct(
        private readonly string $pattern,
        private readonly string $message = 'This value is not in the expected format.',
    ) {
        if (@preg_match($pattern, '') === false) {
            throw new InvalidArgumentException("Invalid validation pattern: {$pattern}");
        }
    }

    public function validate(mixed $value): ?string
    {
        return preg_match($this->pattern, (string) $value) === 1 ? null : $this->message;
    }
}
