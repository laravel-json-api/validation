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
use LaravelJsonApi\Validation\Rules\HasMany;
use PHPUnit\Framework\TestCase;

class HasManyTest extends TestCase
{

    /**
     * @return array
     */
    public static function validProvider(): array
    {
        return [
            'empty' => [
                'users',
                [],
            ],
            'identifier' => [
                'users',
                [
                    ['type' => 'users', 'id' => '123'],
                ],
            ],
            'identifiers' => [
                'users',
                [
                    ['type' => 'users', 'id' => '123'],
                    ['type' => 'users', 'id' => '456'],
                ],
            ],
            'polymorph identifier' => [
                ['users', 'people'],
                [
                    ['type' => 'people', 'id' => '123'],
                ],
            ],
            'polymorph identifiers' => [
                ['users', 'people'],
                [
                    ['type' => 'people', 'id' => '123'],
                    ['type' => 'users', 'id' => '456'],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public static function invalidProvider(): array
    {
        return [
            'has-one null' => [
                'users',
                ['data' => null],
            ],
            'has-one identifier' => [
                'users',
                ['data' => ['type' => 'users', 'id' => '123']],
            ],
            'identifiers' => [
                'users',
                [
                    ['type' => 'users', 'id' => '123'],
                    ['type' => 'people', 'id' => '456'],
                ],
            ],
            'polymorph identifiers' => [
                ['users', 'people'],
                [
                    ['type' => 'people', 'id' => '123'],
                    ['type' => 'users', 'id' => '456'],
                    ['type' => 'foobars', 'id' => '789'],
                ],
            ],
        ];
    }

    /**
     * @param $types
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
        $schema->method('relationship')->with('authors')->willReturn($relation);

        $rule = new HasMany($schema);

        $this->assertTrue($rule->passes('authors', $value));
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
        $schema->method('relationship')->with('authors')->willReturn($relation);

        $rule = new HasMany($schema);

        $this->assertFalse($rule->passes('authors', $value));
    }

}
