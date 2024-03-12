<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

declare(strict_types=1);

namespace LaravelJsonApi\Validation\Tests\Unit;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Request;
use LaravelJsonApi\Contracts\Schema\Relation;
use LaravelJsonApi\Contracts\Schema\Schema;
use LaravelJsonApi\Core\Document\Input\Values\ListOfResourceIdentifiers;
use LaravelJsonApi\Core\Document\Input\Values\ResourceObject;
use LaravelJsonApi\Core\Extensions\Atomic\Operations\Create;
use LaravelJsonApi\Core\Extensions\Atomic\Operations\Delete;
use LaravelJsonApi\Core\Extensions\Atomic\Operations\Update;
use LaravelJsonApi\Core\Extensions\Atomic\Operations\UpdateToMany;
use LaravelJsonApi\Core\Extensions\Atomic\Operations\UpdateToOne;
use LaravelJsonApi\Core\Extensions\Atomic\Values\OpCodeEnum;
use LaravelJsonApi\Core\Extensions\Atomic\Values\Ref;
use LaravelJsonApi\Core\Values\ResourceId;
use LaravelJsonApi\Core\Values\ResourceType;
use LaravelJsonApi\Validation\ValidatedSchema;
use PHPUnit\Framework\TestCase;

class ValidatedSchemaTest extends TestCase
{
    /**
     * @var Request
     */
    private Request $request;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->request = $this->createMock(Request::class);
    }

    /**
     * @return void
     */
    public function testItHasFields(): void
    {
        $schema = $this->createMock(Schema::class);
        $schema->expects($this->once())->method('attributes')->willReturn([
            'foo' => 'bar',
            'baz' => 'bat',
        ]);
        $schema->expects($this->once())->method('relationships')->willReturn([
            'foobar' => 'barfoo',
            'bazbat' => 'batbaz',
        ]);

        $validatedSchema = new ValidatedSchema($schema, $this->request);

        $this->assertSame([
            'foo' => 'bar',
            'baz' => 'bat',
            'foobar' => 'barfoo',
            'bazbat' => 'batbaz',
        ], iterator_to_array($validatedSchema->fields()));
    }

    /**
     * @return void
     */
    public function testItReturnsRelationship(): void
    {
        $schema = $this->createMock(Schema::class);
        $schema
            ->expects($this->once())
            ->method('relationship')
            ->with('foo')
            ->willReturn($relation = $this->createMock(Relation::class));

        $validatedSchema = new ValidatedSchema($schema, $this->request);

        $this->assertSame($relation, $validatedSchema->relationship('foo'));
    }

    /**
     * @return void
     */
    public function testItReturnsIncludePaths(): void
    {
        $schema = $this->createMock(Schema::class);
        $schema
            ->expects($this->once())
            ->method('relationships')
            ->willReturn([
                'foo' => $foo = $this->createMock(Relation::class),
                'bar' => $bar = $this->createMock(Relation::class),
                'baz' => $baz = $this->createMock(Relation::class),
            ]);

        $foo->expects($this->once())->method('isValidated')->willReturn(true);
        $foo->method('name')->willReturn('foo');
        $bar->expects($this->once())->method('isValidated')->willReturn(false);
        $bar->method('name')->willReturn('bar');
        $baz->expects($this->once())->method('isValidated')->willReturn(true);
        $baz->method('name')->willReturn('baz');

        $validatedSchema = new ValidatedSchema($schema, $this->request);

        $this->assertSame(['foo', 'baz'], $validatedSchema->includePaths()->toArray());
    }

    /**
     * @return void
     */
    public function testItReturnsMessages(): void
    {
        $schema = new class extends TestSchema {
            public function validationMessages(): array
            {
                return [
                    'foo' => 'bar',
                    'baz' => 'bat',
                ];
            }
        };

        $validatedSchema = new ValidatedSchema($schema, $this->request);

        $this->assertSame([
            'foo' => 'bar',
            'baz' => 'bat',
        ], $validatedSchema->messages());
    }

    /**
     * @return void
     */
    public function testItDoesNotReturnMessages(): void
    {
        $validatedSchema = new ValidatedSchema(new TestSchema(), $this->request);

        $this->assertSame([], $validatedSchema->messages());
    }

    /**
     * @return void
     */
    public function testItReturnsAttributes(): void
    {
        $schema = new class extends TestSchema {
            public function validationAttributes(): array
            {
                return [
                    'foo' => 'bar',
                    'baz' => 'bat',
                ];
            }
        };

        $validatedSchema = new ValidatedSchema($schema, $this->request);

        $this->assertSame([
            'foo' => 'bar',
            'baz' => 'bat',
        ], $validatedSchema->attributes());
    }

    /**
     * @return void
     */
    public function testItDoesNotReturnAttributes(): void
    {
        $validatedSchema = new ValidatedSchema(new TestSchema(), $this->request);

        $this->assertSame([], $validatedSchema->attributes());
    }

    /**
     * @return void
     */
    public function testItCallsWithExisting(): void
    {
        $model = new \stdClass();
        $resource = ['type' => 'posts', 'id' => '123'];

        $schema = new class() extends TestSchema {
            public ?object $model = null;
            public ?array $resource = null;

            public function withExisting(object $model, array $resource): array
            {
                $this->model = $model;
                $this->resource = $resource;

                return [
                    'foo' => 'bar',
                    'baz' => 'bat',
                ];
            }
        };

        $validatedSchema = new ValidatedSchema($schema, $this->request);

        $this->assertSame([
            'foo' => 'bar',
            'baz' => 'bat',
        ], $validatedSchema->withExisting($model, $resource));
        $this->assertSame($model, $schema->model);
        $this->assertSame($resource, $schema->resource);
    }

    /**
     * @return void
     */
    public function testItDoesNotCallExisting(): void
    {
        $validatedSchema = new ValidatedSchema(new TestSchema(), $this->request);

        $this->assertNull($validatedSchema->withExisting(new \stdClass(), ['type' => 'posts', 'id' => '123']));
    }

    /**
     * @return array
     */
    public static function createAndUpdateProvider(): array
    {
        return [
            'create' => [
                new Create(null, new ResourceObject(
                    type: new ResourceType('posts'),
                )),
            ],
            'update' => [
                new Update(null, new ResourceObject(
                    type: new ResourceType('posts'),
                    id: new ResourceId('123'),
                )),
            ],
        ];
    }

    /**
     * @param Create|Update $operation
     * @return void
     * @dataProvider createAndUpdateProvider
     */
    public function testItCallsWithValidator(Create|Update $operation): void
    {
        $validator = $this->createMock(Validator::class);

        $schema = new class() extends TestSchema {
            public ?Validator $validator = null;
            public ?Request $request = null;
            public Create|Update|null $operation = null;

            public function withValidator(Validator $validator, Request $request, Create|Update $operation): void
            {
                $this->validator = $validator;
                $this->request = $request;
                $this->operation = $operation;
            }
        };

        $validatedSchema = new ValidatedSchema($schema, $this->request);
        $validatedSchema->withValidator($validator, $operation);

        $this->assertSame($validator, $schema->validator);
        $this->assertSame($this->request, $schema->request);
        $this->assertSame($operation, $schema->operation);
    }

    /**
     * @param Create|Update $operation
     * @return void
     * @dataProvider createAndUpdateProvider
     */
    public function testItDoesNotCallWithValidator(Create|Update $operation): void
    {
        $validator = $this->createMock(Validator::class);
        $validatedSchema = new ValidatedSchema(new TestSchema(), $this->request);

        $validatedSchema->withValidator($validator, $operation);
        $this->assertTrue(true);
    }

    /**
     * @return void
     */
    public function testItCallsWithCreationValidator(): void
    {
        $operation = new Create(null, new ResourceObject(
            type: new ResourceType('posts'),
        ));

        $validator = $this->createMock(Validator::class);

        $schema = new class() extends TestSchema {
            public ?Validator $validator = null;
            public ?Request $request = null;
            public Create|null $operation = null;

            public function withCreationValidator(Validator $validator, Request $request, Create $operation): void
            {
                $this->validator = $validator;
                $this->request = $request;
                $this->operation = $operation;
            }
        };

        $validatedSchema = new ValidatedSchema($schema, $this->request);
        $validatedSchema->withCreationValidator($validator, $operation);

        $this->assertSame($validator, $schema->validator);
        $this->assertSame($this->request, $schema->request);
        $this->assertSame($operation, $schema->operation);
    }

    /**
     * @return void
     */
    public function testItDoesNotCallWithCreationValidator(): void
    {
        $operation = new Create(null, new ResourceObject(
            type: new ResourceType('posts'),
        ));

        $validator = $this->createMock(Validator::class);
        $validatedSchema = new ValidatedSchema(new TestSchema(), $this->request);

        $validatedSchema->withCreationValidator($validator, $operation);
        $this->assertTrue(true);
    }

    /**
     * @return void
     */
    public function testItCallsWithUpdateValidator(): void
    {
        $operation = new Update(null, new ResourceObject(
            type: new ResourceType('posts'),
            id: new ResourceId('123'),
        ));

        $model = new \stdClass();
        $validator = $this->createMock(Validator::class);

        $schema = new class() extends TestSchema {
            public ?Validator $validator = null;
            public ?Request $request = null;
            public Update|null $operation = null;
            public ?object $model = null;

            public function withUpdateValidator(Validator $validator, Request $request, Update $operation, object $model): void
            {
                $this->validator = $validator;
                $this->request = $request;
                $this->operation = $operation;
                $this->model = $model;
            }
        };

        $validatedSchema = new ValidatedSchema($schema, $this->request);
        $validatedSchema->withUpdateValidator($validator, $operation, $model);

        $this->assertSame($validator, $schema->validator);
        $this->assertSame($this->request, $schema->request);
        $this->assertSame($operation, $schema->operation);
        $this->assertSame($model, $schema->model);
    }

    /**
     * @return void
     */
    public function testItDoesNotCallWithUpdateValidator(): void
    {
        $operation = new Update(null, new ResourceObject(
            type: new ResourceType('posts'),
            id: new ResourceId('123'),
        ));

        $model = new \stdClass();
        $validator = $this->createMock(Validator::class);
        $validatedSchema = new ValidatedSchema(new TestSchema(), $this->request);

        $validatedSchema->withUpdateValidator($validator, $operation, $model);
        $this->assertTrue(true);
    }

    /**
     * @return array
     */
    public static function relationshipOperationProvider(): array
    {
        return [
            'to-one' => [
                new UpdateToOne(new Ref(
                    type: new ResourceType('posts'),
                    id: new ResourceId('123'),
                    relationship: 'blog-posts',
                ), null),
            ],
            'to-many' => [
                new UpdateToMany(OpCodeEnum::Update, new Ref(
                    type: new ResourceType('posts'),
                    id: new ResourceId('123'),
                    relationship: 'blog-posts',
                ), new ListOfResourceIdentifiers()),
            ],
        ];
    }

    /**
     * @param UpdateToOne|UpdateToMany $operation
     * @return void
     * @dataProvider relationshipOperationProvider
     */
    public function testItCallsWithRelationshipValidator(UpdateToOne|UpdateToMany $operation): void
    {
        $validator = $this->createMock(Validator::class);
        $model = new \stdClass();

        $schema = new class() extends TestSchema {
            public ?Validator $validator = null;
            public ?Request $request = null;
            public UpdateToOne|UpdateToMany|null $operation = null;
            public ?object $model = null;

            public function withBlogPostsValidator(
                Validator $validator,
                Request $request,
                UpdateToOne|UpdateToMany $operation,
                object $model,
            ): void {
                $this->validator = $validator;
                $this->request = $request;
                $this->operation = $operation;
                $this->model = $model;
            }
        };

        $validatedSchema = new ValidatedSchema($schema, $this->request);
        $validatedSchema->withRelationshipValidator($validator, $operation, $model);

        $this->assertSame($validator, $schema->validator);
        $this->assertSame($this->request, $schema->request);
        $this->assertSame($operation, $schema->operation);
        $this->assertSame($model, $schema->model);
    }

    /**
     * @param UpdateToOne|UpdateToMany $operation
     * @return void
     * @dataProvider relationshipOperationProvider
     */
    public function testItDoesNotCallWithRelationshipValidator(UpdateToOne|UpdateToMany $operation): void
    {
        $validator = $this->createMock(Validator::class);
        $validatedSchema = new ValidatedSchema(new TestSchema(), $this->request);
        $model = new \stdClass();

        $validatedSchema->withRelationshipValidator($validator, $operation, $model);
        $this->assertTrue(true);
    }

    /**
     * @param Create|Update $operation
     * @return void
     * @dataProvider createAndUpdateProvider
     */
    public function testItCallsAfterValidation(Create|Update $operation): void
    {
        $validator = $this->createMock(Validator::class);

        $schema = new class() extends TestSchema {
            public ?Validator $validator = null;
            public ?Request $request = null;
            public Create|Update|null $operation = null;

            public function afterValidation(
                Validator $validator,
                Request $request,
                Create|Update $operation,
            ): void
            {
                $this->validator = $validator;
                $this->request = $request;
                $this->operation = $operation;
            }
        };

        $validatedSchema = new ValidatedSchema($schema, $this->request);
        $validatedSchema->afterValidation($validator, $operation);

        $this->assertSame($validator, $schema->validator);
        $this->assertSame($this->request, $schema->request);
        $this->assertSame($operation, $schema->operation);
    }

    /**
     * @param Create|Update $operation
     * @return void
     * @dataProvider createAndUpdateProvider
     */
    public function testItDoesNotCallAfterValidation(Create|Update $operation): void
    {
        $validator = $this->createMock(Validator::class);
        $validatedSchema = new ValidatedSchema(new TestSchema(), $this->request);

        $validatedSchema->afterValidation($validator, $operation);
        $this->assertTrue(true);
    }

    /**
     * @return void
     */
    public function testItCallsAfterCreationValidation(): void
    {
        $operation = new Create(null, new ResourceObject(
            type: new ResourceType('posts'),
        ));

        $validator = $this->createMock(Validator::class);

        $schema = new class() extends TestSchema {
            public ?Validator $validator = null;
            public ?Request $request = null;
            public Create|null $operation = null;

            public function afterCreationValidation(
                Validator $validator,
                Request $request,
                Create $operation,
            ): void
            {
                $this->validator = $validator;
                $this->request = $request;
                $this->operation = $operation;
            }
        };

        $validatedSchema = new ValidatedSchema($schema, $this->request);
        $validatedSchema->afterCreationValidation($validator, $operation);

        $this->assertSame($validator, $schema->validator);
        $this->assertSame($this->request, $schema->request);
        $this->assertSame($operation, $schema->operation);
    }

    /**
     * @return void
     */
    public function testItDoesNotCallAfterCreationValidation(): void
    {
        $operation = new Create(null, new ResourceObject(
            type: new ResourceType('posts'),
        ));

        $validator = $this->createMock(Validator::class);
        $validatedSchema = new ValidatedSchema(new TestSchema(), $this->request);

        $validatedSchema->afterCreationValidation($validator, $operation);
        $this->assertTrue(true);
    }

    /**
     * @return void
     */
    public function testItCallsAfterUpdateValidation(): void
    {
        $operation = new Update(null, new ResourceObject(
            type: new ResourceType('posts'),
            id: new ResourceId('123'),
        ));

        $model = new \stdClass();
        $validator = $this->createMock(Validator::class);

        $schema = new class() extends TestSchema {
            public ?Validator $validator = null;
            public ?Request $request = null;
            public Update|null $operation = null;
            public ?object $model = null;

            public function afterUpdateValidation(
                Validator $validator,
                Request $request,
                Update $operation,
                object $model,
            ): void
            {
                $this->validator = $validator;
                $this->request = $request;
                $this->operation = $operation;
                $this->model = $model;
            }
        };

        $validatedSchema = new ValidatedSchema($schema, $this->request);
        $validatedSchema->afterUpdateValidation($validator, $operation, $model);

        $this->assertSame($validator, $schema->validator);
        $this->assertSame($this->request, $schema->request);
        $this->assertSame($operation, $schema->operation);
        $this->assertSame($model, $schema->model);
    }

    /**
     * @return void
     */
    public function testItDoesNotCallAfterUpdateValidation(): void
    {
        $operation = new Update(null, new ResourceObject(
            type: new ResourceType('posts'),
            id: new ResourceId('123'),
        ));

        $model = new \stdClass();
        $validator = $this->createMock(Validator::class);
        $validatedSchema = new ValidatedSchema(new TestSchema(), $this->request);

        $validatedSchema->afterUpdateValidation($validator, $operation, $model);
        $this->assertTrue(true);
    }

    /**
     * @param UpdateToOne|UpdateToMany $operation
     * @return void
     * @dataProvider relationshipOperationProvider
     */
    public function testItCallsAfterRelationshipValidation(UpdateToOne|UpdateToMany $operation): void
    {
        $validator = $this->createMock(Validator::class);
        $model = new \stdClass();

        $schema = new class() extends TestSchema {
            public ?Validator $validator = null;
            public ?Request $request = null;
            public UpdateToOne|UpdateToMany|null $operation = null;
            public ?object $model = null;

            public function afterBlogPostsValidation(
                Validator $validator,
                Request $request,
                UpdateToOne|UpdateToMany $operation,
                object $model,
            ): void {
                $this->validator = $validator;
                $this->request = $request;
                $this->operation = $operation;
                $this->model = $model;
            }
        };

        $validatedSchema = new ValidatedSchema($schema, $this->request);
        $validatedSchema->afterRelationshipValidation($validator, $operation, $model);

        $this->assertSame($validator, $schema->validator);
        $this->assertSame($this->request, $schema->request);
        $this->assertSame($operation, $schema->operation);
        $this->assertSame($model, $schema->model);
    }

    /**
     * @param UpdateToOne|UpdateToMany $operation
     * @return void
     * @dataProvider relationshipOperationProvider
     */
    public function testItDoesNotCallAfterRelationshipValidation(UpdateToOne|UpdateToMany $operation): void
    {
        $validator = $this->createMock(Validator::class);
        $validatedSchema = new ValidatedSchema(new TestSchema(), $this->request);
        $model = new \stdClass();

        $validatedSchema->afterRelationshipValidation($validator, $operation, $model);
        $this->assertTrue(true);
    }

    /**
     * @return void
     */
    public function testItReturnsDeletionRules(): void
    {
        $schema = new class extends TestSchema {
            public ?Request $request = null;
            public ?object $model = null;
            public function deletionRules(Request $request, object $model): array
            {
                $this->request = $request;
                $this->model = $model;

                return [
                    'foo' => 'bar',
                    'baz' => 'bat',
                ];
            }
        };

        $model = new \stdClass();
        $validatedSchema = new ValidatedSchema($schema, $this->request);

        $this->assertSame([
            'foo' => 'bar',
            'baz' => 'bat',
        ], $validatedSchema->deletionRules($model));
        $this->assertSame($this->request, $schema->request);
        $this->assertSame($model, $schema->model);
    }

    /**
     * @return void
     */
    public function testItDoesNotReturnDeletionRules(): void
    {
        $validatedSchema = new ValidatedSchema(new TestSchema(), $this->request);
        $this->assertSame([], $validatedSchema->deletionRules(new \stdClass()));
    }

    /**
     * @return void
     */
    public function testItReturnsMetaForDeletion(): void
    {
        $schema = new class extends TestSchema {
            public ?Request $request = null;
            public ?object $model = null;
            public function metaForDeletion(Request $request, object $model): array
            {
                $this->request = $request;
                $this->model = $model;

                return [
                    'foo' => 'bar',
                    'baz' => 'bat',
                ];
            }
        };

        $model = new \stdClass();
        $validatedSchema = new ValidatedSchema($schema, $this->request);

        $this->assertSame([
            'foo' => 'bar',
            'baz' => 'bat',
        ], $validatedSchema->metaForDeletion($model));
        $this->assertSame($this->request, $schema->request);
        $this->assertSame($model, $schema->model);
    }

    /**
     * @return void
     */
    public function testItDoesNotReturnMetaForDeletion(): void
    {
        $validatedSchema = new ValidatedSchema(new TestSchema(), $this->request);
        $this->assertSame([], $validatedSchema->metaForDeletion(new \stdClass()));
    }

    /**
     * @return void
     */
    public function testItReturnsDeletionMessages(): void
    {
        $schema = new class extends TestSchema {
            public function deletionMessages(): array
            {
                return [
                    'foo' => 'bar',
                    'baz' => 'bat',
                ];
            }
        };

        $validatedSchema = new ValidatedSchema($schema, $this->request);

        $this->assertSame([
            'foo' => 'bar',
            'baz' => 'bat',
        ], $validatedSchema->deletionMessages());
    }

    /**
     * @return void
     */
    public function testItReturnsDeletionMessagesCombinedWithMessages(): void
    {
        $schema = new class extends TestSchema {
            public function validationMessages(): array
            {
                return [
                    'foo' => 'bar',
                    'baz' => 'bat',
                ];
            }
            public function deletionMessages(): array
            {
                return [
                    'baz' => 'blah!',
                    'bat' => 'blah!!',
                ];
            }
        };

        $validatedSchema = new ValidatedSchema($schema, $this->request);

        $this->assertSame([
            'foo' => 'bar',
            'baz' => 'blah!',
            'bat' => 'blah!!',
        ], $validatedSchema->deletionMessages());
    }

    /**
     * @return void
     */
    public function testItDoesNotReturnDeletionMessages(): void
    {
        $validatedSchema = new ValidatedSchema(new TestSchema(), $this->request);
        $this->assertSame([], $validatedSchema->deletionMessages());
    }

    /**
     * @return void
     */
    public function testItReturnsDeletionAttributes(): void
    {
        $schema = new class extends TestSchema {
            public function deletionAttributes(): array
            {
                return [
                    'foo' => 'bar',
                    'baz' => 'bat',
                ];
            }
        };

        $validatedSchema = new ValidatedSchema($schema, $this->request);

        $this->assertSame([
            'foo' => 'bar',
            'baz' => 'bat',
        ], $validatedSchema->deletionAttributes());
    }

    /**
     * @return void
     */
    public function testItReturnsDeletionAttributesCombinedWithAttributes(): void
    {
        $schema = new class extends TestSchema {
            public function validationAttributes(): array
            {
                return [
                    'foo' => 'bar',
                    'baz' => 'bat',
                ];
            }
            public function deletionAttributes(): array
            {
                return [
                    'baz' => 'blah!',
                    'bat' => 'blah!!',
                ];
            }
        };

        $validatedSchema = new ValidatedSchema($schema, $this->request);

        $this->assertSame([
            'foo' => 'bar',
            'baz' => 'blah!',
            'bat' => 'blah!!',
        ], $validatedSchema->deletionAttributes());
    }

    /**
     * @return void
     */
    public function testItDoesNotReturnDeletionAttributes(): void
    {
        $validatedSchema = new ValidatedSchema(new TestSchema(), $this->request);
        $this->assertSame([], $validatedSchema->deletionAttributes());
    }

    /**
     * @return void
     */
    public function testItCallsWithDeletionValidator(): void
    {
        $operation = new Delete(new Ref(
            type: new ResourceType('posts'),
            id: new ResourceId('123'),
        ));

        $model = new \stdClass();
        $validator = $this->createMock(Validator::class);

        $schema = new class() extends TestSchema {
            public ?Validator $validator = null;
            public ?Request $request = null;
            public Delete|null $operation = null;
            public ?object $model = null;

            public function withDeletionValidator(Validator $validator, Request $request, Delete $operation, object $model): void
            {
                $this->validator = $validator;
                $this->request = $request;
                $this->operation = $operation;
                $this->model = $model;
            }
        };

        $validatedSchema = new ValidatedSchema($schema, $this->request);
        $validatedSchema->withDeletionValidator($validator, $operation, $model);

        $this->assertSame($validator, $schema->validator);
        $this->assertSame($this->request, $schema->request);
        $this->assertSame($operation, $schema->operation);
        $this->assertSame($model, $schema->model);
    }

    /**
     * @return void
     */
    public function testItDoesNotCallWithDeletionValidator(): void
    {
        $operation = new Delete(new Ref(
            type: new ResourceType('posts'),
            id: new ResourceId('123'),
        ));

        $model = new \stdClass();
        $validator = $this->createMock(Validator::class);
        $validatedSchema = new ValidatedSchema(new TestSchema(), $this->request);

        $validatedSchema->withDeletionValidator($validator, $operation, $model);
        $this->assertTrue(true);
    }

    /**
     * @return void
     */
    public function testItCallsAfterDeletionValidation(): void
    {
        $operation = new Delete(new Ref(
            type: new ResourceType('posts'),
            id: new ResourceId('123'),
        ));

        $model = new \stdClass();
        $validator = $this->createMock(Validator::class);

        $schema = new class() extends TestSchema {
            public ?Validator $validator = null;
            public ?Request $request = null;
            public Delete|null $operation = null;
            public ?object $model = null;

            public function afterDeletionValidation(Validator $validator, Request $request, Delete $operation, object $model): void
            {
                $this->validator = $validator;
                $this->request = $request;
                $this->operation = $operation;
                $this->model = $model;
            }
        };

        $validatedSchema = new ValidatedSchema($schema, $this->request);
        $validatedSchema->afterDeletionValidation($validator, $operation, $model);

        $this->assertSame($validator, $schema->validator);
        $this->assertSame($this->request, $schema->request);
        $this->assertSame($operation, $schema->operation);
        $this->assertSame($model, $schema->model);
    }

    /**
     * @return void
     */
    public function testItDoesNotCallAfterDeletionValidation(): void
    {
        $operation = new Delete(new Ref(
            type: new ResourceType('posts'),
            id: new ResourceId('123'),
        ));

        $model = new \stdClass();
        $validator = $this->createMock(Validator::class);
        $validatedSchema = new ValidatedSchema(new TestSchema(), $this->request);

        $validatedSchema->afterDeletionValidation($validator, $operation, $model);
        $this->assertTrue(true);
    }
}
