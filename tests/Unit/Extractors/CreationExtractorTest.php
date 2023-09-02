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

use LaravelJsonApi\Core\Document\Input\Values\ResourceObject;
use LaravelJsonApi\Core\Extensions\Atomic\Operations\Create;
use LaravelJsonApi\Core\Values\ResourceType;
use LaravelJsonApi\Validation\Extractors\CreationExtractor;
use PHPUnit\Framework\TestCase;

class CreationExtractorTest extends TestCase
{
    /**
     * @return void
     */
    public function test(): void
    {
        $in = [
            'type' => 'posts',
            'attributes' => [
                'title' => 'Hello World',
                'content' => '...',
                'publishedAt' => null,
            ],
            'relationships' => [
                'author' => [
                    'data' => [
                        'type' => 'users',
                        'id' => '123',
                    ],
                ],
                'tags' => [
                    'data' => [
                        [
                            'type' => 'tags',
                            'id' => '456',
                        ],
                        [
                            'type' => 'tags',
                            'id' => '789',
                        ],
                    ],
                ],
            ],
        ];

        $expected = [
            'author' => [
                'type' => 'users',
                'id' => '123',
            ],
            'content' => '...',
            'id' => null,
            'publishedAt' => null,
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
            'title' => 'Hello World',
            'type' => 'posts',
        ];

        $operation = new Create(null, new ResourceObject(
            type: new ResourceType($in['type']),
            attributes: $in['attributes'],
            relationships: $in['relationships'],
        ));

        $extractor = new CreationExtractor();
        $this->assertSame($expected, $extractor->extract($operation));
    }
}
