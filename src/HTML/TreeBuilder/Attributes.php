<?php declare(strict_types=1);

namespace Souplette\HTML\TreeBuilder;

use Souplette\DOM\Namespaces;

final class Attributes
{
    const ADJUSTED_MATHML_ATTRIBUTES = [
        'definitionurl' => 'definitionURL',
    ];

    const ADJUSTED_FOREIGN_ATTRIBUTES = [
        'xlink:actuate' => ['xlink', 'actuate', Namespaces::XLINK],
        'xlink:arcrole' => ['xlink', 'arcrole', Namespaces::XLINK],
        'xlink:href' => ['xlink', 'href', Namespaces::XLINK],
        'xlink:role' => ['xlink', 'role', Namespaces::XLINK],
        'xlink:show' => ['xlink', 'show', Namespaces::XLINK],
        'xlink:title' => ['xlink', 'title', Namespaces::XLINK],
        'xlink:type' => ['xlink', 'type', Namespaces::XLINK],
        'xml:base' => ['xml', 'base', Namespaces::XML],
        'xml:lang' => ['xml', 'lang', Namespaces::XML],
        'xml:space' => ['xml', 'space', Namespaces::XML],
        'xmlns' => [null, 'xmlns', Namespaces::XMLNS],
        'xmlns:xlink' => ['xmlns', 'xlink', Namespaces::XMLNS],
    ];

    const ADJUSTED_SVG_ATTRIBUTES = [
        'attributename' => 'attributeName',
        'attributetype' => 'attributeType',
        'basefrequency' => 'baseFrequency',
        'baseprofile' => 'baseProfile',
        'calcmode' => 'calcMode',
        'clippathunits' => 'clipPathUnits',
        'diffuseconstant' => 'diffuseConstant',
        'edgemode' => 'edgeMode',
        'filterunits' => 'filterUnits',
        'glyphref' => 'glyphRef',
        'gradienttransform' => 'gradientTransform',
        'gradientunits' => 'gradientUnits',
        'kernelmatrix' => 'kernelMatrix',
        'kernelunitlength' => 'kernelUnitLength',
        'keypoints' => 'keyPoints',
        'keysplines' => 'keySplines',
        'keytimes' => 'keyTimes',
        'lengthadjust' => 'lengthAdjust',
        'limitingconeangle' => 'limitingConeAngle',
        'markerheight' => 'markerHeight',
        'markerunits' => 'markerUnits',
        'markerwidth' => 'markerWidth',
        'maskcontentunits' => 'maskContentUnits',
        'maskunits' => 'maskUnits',
        'numoctaves' => 'numOctaves',
        'pathlength' => 'pathLength',
        'patterncontentunits' => 'patternContentUnits',
        'patterntransform' => 'patternTransform',
        'patternunits' => 'patternUnits',
        'pointsatx' => 'pointsAtX',
        'pointsaty' => 'pointsAtY',
        'pointsatz' => 'pointsAtZ',
        'preservealpha' => 'preserveAlpha',
        'preserveaspectratio' => 'preserveAspectRatio',
        'primitiveunits' => 'primitiveUnits',
        'refx' => 'refX',
        'refy' => 'refY',
        'repeatcount' => 'repeatCount',
        'repeatdur' => 'repeatDur',
        'requiredextensions' => 'requiredExtensions',
        'requiredfeatures' => 'requiredFeatures',
        'specularconstant' => 'specularConstant',
        'specularexponent' => 'specularExponent',
        'spreadmethod' => 'spreadMethod',
        'startoffset' => 'startOffset',
        'stddeviation' => 'stdDeviation',
        'stitchtiles' => 'stitchTiles',
        'surfacescale' => 'surfaceScale',
        'systemlanguage' => 'systemLanguage',
        'tablevalues' => 'tableValues',
        'targetx' => 'targetX',
        'targety' => 'targetY',
        'textlength' => 'textLength',
        'viewbox' => 'viewBox',
        'viewtarget' => 'viewTarget',
        'xchannelselector' => 'xChannelSelector',
        'ychannelselector' => 'yChannelSelector',
        'zoomandpan' => 'zoomAndPan',
    ];
}
