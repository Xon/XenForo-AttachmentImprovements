<?php

class SV_SVGAttachment_XenForo_Model_Attachment extends XFCP_SV_SVGAttachment_XenForo_Model_Attachment
{
    public function insertUploadedAttachmentData(XenForo_Upload $file, $userId, array $extra = array())
    {
        $filename = $file->getFileName();
        $extension = XenForo_Helper_File::getFileExtension($filename);
        if ($extension == 'svg')
        {
            if (method_exists('XenForo_Helper_DevelopmentXml', 'scanFile'))
            {
                $svgfile = XenForo_Helper_DevelopmentXml::scanFile($file->getTempFile());
            }
            else
            {
                $svgfile = new SimpleXMLElement($file->getTempFile(), 0, true);
            }
            $width = substr((string)$svgfile['width'], 0, -2);
            $height = substr((string)$svgfile['height'], 0, -2);

            $dimensions = array(
                'width' => $width,
                'height' => $height,
            );
            $tempThumbFile = tempnam(XenForo_Helper_File::getTempDir(), 'xf');
            if ($tempThumbFile)
            {
                copy($file->getTempFile(), $tempThumbFile);
                $attachmentThumbnailDimensions = XenForo_Application::getOptions()->attachmentThumbnailDimensions;
                $dimensions['thumbnail_width'] = ($attachmentThumbnailDimensions > $width)
                                                 ? $attachmentThumbnailDimensions
                                                 : $width;
                $dimensions['thumbnail_height'] = ($attachmentThumbnailDimensions > $height)
                                                 ? $attachmentThumbnailDimensions
                                                 : $height;
                SV_SVGAttachment_Globals::$tempThumbFile = $tempThumbFile;
            }

            SV_SVGAttachment_Globals::$forcedDimensions = $dimensions;
        }

        return parent::insertUploadedAttachmentData($file, $userId, $extra);
    }

    public function getAttachmentThumbnailFilePath(array $data, $externalDataPath = null)
    {
        $extension_swap = null;
        if (!empty($data['filename']))
        {
            $extension = XenForo_Helper_File::getFileExtension($data['filename']);
            if ($extension == 'svg')
            {
                $extension_swap = 'svg';

            }
        }
        $path = parent::getAttachmentThumbnailFilePath($data, $externalDataPath);
        if ($extension_swap)
        {
            return $this->_replaceExtenstion($path, $extension_swap);
        }
        return $path;
    }

    public function getAttachmentThumbnailUrl(array $data)
    {
        $extension_swap = null;
        if (!empty($data['filename']))
        {
            $extension = XenForo_Helper_File::getFileExtension($data['filename']);
            if ($extension == 'svg')
            {
                $extension_swap = 'svg';
            }
        }
        $url = parent::getAttachmentThumbnailUrl($data);
        if ($extension_swap)
        {
            return $this->_replaceExtenstion($url, $extension_swap);
        }
        return $url;
    }

    private function _replaceExtenstion($path, $ext)
    {
        $pos = strrpos($path , '.');
        return $pos === false ? $path : substr_replace($path, $ext, $pos +1);
    }
}