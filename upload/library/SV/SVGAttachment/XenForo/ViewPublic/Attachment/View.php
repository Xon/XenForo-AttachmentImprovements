<?php

class SV_SVGAttachment_XenForo_ViewPublic_Attachment_View extends XFCP_SV_SVGAttachment_XenForo_ViewPublic_Attachment_View
{
    public function renderRaw()
    {
        $response = parent::renderRaw();

        $attachment = $this->_params['attachment'];
        $extension = XenForo_Helper_File::getFileExtension($attachment['filename']);
        $imageTypes = array(
            'svg' => 'image/svg+xml',
        );

        if (in_array($extension, array_keys($imageTypes)) &&
            $attachment['thumbnail_width'] && $attachment['thumbnail_height'])
        {
            $this->_response->setHeader('Content-type', $imageTypes[$extension], true);
        }

        return $response;
    }
}