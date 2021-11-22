<?php declare(strict_types=1);

namespace Souplette\Dom\Api;

use DOMConfiguration;
use DOMDocumentType;
use DOMElement;
use DOMImplementation;
use DOMNode;

/**
 * @property-read string $actualEncoding
 * @property-read DOMConfiguration $config
 * @property-read DOMDocumentType $doctype
 * @property-read DOMElement $documentElement
 * @property string $documentURI
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
 * @property-read string $xmlEncoding
 * @property bool $xmlStandalone
 * @property string $xmlVersion
 */
interface DomDocumentInterface extends DomNodeInterface
{
    public function createElement($name, $value = null);
    public function createDocumentFragment();
    public function createTextNode($content);
    public function createComment($data);
    public function createCDATASection($data);
    public function createProcessingInstruction($target, $data = null);
    public function createAttribute($name);
    public function createEntityReference($name);
    public function getElementsByTagName($name);
    public function importNode(DOMNode $importedNode, $deep = null);
    public function createElementNS($namespaceURI, $qualifiedName, $value = null);
    public function createAttributeNS($namespaceURI, $qualifiedName);
    public function getElementsByTagNameNS($namespaceURI, $localName);
    public function getElementById($elementId);
    public function adoptNode(DOMNode $source);
    public function normalizeDocument();
    public function renameNode(DOMNode $node, $namespaceURI, $qualifiedName);
    public function load($filename, $options = null);
    public function save($filename, $options = null);
    public function loadXML($source, $options = null);
    public function saveXML(DOMNode $node = null, $options = null);
    public function validate();
    public function xinclude($options = null);
    public function loadHTML($source, $options = 0);
    public function loadHTMLFile($filename, $options = 0);
    public function saveHTML(DOMNode $node = NULL);
    public function saveHTMLFile($filename);
    public function schemaValidate($filename, $options = null);
    public function schemaValidateSource($source, $flags);
    public function relaxNGValidate($filename);
    public function relaxNGValidateSource($source);
    public function registerNodeClass($baseclass, $extendedClass);
}
