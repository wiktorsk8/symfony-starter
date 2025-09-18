<?php

namespace App\Shared\DTO;

readonly abstract class DTO
{
    public abstract function toArray(): array;
}
