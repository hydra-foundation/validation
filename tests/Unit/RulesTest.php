<?php

declare(strict_types=1);

namespace Hydra\Validation\Tests\Unit;

use Hydra\Validation\Rules\MaxLength;
use Hydra\Validation\Rules\MinLength;
use Hydra\Validation\Rules\Pattern;
use Hydra\Validation\Rules\Required;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class RulesTest extends TestCase
{
    public function testRequiredFailsOnNullEmptyStringAndEmptyArray(): void
    {
        $rule = new Required;

        $this->assertSame('This field is required.', $rule->validate(null));
        $this->assertSame('This field is required.', $rule->validate(''));
        $this->assertSame('This field is required.', $rule->validate([]));
    }

    public function testRequiredAllowsFalsyButPresentValues(): void
    {
        // Regression guard: "0", 0, and false are present and must pass.
        $rule = new Required;

        $this->assertNull($rule->validate('0'));
        $this->assertNull($rule->validate(0));
        $this->assertNull($rule->validate(false));
        $this->assertNull($rule->validate('hello'));
    }

    public function testRequiredAllowsNonEmptyArray(): void
    {
        // Only the empty array is "missing"; a populated array is present.
        $rule = new Required;

        $this->assertNull($rule->validate(['a']));
        $this->assertNull($rule->validate([0]));
    }

    public function testRequiredCarriesItsOwnMessage(): void
    {
        $this->assertSame('Write something first.', (new Required('Write something first.'))->validate(''));
    }

    public function testMaxLengthIsMultibyteAware(): void
    {
        $rule = new MaxLength(3);

        $this->assertNull($rule->validate('abc'));
        $this->assertNull($rule->validate('héé')); // 3 characters, more than 3 bytes
        $this->assertSame('Must be 3 characters or fewer.', $rule->validate('abcd'));
    }

    public function testMaxLengthPassesAbsentValue(): void
    {
        // null is length zero; presence is Required's job, not MaxLength's.
        $this->assertNull((new MaxLength(10))->validate(null));
    }

    public function testMinLengthIsMultibyteAware(): void
    {
        $rule = new MinLength(3);

        $this->assertNull($rule->validate('abc'));
        $this->assertSame('Must be at least 3 characters.', $rule->validate('ab'));
        $this->assertSame('Must be at least 3 characters.', $rule->validate(''));
    }

    public function testMinLengthFailsAbsentValue(): void
    {
        // Deliberate asymmetry with MaxLength: null is length zero, so for any
        // positive minimum it fails rather than passing.
        $this->assertSame('Must be at least 3 characters.', (new MinLength(3))->validate(null));
    }

    public function testCustomMessagesOverrideDefaults(): void
    {
        $this->assertSame('too long', (new MaxLength(2, 'too long'))->validate('abc'));
        $this->assertSame('too short', (new MinLength(5, 'too short'))->validate('ab'));
    }

    public function testPatternMatches(): void
    {
        $rule = new Pattern('/^[a-z]+$/', 'letters only');

        $this->assertNull($rule->validate('abc'));
        $this->assertSame('letters only', $rule->validate('abc123'));
    }

    public function testPatternUsesDefaultMessage(): void
    {
        $this->assertSame(
            'This value is not in the expected format.',
            (new Pattern('/^[a-z]+$/'))->validate('123'),
        );
    }

    public function testPatternCoercesNonStringValues(): void
    {
        // The (string) cast is load-bearing: numbers stringify, null becomes ''.
        $digits = new Pattern('/^\d+$/');

        $this->assertNull($digits->validate(123));
        $this->assertSame('This value is not in the expected format.', $digits->validate(null));
    }

    public function testPatternRejectsMalformedRegexAtConstruction(): void
    {
        // A bad pattern is a developer error, surfaced immediately — not a
        // silent per-value "invalid" plus a runtime warning at match time.
        $this->expectException(InvalidArgumentException::class);

        new Pattern('/[unclosed');
    }
}
