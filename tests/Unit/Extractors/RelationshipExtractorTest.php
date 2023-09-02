<?php
/*
 * Copyright 2023 Cloud Creativity Limited
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

namespace LaravelJsonApi\Validation\Tests\Unit\Extractors;

use LaravelJsonApi\Core\Document\Input\Values\ListOfResourceIdentifiers;
use LaravelJsonApi\Core\Document\Input\Values\ResourceIdentifier;
use LaravelJsonApi\Core\Extensions\Atomic\Operations\UpdateToMany;
use LaravelJsonApi\Core\Extensions\Atomic\Operations\UpdateToOne;
use LaravelJsonApi\Core\Extensions\Atomic\Values\OpCodeEnum;
use LaravelJsonApi\Core\Extensions\Atomic\Values\Ref;
use LaravelJsonApi\Core\Values\ResourceId;
use LaravelJsonApi\Core\Values\ResourceType;
use LaravelJsonApi\Validation\Extractors\RelationshipExtractor;
use PHPUnit\Framework\TestCase;

class RelationshipExtractorTest extends TestCase
{
    /**
     * @var RelationshipExtractor
     */
    private RelationshipExtractor $extractor;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->extractor = new RelationshipExtractor();
    }

    /**
     * @return void
     */
    public function testItExtractsToOneWithNull(): void
    {
        $op = new UpdateToOne(new Ref(
            type: new ResourceType('posts'),
            id: new ResourceId('123'),
            relationship: 'author',
        ), null);

        $expected = [
            'author' => null,
            'id' => '123',
            'type' => 'posts',
        ];

        $this->assertSame($expected, $this->extractor->extract($op));
    }

    /**
     * @return void
     */
    public function testItExtractsToOneWithIdentifier(): void
    {
        $op = new UpdateToOne(new Ref(
            type: new ResourceType('posts'),
            id: new ResourceId('123'),
            relationship: 'author',
        ), new ResourceIdentifier(new ResourceType('users'), new ResourceId('456')));

        $expected = [
            'author' => [
                'type' => 'users',
                'id' => '456',
            ],
            'id' => '123',
            'type' => 'posts',
        ];

        $this->assertSame($expected, $this->extractor->extract($op));
    }

    /**
     * @return void
     */
    public function testItExtractsToMany(): void
    {
        $op = new UpdateToMany(
            OpCodeEnum::Add,
            new Ref(
                type: new ResourceType('posts'),
                id: new ResourceId('123'),
                relationship: 'tags',
            ),
            new ListOfResourceIdentifiers(
                new ResourceIdentifier(new ResourceType('tags'), new ResourceId('456')),
                new ResourceIdentifier(new ResourceType('tags'), new ResourceId('789')),
            )
        );

        $expected = [
            'id' => '123',
            'tags' => [
                [
                    'type' => 'tags',
                    'id' => '456',
                ],
                [
                    'type' => 'tags',
                    'id' => '789',
                ],
            ],
            'type' => 'posts',
        ];

        $this->assertSame($expected, $this->extractor->extract($op));
    }
}
