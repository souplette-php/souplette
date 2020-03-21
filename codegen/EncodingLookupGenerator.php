<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\Codegen;

final class EncodingLookupGenerator extends AbstractCodeGenerator
{
    const DATA_URL = 'https://encoding.spec.whatwg.org/encodings.json';

    protected function getTemplateFile(): string
    {
        return 'encoding_lookup.php.twig';
    }

    protected function getOutputFile(): string
    {
        return __DIR__.'/../src/Encoding/EncodingLookup.php';
    }

    protected function createContext(): array
    {
        $spec = $this->fetchSpecData();
        return [
            'encodings' => $spec,
        ];
    }

    private function fetchSpecData()
    {
        $cacheDir = $this->getCacheDirectory();
        $dataFile = sprintf('%s/encodings.json', $cacheDir);
        if (!file_exists($dataFile)) {
            Utils::downloadFile(self::DATA_URL, $dataFile);
        }

        return json_decode(file_get_contents($dataFile));
    }
}
