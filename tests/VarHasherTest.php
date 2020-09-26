<?php

declare(strict_types=1);

namespace MyProjectTest;

use Brick\VarExporter\ExportException;
use MyProject\VarHasher;
use PHPUnit\Framework\TestCase;

final class VarHasherTest extends TestCase
{
    /**
     * @dataProvider provideCases
     *
     * @param mixed $var
     */
    public function testHashes($var): void
    {
        self::assertGreaterThan(0, (new VarHasher())->compute($var));
    }

    public function provideCases(): array
    {
        return [
            [true],
            ['string'],
            [1],
            [[]],
            [new \stdClass()],
        ];
    }

    public function testCannotExportClosuers(): void
    {
        $this->expectException(ExportException::class);

        (new VarHasher())->compute(function () {});
    }
}
