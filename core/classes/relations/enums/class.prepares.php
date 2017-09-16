<?php

namespace Forge\Core\Classes\Relations\Enums;

class Prepares {

    const AS_ARRAY = 0x1;
    const AS_ID = 0x2;
    const AS_OBJECT = 0x3;
    const AS_IDS_RIGHT = 0x4;
    const AS_IDS_LEFT = 0x5;
    const AS_ITEM_LEFT = 0x6;
    const AS_ITEM_RIGHT = 0x7;
    // Used for child-classes (Collection relation)
    const AS_INSTANCE = 0x8;
    const AS_INSTANCE_LEFT = 0x9;
    const AS_INSTANCE_RIGHT = 0x10;

}