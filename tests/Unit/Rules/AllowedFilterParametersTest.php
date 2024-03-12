<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

declare(strict_types=1);

namespace LaravelJsonApi\Validation\Tests\Unit\Rules;

use LaravelJsonApi\Contracts\Schema\Filter;
use LaravelJsonApi\Contracts\Schema\Schema;
use LaravelJsonApi\Validation\Rules\AllowedFilterParameters;
use PHPUnit\Framework\TestCase;

class AllowedFilterParametersTest extends TestCase
{

    public function test(): void
    {
        $rule = new AllowedFilterParameters(['foo', 'bar']);

        $this->assertTrue($rule->passes('filter', ['foo' => 'foobar', 'bar' => 'bazbat']));
        $this->assertFalse($rule->passes('filter', ['foo' => 'foobar', 'baz' => 'bazbat']));
    }

    public function testAllowAndForget(): void
    {
        $rule = (new AllowedFilterParameters(['id', 'foobar', 'bazbat']))
            ->allow('foo', 'bar')
            ->forget('id');

        $this->assertTrue($rule->passes('filter', [
            'foo' => 'foobar',
            'bar' => 'bazbat',
            'foobar' => 'blah',
            'bazbat' => 'blah',
        ]));
        $this->assertFalse($rule->passes('filter', ['foo' => 'foobar', 'baz' => 'bazbat']));
        $this->assertFalse($rule->passes('filter', ['id' => '1']));
    }

    public function testForgetAndAllow(): void
    {
        $rule = (new AllowedFilterParameters(['id', 'foobar', 'bazbat']))
            ->forget('id')
            ->allow('foo', 'bar');

        $this->assertTrue($rule->passes('filter', [
            'foo' => 'foobar',
            'bar' => 'bazbat',
            'foobar' => 'blah',
            'bazbat' => 'blah',
        ]));
        $this->assertFalse($rule->passes('filter', ['foo' => 'foobar', 'baz' => 'bazbat']));
        $this->assertFalse($rule->passes('filter', ['id' => '1']));
    }

    public function testSchema(): void
    {
        $schema = $this->createMock(Schema::class);
        $schema->method('filters')->willReturn([
            $a = $this->createMock(Filter::class),
            $b = $this->createMock(Filter::class),
        ]);

        $a->method('key')->willReturn('slug');
        $b->method('key')->willReturn('title');

        $this->assertEquals(
            new AllowedFilterParameters(['slug', 'title']),
            AllowedFilterParameters::make($schema)
        );
    }

}
