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

use LaravelJsonApi\Contracts\Schema\PolymorphicRelation;
use LaravelJsonApi\Contracts\Schema\Relation;
use LaravelJsonApi\Contracts\Schema\Schema;
use LaravelJsonApi\Validation\Rules\HasOne;
use PHPUnit\Framework\TestCase;

class HasOneTest extends TestCase
{

    /**
     * @return array
     */
    public static function validProvider(): array
    {
        return [
            'null' => [
                'users',
                null,
            ],
            'identifier' => [
                'users',
                ['type' => 'users', 'id' => '123'],
            ],
            'polymorph null' => [
                ['users', 'people'],
                null,
            ],
            'polymorph identifier 1' => [
                ['users', 'people'],
                ['type' => 'users', 'id' => '123'],
            ],
            'polymorph identifier 2' => [
                ['users', 'people'],
                ['type' => 'people', 'id' => '456'],
            ],
        ];
    }

    /**
     * @return array
     */
    public static function invalidProvider(): array
    {
        return [
            'empty has-many' => [
                'users',
                [],
            ],
            'has-many' => [
                'users',
                [
                    ['type' => 'users', 'id' => '123'],
                ],
            ],
            'invalid type' => [
                'users',
                ['type' => 'people', 'id' => '456'],
            ],
            'invalid polymorph type' => [
                ['users', 'people'],
                ['type' => 'foobar', 'id' => '1'],
            ],
        ];
    }

    /**
     * @param string|array $types
     * @param $value
     * @dataProvider validProvider
     */
    public function testValid($types, $value): void
    {
        if (is_array($types)) {
            $relation = $this->createMock(PolymorphicRelation::class);
            $relation->method('inverseTypes')->willReturn($types);
        } else {
            $relation = $this->createMock(Relation::class);
            $relation->method('inverse')->willReturn($types);
        }

        $schema = $this->createMock(Schema::class);
        $schema->method('relationship')->with('author')->willReturn($relation);

        $rule = new HasOne($schema);

        $this->assertTrue($rule->passes('author', $value));
    }

    /**
     * @param $types
     * @param $value
     * @dataProvider invalidProvider
     */
    public function testInvalid($types, $value): void
    {
        if (is_array($types)) {
            $relation = $this->createMock(PolymorphicRelation::class);
            $relation->method('inverseTypes')->willReturn($types);
        } else {
            $relation = $this->createMock(Relation::class);
            $relation->method('inverse')->willReturn($types);
        }

        $schema = $this->createMock(Schema::class);
        $schema->method('relationship')->with('author')->willReturn($relation);

        $rule = new HasOne($schema);

        $this->assertFalse($rule->passes('author', $value));
    }
}
