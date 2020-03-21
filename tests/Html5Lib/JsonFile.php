<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\Tests\Html5Lib;

final class JsonFile extends TestFile
{
    protected function parse(string $input): array
    {
        $data = json_decode($input, true, 512, JSON_THROW_ON_ERROR);
        if (isset($data['tests'])) {
            return $data['tests'];
        }
        if (isset($data['xmlViolationTests'])) {
            return $data['xmlViolationTests'];
        }
        throw new \UnexpectedValueException(sprintf(
            'Invalid JSON test file: %s',
            $this->fileName
        ));
    }
}
