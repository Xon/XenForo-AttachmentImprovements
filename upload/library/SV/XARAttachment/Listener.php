<?php

class SV_XARAttachment_Listener
{
    const AddonNameSpace = 'SV_XARAttachment_';

    public static function load_class($class, array &$extend)
    {
        $extend[] = self::AddonNameSpace.$class;
    }
}