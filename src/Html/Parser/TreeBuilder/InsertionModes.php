<?php declare(strict_types=1);

namespace Souplette\Html\Parser\TreeBuilder;

final class InsertionModes
{
    const INITIAL = 0;
    const BEFORE_HTML = 1;
    const BEFORE_HEAD = 2;
    const IN_HEAD = 3;
    const IN_HEAD_NOSCRIPT = 4;
    const AFTER_HEAD = 5;
    const IN_BODY = 6;
    const TEXT = 7;
    const IN_TABLE = 8;
    const IN_TABLE_TEXT = 9;
    const IN_CAPTION = 10;
    const IN_COLUMN_GROUP = 11;
    const IN_TABLE_BODY = 12;
    const IN_ROW = 13;
    const IN_CELL = 14;
    const IN_SELECT = 15;
    const IN_SELECT_IN_TABLE = 16;
    const IN_TEMPLATE = 17;
    const AFTER_BODY = 18;
    const IN_FRAMESET = 19;
    const AFTER_FRAMESET = 20;
    const AFTER_AFTER_BODY = 21;
    const AFTER_AFTER_FRAMESET = 22;
    const IN_FOREIGN_CONTENT = 23;
}
