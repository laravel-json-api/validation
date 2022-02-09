<?php
/*
 * Copyright 2022 Cloud Creativity Limited
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
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
    public function validProvider(): array
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
    public function invalidProvider(): array
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
