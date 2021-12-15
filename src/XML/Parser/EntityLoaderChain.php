<?php declare(strict_types=1);

namespace Souplette\XML\Parser;

final class EntityLoaderChain implements ExternalEntityLoaderInterface
{
    /**
     * @var ExternalEntityLoaderInterface[]
     */
    private array $loaders = [];

    public function __construct(array $loaders = [])
    {
        foreach ($loaders as $loader) {
            $this->add($loader);
        }
    }

    public function add(ExternalEntityLoaderInterface $loader): void
    {
        $this->loaders[] = $loader;
    }

    public function __invoke(?string $publicId, string $systemId, array $context)
    {
        foreach ($this->loaders as $loader) {
            $result = $loader($publicId, $systemId, $context);
            if ($result !== null) return $result;
        }
        return null;
    }
}
