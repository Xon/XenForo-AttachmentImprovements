<?php

class SV_AttachmentImprovements_Listener
{
    public static function load_class($class, array &$extend)
    {
        $extend[] = 'SV_AttachmentImprovements_'.$class;
    }
}