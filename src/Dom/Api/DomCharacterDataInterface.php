<?php declare(strict_types=1);

namespace Souplette\Dom\Api;

/**
 * @property string $data
 * @property-read int $length
 */
interface DomCharacterDataInterface extends DomNodeInterface
{
    public function appendData($data);
    public function deleteData($offset, $count);
    public function insertData($offset, $data);
    public function replaceData($offset, $count, $data);
    public function substringData($offset, $count);
}
