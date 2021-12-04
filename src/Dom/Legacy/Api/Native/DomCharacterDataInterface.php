<?php declare(strict_types=1);

namespace Souplette\Dom\Legacy\Api\Native;

/**
 * @property string $data
 * @property-read int $length
 */
interface DomCharacterDataInterface extends DomNodeInterface
{
    /**
     * @return bool
     */
    public function appendData(string $data);

    /**
     * @return bool
     */
    public function deleteData(int $offset, int $count);

    /**
     * @return bool
     */
    public function insertData(int $offset, string $data);

    /**
     * @return bool
     */
    public function replaceData(int $offset, int $count, string $data);

    /**
     * @return string|false
     */
    public function substringData(int $offset, int $count);
}
