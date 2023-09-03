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

namespace LaravelJsonApi\Validation\Tests\Unit;

use LaravelJsonApi\Contracts\Schema\Container as SchemaContainer;
use LaravelJsonApi\Contracts\Schema\Schema;
use LaravelJsonApi\Contracts\Server\Server;
use LaravelJsonApi\Core\Values\ResourceType;
use LaravelJsonApi\Validation\Container;
use LaravelJsonApi\Validation\Validators\Factory;
use PHPUnit\Framework\TestCase;
use Illuminate\Contracts\Validation\Factory as ValidatorFactory;

class ContainerTest extends TestCase
{
    /**
     * @return array
     */
    public function typeProvider(): array
    {
        return [
            ['posts'],
            [new ResourceType('posts')],
        ];
    }

    /**
     * @param ResourceType|string $type
     * @return void
     * @dataProvider typeProvider
     */
    public function test(ResourceType|string $type): void
    {
        $container = new Container(
            $server = $this->createMock(Server::class),
            $validatorFactory = $this->createMock(ValidatorFactory::class),
        );

        $server
            ->expects($this->once())
            ->method('schemas')
            ->willReturn($schemas = $this->createMock(SchemaContainer::class));

        $schemas
            ->expects($this->once())
            ->method('schemaFor')
            ->with($this->identicalTo($type))
            ->willReturn($schema = $this->createMock(Schema::class));

        $expected = new Factory(
            $server,
            $schema,
            $validatorFactory,
        );

        $this->assertEquals($expected, $container->validatorsFor($type));
    }
}
