<?php declare(strict_types=1);

namespace Souplette\Xml\Parser;

interface ExternalEntityLoaderInterface
{
    /**
     * @see https://php.net/manual/en/function.libxml-set-external-entity-loader.php
     */
    public function __invoke(string $publicId, string $systemId, array $context): string|null;
}
