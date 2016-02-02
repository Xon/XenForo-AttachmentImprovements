<?php

class SV_XARAttachment_AttachmentHelper
{
    public static function ConvertFilename(&$attachmentFile)
    {
        $xf_code_root = XenForo_Application::getInstance()->getRootDir();
        $internal_data = XenForo_Helper_File::getInternalDataPath();
        $internal_data_uri = self::getInternalDataUrl();
        if ($internal_data_uri && strpos($attachmentFile, $internal_data) === 0)
        {
            $attachmentFile = str_replace($internal_data, $internal_data_uri, $attachmentFile);
            return true;
        }
        else if (strpos($attachmentFile, $xf_code_root) === 0)
        {
            $attachmentFile = str_replace($xf_code_root, '', $attachmentFile);
            return true;
        }
        else
        {
            return false;
        }
    }

    public static function getInternalDataUrl()
    {
        if (!XenForo_Application::isRegistered('config'))
        {
            return '';
        }
        else
        {
            return XenForo_Application::get('config')->internalDataUrl;
        }
    }
}