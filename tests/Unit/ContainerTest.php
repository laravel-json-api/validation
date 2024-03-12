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
    public static function typeProvider(): array
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
