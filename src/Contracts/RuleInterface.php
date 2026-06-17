<?php

declare(strict_types=1);

namespace Hydra\Validation\Contracts;

/**
 * A single validation rule.
 *
 * A rule inspects one value and returns an error message when the value is
 * invalid, or `null` when it passes. The message is the rule's own — phrasing
 * is carried by the rule, not resolved through a separate message subsystem.
 *
 * This is the package's one extension point: an application supplies its own
 * rules (e.g. a "unique in the database" check) by implementing this interface.
 * Such rules are application policy and live in the app, not this package.
 */
interface RuleInterface
{
    /**
     * @return string|null an error message if the value is invalid, otherwise null
     */
    public function validate(mixed $value): ?string;
}
