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

use LaravelJsonApi\Validation\Extractors\DeletionExtractor;
use LaravelJsonApi\Validation\Extractors\UpdateExtractor;
use LaravelJsonApi\Validation\ValidatedSchema;
use PHPUnit\Framework\TestCase;

class DeletionExtractorTest extends TestCase
{
    /**
     * @return void
     */
    public function test(): void
    {
        $resource = [
            'type' => 'posts',
            'id' => '123',
            'attributes' => [
                'title' => 'Hello World',
                'content' => '...',
            ],
            'relationships' => [
                'tags' => [
                    'data' => [
                        ['type' => 'tags', 'id' => '1'],
                        ['type' => 'tags', 'id' => '2'],
                    ],
                ],
            ],
            'meta' => [
                'foo' => 'bar',
                'foobar' => 'blah!',
            ],
        ];

        $model = new \stdClass();
        $extractor = new DeletionExtractor(
            $schema = $this->createMock(ValidatedSchema::class),
            $updateExtractor = $this->createMock(UpdateExtractor::class),
        );

        $updateExtractor
            ->expects($this->once())
            ->method('existing')
            ->with($this->identicalTo($model))
            ->willReturn($resource);

        $schema
            ->expects($this->once())
            ->method('metaForDeletion')
            ->with($this->identicalTo($model))
            ->willReturn(['baz' => 'bat', 'foobar' => 'bazbat']);

        $expected = [
            'content' => '...',
            'id' => '123',
            'meta' => [
                'baz' => 'bat',
                'foo' => 'bar',
                'foobar' => 'bazbat',
            ],
            'tags' => [
                ['type' => 'tags', 'id' => '1'],
                ['type' => 'tags', 'id' => '2'],
            ],
            'title' => 'Hello World',
            'type' => 'posts',
        ];

        $this->assertSame($expected, $extractor->extract($model));
    }
}
