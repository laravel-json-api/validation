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

use Carbon\Carbon;
use LaravelJsonApi\Validation\Rules\DateTimeIso8601;
use PHPUnit\Framework\TestCase;

class DateTimeIso8601Test extends TestCase
{

    /**
     * @var DateTimeIso8601
     */
    private DateTimeIso8601 $rule;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->rule = new DateTimeIso8601();
    }

    /**
     * @return array
     */
    public static function validProvider(): array
    {
        return [
            ['2018-01-01T12:00+00:00'],
            ['2018-01-01T12:00:00+00:00'],
            ['2018-01-01T12:00:00.1+01:00'],
            ['2018-01-01T12:00:00.12+02:00'],
            ['2018-01-01T12:00:00.123+03:00'],
            ['2018-01-01T12:00:00.1234+04:00'],
            ['2018-01-01T12:00:00.12345+05:00'],
            ['2018-01-01T12:00:00.123456+06:00'],
            ['2018-01-01T12:00Z'],
            ['2018-01-01T12:00:00Z'],
            ['2018-01-01T12:00:00.123Z'],
            ['2018-01-01T12:00:00.123456Z'],
        ];
    }

    /**
     * @return array
     */
    public static function invalidProvider(): array
    {
        return [
            [null],
            [false],
            [true],
            [[]],
            [new \stdClass()],
            [new \DateTime()],
            [new Carbon()],
            [time()],
            [''],
            ['2018'],
            ['2018-01'],
            ['2018-01-02'],
        ];
    }

    /**
     * @param $value
     * @dataProvider validProvider
     */
    public function testValid($value): void
    {
        $this->assertTrue($this->rule->passes('date', $value));
    }

    /**
     * @param $value
     * @dataProvider invalidProvider
     */
    public function testInvalid($value): void
    {
        $this->assertFalse($this->rule->passes('date', $value));
    }

    /**
     * @param string $value
     * @dataProvider validProvider
     */
    public function testValidValuesCanBeDates(string $value): void
    {
        $date = new \DateTime($value);

        $this->assertInstanceOf(\DateTime::class, $date);
    }
}
