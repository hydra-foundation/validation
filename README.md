# Hydra Validation

Validates a set of input values against a per-field list of rules. Stateless —
the rules travel with each call, so one `Validator` can be shared and autowired.
Ships **no** `ServiceProvider`; the validator has no dependencies to bind.

## Usage

For each field the rules run in order and the validator short-circuits on the
first failure — there's no point computing messages a single-message-per-field
UI (an htmx form) will never show.

```php
$result = $validator->validate($data, [
    'email' => [new Required, new Pattern('/\A.+@.+\z/')],
    'name'  => [new Required, new MaxLength(120)],
]);

if ($result->fails()) {
    $errors = $result->errors();      // ['email' => 'first message', ...]
    $first  = $result->first('email'); // or null if it passed
}
```

`Result` carries at most one message per field (the first rule that failed),
which is the shape view models consume.

## Consuming validated input

On a passing result, `validated()` returns the vetted subset of the input, so
a controller consumes that instead of reaching back into the raw request:

```php
$result = $validator->validate($input->all(), [
    'username' => [new Required, new MaxLength(64)],
    'bio'      => [new MaxLength(280)], // optional: no Required
]);

if ($result->fails()) {
    return $this->render('form', ['errors' => $result->errors()], Status::UnprocessableEntity);
}

$valid = $result->validated();          // ONLY the ruled, present, passing fields
$this->users->create($valid['username'], $valid['bio'] ?? '');
```

The semantics are deliberate and strict:

- **Failed result → `LogicException`.** Asking a failed result for validated
  data is a programming error — there is no safe subset to hand out — so it
  fails loud instead of returning partial data. Check `passes()` first.
- **Unruled input keys never appear.** Whatever extra fields the client posts,
  only what was vetted comes out — that's the point.
- **Absent stays absent.** A field listed in the rules but missing from the
  input is omitted, not invented as `null`; use `?? $default` for optional
  fields. (Listing a field with an empty rule list is still an explicit
  opt-in, so such a field appears when present.)

Note the `\A`/`\z` anchors in the pattern: with `^`/`$`, PCRE's `$` tolerates a
trailing newline (so `"a@b\n"` would pass), whereas `\z` anchors at the true
end of the string.

## Custom rules

`RuleInterface` is the package's one extension point: a rule inspects one value
and returns an error message when invalid, or `null` when it passes. The message
is the rule's own — there's no separate message subsystem. App-specific rules
(e.g. "unique in the database") are application policy and live in the app.

```php
final class Unique implements RuleInterface
{
    public function __construct(private UserRepository $users) {}

    public function validate(mixed $value): ?string
    {
        return $this->users->existsByEmail((string) $value) ? 'Already taken.' : null;
    }
}
```
