<?php

declare(strict_types=1);

namespace Hydra\Validation\Rules;

use Hydra\Validation\Contracts\RuleInterface;

/**
 * The value, as a string, must be at most $max characters long (multibyte-aware).
 *
 * An absent value (null) has length zero and passes — pair with {@see Required}
 * when presence is also required. The length check is on characters, not bytes.
 */
final class MaxLength implements RuleInterface
{
    private readonly string $message;

    public function __construct(private readonly int $max, ?string $message = null)
    {
        $this->message = $message ?? "Must be {$max} characters or fewer.";
    }

    public function validate(mixed $value): ?string
    {
        return mb_strlen((string) $value) > $this->max ? $this->message : null;
    }
}
