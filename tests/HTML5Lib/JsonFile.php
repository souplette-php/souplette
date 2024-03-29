<?php declare(strict_types=1);

namespace Souplette\Tests\HTML5Lib;

use UnexpectedValueException;

final class JsonFile extends TestFile
{
    protected function parse(string $fileName): array
    {
        $data = json_decode(file_get_contents($fileName), true, 512, JSON_THROW_ON_ERROR);
        if (isset($data['tests'])) {
            return $data['tests'];
        }
        if (isset($data['xmlViolationTests'])) {
            return $data['xmlViolationTests'];
        }
        throw new UnexpectedValueException(sprintf(
            'Invalid JSON test file: %s',
            $this->fileName
        ));
    }
}
