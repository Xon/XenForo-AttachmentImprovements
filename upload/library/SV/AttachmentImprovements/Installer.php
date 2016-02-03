<?php

class SV_AttachmentImprovements_Installer
{
    public static function install($existingAddOn, $addOnData)
    {
        $version = isset($existingAddOn['version_id']) ? $existingAddOn['version_id'] : 0;

        $addonsToUninstall = array('SV_SVGAttachment' => array(),
                                   'SV_XARAttachment' => array());
        SV_Utils_Install::removeOldAddons($addonsToUninstall);

        return true;
    }

    public static function uninstall()
    {
        return true;
    }
}
