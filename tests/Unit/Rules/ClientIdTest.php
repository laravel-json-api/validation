<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

declare(strict_types=1);

namespace LaravelJsonApi\Validation\Tests\Unit\Rules;

use LaravelJsonApi\Contracts\Schema\ID;
use LaravelJsonApi\Contracts\Schema\Schema;
use LaravelJsonApi\Validation\Rules\ClientId;
use PHPUnit\Framework\TestCase;

class ClientIdTest extends TestCase
{

    public function test(): void
    {
        $schema = $this->createMock(Schema::class);
        $schema->method('id')->willReturn($id = $this->createMock(ID::class));

        $id->expects($this->exactly(2))
            ->method('match')
            ->willReturnCallback(static fn($value) => 'a2a05c9d-9525-41ca-bdd2-57e174a551c9' === $value);

        $rule = new ClientId($schema);

        $this->assertTrue($rule->passes('id', 'a2a05c9d-9525-41ca-bdd2-57e174a551c9'));
        $this->assertFalse($rule->passes('id', 'f78ad6a6-d593-48c2-b68c-d91fa46f8ce1'));
    }
}
