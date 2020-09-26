<?php

declare(strict_types=1);

namespace MyProject;

use Brick\VarExporter\VarExporter;

final class VarHasher
{
    /**
     * @param mixed $var
     */
    public function compute($var): int
    {
        return crc32(VarExporter::export($var, VarExporter::NO_CLOSURES));
    }
}
