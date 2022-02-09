<?php
/*
 * Copyright 2022 Cloud Creativity Limited
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
