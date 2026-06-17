<?php

declare(strict_types=1);

namespace Hydra\Validation\Tests\Unit;

use Hydra\Validation\Result;
use Hydra\Validation\Contracts\RuleInterface;
use Hydra\Validation\Rules\MaxLength;
use Hydra\Validation\Rules\Required;
use Hydra\Validation\Validator;
use PHPUnit\Framework\TestCase;

final class ValidatorTest extends TestCase
{
    private Validator $validator;

    protected function setUp(): void
    {
        $this->validator = new Validator;
    }

    public function testPassesWhenEveryRulePasses(): void
    {
        $result = $this->validator->validate(
            ['name' => 'Ada'],
            ['name' => [new Required, new MaxLength(50)]],
        );

        $this->assertInstanceOf(Result::class, $result);
        $this->assertTrue($result->passes());
        $this->assertFalse($result->fails());
        $this->assertSame([], $result->errors());
        $this->assertNull($result->first('name'));
    }

    public function testCollectsOneErrorPerField(): void
    {
        $result = $this->validator->validate(
            ['name' => '', 'body' => str_repeat('x', 300)],
            [
                'name' => [new Required('Name is required.')],
                'body' => [new MaxLength(280, 'Too long.')],
            ],
        );

        $this->assertTrue($result->fails());
        $this->assertSame(
            ['name' => 'Name is required.', 'body' => 'Too long.'],
            $result->errors(),
        );
        $this->assertSame('Name is required.', $result->first('name'));
    }

    public function testShortCircuitsToFirstFailingRulePerField(): void
    {
        // Required fails first, so MaxLength must never run.
        $exploding = new class implements RuleInterface {
            public function validate(mixed $value): ?string
            {
                throw new \RuntimeException('second rule should not run');
            }
        };

        $result = $this->validator->validate(
            ['name' => ''],
            ['name' => [new Required('required'), $exploding]],
        );

        $this->assertSame('required', $result->first('name'));
    }

    public function testAbsentFieldIsPassedToRulesAsNull(): void
    {
        // 'name' is not present in the data at all.
        $result = $this->validator->validate(
            [],
            ['name' => [new Required('required')]],
        );

        $this->assertSame('required', $result->first('name'));
    }

    public function testFieldWithNoRulesIsIgnored(): void
    {
        $result = $this->validator->validate(['name' => ''], ['name' => []]);

        $this->assertTrue($result->passes());
    }
}
