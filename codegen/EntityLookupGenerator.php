<?php declare(strict_types=1);

namespace Souplette\Codegen;

class EntityLookupGenerator extends AbstractCodeGenerator
{
    const DATA_URL = 'https://html.spec.whatwg.org/entities.json';

    protected function getTemplateFile(): string
    {
        return 'entity_lookup.php.twig';
    }

    protected function getOutputFile(): string
    {
        return __DIR__ . '/../src/HTML/Tokenizer/EntityLookup.php';
    }

    protected function createContext(): array
    {
        return [
            'entity_lookup' => $this->generateLookupTable(),
        ];
    }

    protected function generateLookupTable(): array
    {
        $spec = $this->fetchSpecData();
        $lookup = [];
        foreach ($spec as $entity => $value) {
            $key = self::reprEntityName($entity);
            $lookup[$key] = self::reprEntityValue($value->codepoints);
        }

        return $lookup;
    }

    protected function fetchSpecData()
    {
        $cacheDir = $this->getCacheDirectory();
        $dataFile = sprintf('%s/entities.json', $cacheDir);
        if (!file_exists($dataFile)) {
            Utils::downloadFile(self::DATA_URL, $dataFile);
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
