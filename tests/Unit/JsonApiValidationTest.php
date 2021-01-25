<?php
/*
 * Copyright 2021 Cloud Creativity Limited
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

use LaravelJsonApi\Contracts\Schema\Schema;
use LaravelJsonApi\Validation\JsonApiValidation;
use LaravelJsonApi\Validation\Rules\ClientId;
use LaravelJsonApi\Validation\Tests\Integration\TestCase;

class JsonApiValidationTest extends TestCase
{

    public function testTranslationKey(): void
    {
        $rule = new ClientId($this->createMock(Schema::class));

        $this->assertSame(
            'jsonapi-validation::validation.client_id',
            JsonApiValidation::translationKeyForRule($rule)
        );

        $this->assertSame(
            'jsonapi-validation::validation.client_id.foo',
            JsonApiValidation::translationKeyForRule($rule, 'foo')
        );
    }
}
