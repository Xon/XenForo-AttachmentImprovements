<?php

class SV_AttachmentImprovements_XenForo_ViewPublic_Attachment_View extends XFCP_SV_AttachmentImprovements_XenForo_ViewPublic_Attachment_View
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
            'svg'  => 'image/svg+xml',
            'gif'  => 'image/gif',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpe'  => 'image/jpeg',
            'png'  => 'image/png'
        );

        if (isset($imageTypes[$extension]) && ($attachment['width'] && $attachment['height']))
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

        $options = XenForo_Application::getOptions();
        if ($options->SV_AttachImpro_XAR)
        {
            if (SV_AttachmentImprovements_AttachmentHelper::ConvertFilename($attachmentFile))
            {
                if (XenForo_Application::debugMode() && $options->SV_AttachImpro_log)
                {
                    XenForo_Error::debug('X-Accel-Redirect:' . $attachmentFile);
                }
                $this->_response->setHeader('X-Accel-Redirect', $attachmentFile);
                return '';
            }
            if (XenForo_Application::debugMode() && $options->SV_AttachImpro_log)
            {
                XenForo_Error::debug('X-Accel-Redirect skipped');
            }
        }
        return new XenForo_FileOutput($attachmentFile);
    }
}