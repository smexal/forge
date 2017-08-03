<?php

namespace Forge\Core\Classes\Relations\Enums;

class Directions {

    const DIRECTED = 0x1;
    const REVERSED = 0x2;
    const BIDIRECT = 0x3;

    // Reversed should not be used here as it is basically just a DIR_DIRECTED 
    const DIRS = [0x1, 0x3];
}