<?php

declare(strict_types=1);

namespace Common;

interface Result
{
    public function isSuccess(): bool;

    /**
     * @return Error[]
     */
    public function getErrors(): array;

    public function hasErrors(): bool;
}
