<?php

declare(strict_types=1);

namespace Hydra\Validation\Rules;

use Hydra\Validation\Contracts\RuleInterface;

/**
 * The value, as a string, must be at least $min characters long (multibyte-aware).
 *
 * An absent value (null) has length zero and fails for any positive $min;
 * combine with {@see Required} when you want a single "missing" message instead.
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
        return mb_strlen((string) $value) < $this->min ? $this->message : null;
    }
}
