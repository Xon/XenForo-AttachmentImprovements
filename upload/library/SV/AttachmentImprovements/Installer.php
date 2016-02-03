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
        $db = XenForo_Application::get('db');

        $db->query("
            DELETE FROM xf_permission_entry
            WHERE permission_id in ('attach_count', 'attach_size')
        ");
        $db->query("
            DELETE FROM xf_permission_entry_content
            WHERE permission_id in ('attach_count', 'attach_size')
        ");

        return true;
    }
}
