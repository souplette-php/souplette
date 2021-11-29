<?php declare(strict_types=1);

namespace Souplette\Dom\Api\Native;

use DOMAttr;
use DOMCdataSection;
use DOMComment;
use DOMDocument;
use DOMDocumentFragment;
use DOMDocumentType;
use DOMElement;
use DOMEntityReference;
use DOMImplementation;
use DOMNode;
use DOMNodeList;
use DOMParentNode;
use DOMProcessingInstruction;
use DOMText;

/**
 * @property-read string $actualEncoding
 * @property-read DOMDocumentType $doctype
 * @property-read DOMElement|null $documentElement
 * @property string|null $documentURI
 * @property string $encoding
 * @property bool $formatOutput
 * @property-read DOMImplementation $implementation
 * @property bool $preserveWhiteSpace = TRUE
 * @property bool $recover
 * @property bool $resolveExternals
 * @property bool $standalone
 * @property bool $strictErrorChecking = TRUE
 * @property bool $substituteEntities
 * @property bool $validateOnParse = FALSE
 * @property string $version
 * @property-read string|null $xmlEncoding
 * @property bool $xmlStandalone
 * @property string $xmlVersion
 */
interface DomDocumentInterface extends DomNodeInterface, DOMParentNode
{
    /**
     * @return DOMAttr|false
     */
    public function createAttribute(string $localName);
    /**
     * @return DOMAttr|false
     */
    public function createAttributeNS(?string $namespace, string $qualifiedName);
    /**
     * @return DOMCdataSection|false
     */
    public function createCDATASection(string $data);
    /**
     * @return DOMComment|false
     */
    public function createComment(string $data);
    /**
     * @return DOMDocumentFragment|false
     */
    public function createDocumentFragment();
    /**
     * @return DOMElement|false
     */
    public function createElement(string $localName, string $value = "");
    /**
     * @return DOMElement|false
     */
    public function createElementNS(?string $namespace, string $qualifiedName, string $value = "");
    /**
     * @return DOMEntityReference|false
     */
    public function createEntityReference(string $name);
    /**
     * @return DOMProcessingInstruction|false
     */
    public function createProcessingInstruction(string $target, string $data = "");
    /**
     * @return DOMText|false
     */
    public function createTextNode(string $data);
    /**
     * @return ?DOMElement
     */
    public function getElementById(string $elementId);
    /**
     * @return DOMNodeList
     */
    public function getElementsByTagName(string $qualifiedName);
    /**
     * @return DOMNodeList
     */
    public function getElementsByTagNameNS(?string $namespace, string $localName);
    /**
     * @return DOMNode|false
     */
    public function importNode(DOMNode $node, bool $deep = false);
    /**
     * @return DOMDocument|false
     */
    public function load(string $filename, int $options = 0);
    /**
     * @return DOMDocument|false
     */
    public function loadHTML(string $source, int $options = 0);
    /**
     * @return DOMDocument|false
     */
    public function loadHTMLFile(string $filename, int $options = 0);
    /**
     * @return DOMDocument|false
     */
    public function loadXML(string $source, int $options = 0);

    public function normalizeDocument(): void;
    public function registerNodeClass(string $baseClass, ?string $extendedClass): bool;
    public function relaxNGValidate(string $filename): bool;
    public function relaxNGValidateSource(string $source): bool;
    public function save(string $filename, int $options = 0): int|false;
    public function saveHTML(?DOMNode $node = null): string|false;
    public function saveHTMLFile(string $filename): int|false;
    public function saveXML(?DOMNode $node = null, int $options = 0): string|false;
    public function schemaValidate(string $filename, int $flags = 0): bool;
    public function schemaValidateSource(string $source, int $flags = 0): bool;
    public function validate(): bool;
    public function xinclude(int $options = 0): int|false;
}
