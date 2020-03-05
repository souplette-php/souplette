<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\Codegen;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

final class EntityLookupGenerator
{
    const DATA_URL = 'https://html.spec.whatwg.org/entities.json';
    const CACHE_DIR = __DIR__ . '/../tmp';

    public function generate()
    {
        $twig = $this->createEnvironment();
        $context = $this->createContext();
        $code = $twig->render('entity_lookup.php.twig', $context);
        file_put_contents(__DIR__.'/../src/Parser/Entities/EntityLookup.php', $code);
    }

    private function createEnvironment(): Environment
    {
        $loader = new FilesystemLoader(__DIR__ . '/templates');
        $twig = new Environment($loader, [
            'strict_variables' => true,
            'autoescape' => false,
        ]);

        return $twig;
    }

    private function createContext(): array
    {
        return [
            'entity_lookup' => $this->generateLookupTable(),
        ];
    }

    private function generateLookupTable(): array
    {
        $spec = $this->fetchSpecData();
        $lookup = [];
        foreach ($spec as $entity => $value) {
            $key = self::reprEntityName($entity);
            $lookup[$key] = self::reprEntityValue($value->codepoints);
        }

        return $lookup;
    }

    private function fetchSpecData()
    {
        $cacheDir = realpath(self::CACHE_DIR);
        if (!$cacheDir && !mkdir($cacheDir, 0755, true)) {
            printf("Could not create directory: %s\n", self::CACHE_DIR);
        }
        $dataFile = sprintf('%s/entities.json', $cacheDir);
        if (!file_exists($dataFile)) {
            file_put_contents($dataFile, fopen(self::DATA_URL, 'r'));
        }

        return json_decode(file_get_contents($dataFile));
    }

    private static function reprEntityName(string $name): string
    {
        $key = substr($name, 1);

        return var_export($key, true);
    }

    private static function reprEntityValue(array $codepoints): string
    {
        $escapes = array_map(function(int $cp) {
            return sprintf('\u{%02X}', $cp);
        }, $codepoints);

        return sprintf('"%s"', implode('', $escapes));
    }
}
