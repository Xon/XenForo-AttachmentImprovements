<?php

class SV_AttachmentImprovements_Installer
{
    const AddonNameSpace = 'SV_AttachmentImprovements_';

    public static function install($existingAddOn, $addOnData)
    {
        $version = isset($existingAddOn['version_id']) ? $existingAddOn['version_id'] : 0;

        if ($version && $version < 1000300)
        {
            // migrate to remove the 'use' from the bad tag list
            $tags = XenForo_Application::getOption()->SV_AttachImpro_badTags;
            $tags = explode(',', $tags);
            if(($key = array_search('user', $tags)) !== false)
            {
                unset($tags[$key]);
            }
            $dw = XenForo_DataWriter::create('XenForo_DataWriter_Option', XenForo_DataWriter::ERROR_SILENT);
            $dw->setExistingData('SV_AttachImpro_badTags');
            $dw->setOption(XenForo_DataWriter_Option::OPTION_REBUILD_CACHE, false);
            $dw->set('option_value', implode(',', $tags));
            $dw->save();

            XenForo_Application::defer(self::AddonNameSpace.'Deferred_SVGAttachmentThumb', array());
        }
        else if ($version == 0)
        {
            $addon = XenForo_Model::create('XenForo_Model_AddOn')->getAddOnById('SV_SVGAttachment');
            if (!empty($addon))
            {
                XenForo_Application::defer(self::AddonNameSpace.'Deferred_SVGAttachmentThumb', array());
            }
        }

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
