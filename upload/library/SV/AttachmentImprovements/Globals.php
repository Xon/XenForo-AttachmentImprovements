<?php

// This class is used to encapsulate global state between layers without using $GLOBAL[] or
// relying on the consumer being loaded correctly by the dynamic class autoloader
class SV_AttachmentImprovements_Globals
{
    public static $tempThumbFile = null;
    public static $forcedDimensions = null;

    private function __construct() {}
}
