<?php

class SV_AttachmentImprovements_Listener
{
    const AddonNameSpace = 'SV_AttachmentImprovements_';

    public static function load_class($class, array &$extend)
    {
        $extend[] = self::AddonNameSpace.$class;
    }
}