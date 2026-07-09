<?php

declare(strict_types=1);

namespace Hydra\Validation;

/**
 * Validates a set of input values against a per-field list of rules.
 *
 * Stateless: the rules travel with each call, not the instance, so a single
 * Validator can be shared (and autowired) across an application. For each field
 * the rules run in order and the validator short-circuits on the first failure
 * — there is no point computing messages that a single-message-per-field UI
 * (an htmx form, say) will never show.
 */
final class Validator
{
    /**
     * @param array<string, mixed>          $data  field => submitted value
     * @param array<string, iterable<RuleInterface>> $rules field => rules to apply
     */
    public function validate(array $data, array $rules): Result
    {
        $errors = [];
        $validated = [];

        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? null;

            foreach ($fieldRules as $rule) {
                $message = $rule->validate($value);

                if ($message !== null) {
                    $errors[$field] = $message;
                    break; // first failure wins; move to the next field
                }
            }

            // A field enters the validated subset only when it was declared in
            // the rules (this loop), actually present in the input (absent stays
            // absent — never invented as null), and produced no error.
            if (!isset($errors[$field]) && array_key_exists($field, $data)) {
                $validated[$field] = $data[$field];
            }
        }

        return new Result($errors, $validated);
    }
}
