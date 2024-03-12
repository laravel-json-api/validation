<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
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
