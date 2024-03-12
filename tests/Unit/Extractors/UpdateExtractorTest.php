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

use Illuminate\Http\Request;
use LaravelJsonApi\Contracts\Encoder\Encoder;
use LaravelJsonApi\Contracts\Encoder\JsonApiDocument;
use LaravelJsonApi\Core\Document\Input\Values\ResourceObject;
use LaravelJsonApi\Core\Extensions\Atomic\Operations\Update;
use LaravelJsonApi\Core\Query\IncludePaths;
use LaravelJsonApi\Core\Values\ResourceId;
use LaravelJsonApi\Core\Values\ResourceType;
use LaravelJsonApi\Validation\Extractors\UpdateExtractor;
use LaravelJsonApi\Validation\ValidatedSchema;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UpdateExtractorTest extends TestCase
{
    /**
     * @var MockObject&ValidatedSchema
     */
    private ValidatedSchema&MockObject $schema;

    /**
     * @var MockObject&Encoder
     */
    private Encoder&MockObject $encoder;

    /**
     * @var Request
     */
    private Request $request;

    /**
     * @var UpdateExtractor
     */
    private UpdateExtractor $extractor;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->extractor = new UpdateExtractor(
            $this->schema = $this->createMock(ValidatedSchema::class),
            $this->encoder = $this->createMock(Encoder::class),
            $this->request = $this->createMock(Request::class),
        );
    }

    /**
     * @return void
     */
    public function testItExtractsWithoutModifyingExisting(): void
    {
        $model = new \stdClass();
        $resource = [
            'type' => 'posts',
            'id' => '123',
            'attributes' => [
                'title' => 'Hello World',
            ],
            'relationships' => [
                'author' => [
                    'data' => [
                        'type' => 'users',
                        'id' => '456',
                    ],
                ],
            ],
        ];

        $expected = [
            'author' => [
                'type' => 'users',
                'id' => '456',
            ],
            'id' => '123',
            'title' => 'Hello World',
            'type' => 'posts',
        ];

        $operation = new Update(null, new ResourceObject(
            type: new ResourceType($resource['type']),
            id: new ResourceId($resource['id']),
            attributes: $resource['attributes'],
            relationships: $resource['relationships'],
        ));

        $this->willEncode($model, $resource);
        $this->withExisting($model, $resource, null);

        $this->assertSame(
            $expected,
            $this->extractor->extract($operation, $model)
        );
    }

    /**
     * @return void
     */
    public function testItExtractsWithModifyingExisting(): void
    {
        $model = new \stdClass();
        $resource = [
            'type' => 'posts',
            'id' => '123',
            'attributes' => [
                'title' => 'Hello World',
            ],
            'relationships' => [
                'author' => [
                    'data' => [
                        'type' => 'users',
                        'id' => '456',
                    ],
                ],
            ],
        ];

        $modified = $resource;
        $modified['attributes']['content'] = '...';

        $expected = [
            'author' => [
                'type' => 'users',
                'id' => '456',
            ],
            'content' => '...',
            'id' => '123',
            'title' => 'Hello World',
            'type' => 'posts',
        ];

        $operation = new Update(null, new ResourceObject(
            type: new ResourceType($resource['type']),
            id: new ResourceId($resource['id']),
            attributes: $resource['attributes'],
            relationships: $resource['relationships'],
        ));

        $this->willEncode($model, $resource);
        $this->withExisting($model, $resource, $modified);

        $this->assertSame(
            $expected,
            $this->extractor->extract($operation, $model)
        );
    }

    /**
     * @param object $model
     * @param array $existing
     * @return void
     */
    private function willEncode(object $model, array $existing): void
    {
        $this->schema
            ->expects($this->once())
            ->method('includePaths')
            ->willReturn($includePaths = new IncludePaths());

        $this->encoder
            ->expects($this->once())
            ->method('withRequest')
            ->with($this->identicalTo($this->request))
            ->willReturnSelf();

        $this->encoder
            ->expects($this->once())
            ->method('withIncludePaths')
            ->with($this->identicalTo($includePaths))
            ->willReturnSelf();

        $this->encoder
            ->expects($this->once())
            ->method('withResource')
            ->with($this->identicalTo($model))
            ->willReturn($document = $this->createMock(JsonApiDocument::class));

        $document
            ->expects($this->once())
            ->method('toArray')
            ->willReturn(['data' => $existing]);
    }

    /**
     * @param object $model
     * @param array $resource
     * @param array|null $result
     * @return void
     */
    private function withExisting(object $model, array $resource, ?array $result): void
    {
        $this->schema
            ->expects($this->once())
            ->method('withExisting')
            ->with($this->identicalTo($model), $this->identicalTo($resource))
            ->willReturn($result);
    }
}
