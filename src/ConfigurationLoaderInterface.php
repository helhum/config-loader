<?php
declare(strict_types=1);
namespace Helhum\ConfigLoader;

interface ConfigurationLoaderInterface
{
    public function load(): array;
}
