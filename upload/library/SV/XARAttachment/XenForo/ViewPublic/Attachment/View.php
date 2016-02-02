<?php

class SV_XARAttachment_XenForo_ViewPublic_Attachment_View extends XFCP_SV_XARAttachment_XenForo_ViewPublic_Attachment_View
{
    public function renderRaw()
    {
        $attachment = $this->_params['attachment'];

        if (!headers_sent() && function_exists('header_remove'))
        {
            header_remove('Expires');
            header('Cache-control: private');
        }

        $extension = XenForo_Helper_File::getFileExtension($attachment['filename']);
        $imageTypes = array(
            'gif' => 'image/gif',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpe' => 'image/jpeg',
            'png' => 'image/png'
        );

        if (in_array($extension, array_keys($imageTypes)))
        {
            $this->_response->setHeader('Content-type', $imageTypes[$extension], true);
            $this->setDownloadFileName($attachment['filename'], true);
        }
        else
        {
            $this->_response->setHeader('Content-type', 'application/octet-stream', true);
            $this->setDownloadFileName($attachment['filename']);
        }

        $this->_response->setHeader('ETag', '"' . $attachment['attach_date'] . '"', true);
        $this->_response->setHeader('Content-Length', $attachment['file_size'], true);
        $this->_response->setHeader('X-Content-Type-Options', 'nosniff');

        $attachmentFile = $this->_params['attachmentFile'];
        if (SV_XARAttachment_AttachmentHelper::ConvertFilename($attachmentFile))
        {
            if (XenForo_Application::debugMode() && XenForo_Application::getOptions()->sv_xar_log)
            {
                XenForo_Error::debug('X-Accel-Redirect:' . $attachmentFile);
            }
            $this->_response->setHeader('X-Accel-Redirect', $attachmentFile);
            return '';
        }
        if (XenForo_Application::debugMode() && XenForo_Application::getOptions()->sv_xar_log)
        {
            XenForo_Error::debug('X-Accel-Redirect skipped');
        }
        return new XenForo_FileOutput($this->_params['attachmentFile']);
    }
}