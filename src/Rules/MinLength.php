<?php

declare(strict_types=1);

namespace Hydra\Validation\Rules;

use Hydra\Validation\Contracts\RuleInterface;
use Stringable;

/**
 * The value, as a string, must be at least $min characters long (multibyte-aware).
 *
 * An absent value (null) has length zero and fails for any positive $min;
 * combine with {@see Required} when you want a single "missing" message instead.
 *
 * A value that is not text-shaped (array, bool, non-Stringable object) is
 * client input in the wrong shape — e.g. `field[]=x` arrives as an array — so
 * it fails validation with the rule's normal message rather than being cast
 * (which warns on arrays and fatals on objects) or throwing.
 */
final class MinLength implements RuleInterface
{
    private readonly string $message;

    public function __construct(private readonly int $min, ?string $message = null)
    {
        $this->message = $message ?? "Must be at least {$min} characters.";
    }

    public function validate(mixed $value): ?string
    {
        if (!$this->isTextual($value)) {
            return $this->message;
        }

        return mb_strlen((string) $value) < $this->min ? $this->message : null;
    }

    /**
     * Whether the value can be safely treated as text. Booleans are excluded
     * deliberately: real text input never arrives as a bool, and "1"/"" from
     * a cast would be misleading lengths rather than what the client sent.
     */
    private function isTextual(mixed $value): bool
    {
        return $value === null
            || is_string($value)
            || is_int($value)
            || is_float($value)
            || $value instanceof Stringable;
    }
}
