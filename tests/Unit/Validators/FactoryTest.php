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

namespace LaravelJsonApi\Validation\Tests\Unit\Validators;

use Illuminate\Contracts\Validation\Factory as ValidatorFactory;
use Illuminate\Http\Request;
use LaravelJsonApi\Contracts\Schema\Schema;
use LaravelJsonApi\Contracts\Server\Server;
use LaravelJsonApi\Validation\Tests\Unit\TestSchema;
use LaravelJsonApi\Validation\Validators\DeletionValidator;
use LaravelJsonApi\Validation\Validators\Factory;
use LaravelJsonApi\Validation\Validators\QueryManyValidator;
use LaravelJsonApi\Validation\Validators\QueryOneValidator;
use LaravelJsonApi\Validation\Validators\RelationshipValidator;
use LaravelJsonApi\Validation\Validators\CreationValidator;
use LaravelJsonApi\Validation\Validators\UpdateValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase
{
    /**
     * @var MockObject&Server
     */
    private Server&MockObject $server;

    /**
     * @var MockObject&Schema
     */
    private Schema&MockObject $schema;

    /**
     * @var MockObject&ValidatorFactory
     */
    private ValidatorFactory&MockObject $validatorFactory;

    /**
     * @var MockObject&Request
     */
    private Request&MockObject $request;

    /**
     * @var Factory
     */
    private Factory $factory;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = new Factory(
            $this->server = $this->createMock(Server::class),
            $this->schema = $this->createMock(Schema::class),
            $this->validatorFactory = $this->createMock(ValidatorFactory::class),
        );

        $this->request = $this->createMock(Request::class);
    }

    /**
     * @return void
     */
    public function testItCreatesQueryOneValidatorWithRequest(): void
    {
        $this->assertInstanceOf(
            QueryOneValidator::class,
            $this->factory->withRequest($this->request)->queryOne(),
        );
    }

    /**
     * @return void
     */
    public function testItCreatesQueryOneValidatorWithoutRequest(): void
    {
        $this->assertInstanceOf(
            QueryOneValidator::class,
            $this->factory->queryOne(),
        );
    }

    /**
     * @return void
     */
    public function testItCreatesQueryManyValidatorWithRequest(): void
    {
        $this->assertInstanceOf(
            QueryManyValidator::class,
            $this->factory->withRequest($this->request)->queryMany(),
        );
    }

    /**
     * @return void
     */
    public function testItCreatesQueryManyValidatorWithoutRequest(): void
    {
        $this->assertInstanceOf(
            QueryManyValidator::class,
            $this->factory->queryMany(),
        );
    }

    /**
     * @return void
     */
    public function testItCreatesStoreValidatorWithRequest(): void
    {
        $this->assertInstanceOf(
            CreationValidator::class,
            $this->factory->withRequest($this->request)->store(),
        );
    }

    /**
     * @return void
     */
    public function testItCreatesStoreValidatorWithoutRequest(): void
    {
        $this->assertInstanceOf(
            CreationValidator::class,
            $this->factory->store(),
        );
    }

    /**
     * @return void
     */
    public function testItCreatesUpdateValidatorWithoutRequest(): void
    {
        $this->assertInstanceOf(
            UpdateValidator::class,
            $this->factory->update(),
        );
    }

    /**
     * @return void
     */
    public function testItCreatesUpdateValidatorWithRequest(): void
    {
        $this->assertInstanceOf(
            UpdateValidator::class,
            $this->factory->withRequest($this->request)->update(),
        );
    }

    /**
     * @return void
     */
    public function testItDoesNotCreateDestroyValidator(): void
    {
        $this->assertNull(
            $this->factory->withRequest($this->request)->destroy()
        );
    }

    /**
     * @return void
     */
    public function testItCreatesDestroyValidatorWithoutRequest(): void
    {
        $schema = new class extends TestSchema {
            public function deleteRules(): array
            {
                return [];
            }
        };

        $factory = new Factory(
            $this->server,
            $schema,
            $this->validatorFactory,
        );

        $this->assertInstanceOf(
            DeletionValidator::class,
            $factory->destroy(),
        );
    }

    /**
     * @return void
     */
    public function testItCreatesDestroyValidatorWithRequest(): void
    {
        $schema = new class extends TestSchema {
            public function deleteRules(): array
            {
                return [];
            }
        };

        $factory = new Factory(
            $this->server,
            $schema,
            $this->validatorFactory,
        );

        $this->assertInstanceOf(
            DeletionValidator::class,
            $factory->withRequest($this->request)->destroy(),
        );
    }

    /**
     * @return void
     */
    public function testItCreatesRelationshipValidatorWithoutRequest(): void
    {
        $this->assertInstanceOf(
            RelationshipValidator::class,
            $this->factory->relation(),
        );
    }

    /**
     * @return void
     */
    public function testItCreatesRelationshipValidatorWithRequest(): void
    {
        $this->assertInstanceOf(
            RelationshipValidator::class,
            $this->factory->withRequest($this->request)->relation(),
        );
    }
}
