<?php

class SV_SVGAttachment_Listener
{
    const AddonNameSpace = 'SV_SVGAttachment_';

    public static function load_class($class, array &$extend)
    {
        $extend[] = self::AddonNameSpace.$class;
    }
}