<?php

class SV_AttachmentImprovements_XenForo_Model_Attachment extends XFCP_SV_AttachmentImprovements_XenForo_Model_Attachment
{
    public function insertUploadedAttachmentData(XenForo_Upload $file, $userId, array $extra = array())
    {
        $filename = $file->getFileName();
        $extension = XenForo_Helper_File::getFileExtension($filename);
        if ($extension == 'svg')
        {
            $svgfile = $this->parseSVG($file->getTempFile());
            if (!empty($svgfile))
            {
                $width = substr((string)$svgfile['width'], 0, -2);
                $height = substr((string)$svgfile['height'], 0, -2);
                if (empty($width) || !is_numeric($width))
                {
                    $width = 0;
                }
                if (empty($height) || !is_numeric($height))
                {
                    $height = 0;
                }
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
                    SV_AttachmentImprovements_Globals::$tempThumbFile = $tempThumbFile;
                }
                SV_AttachmentImprovements_Globals::$forcedDimensions = $dimensions;
            }
        }

        return parent::insertUploadedAttachmentData($file, $userId, $extra);
    }

    public function parseSVG($filename)
    {
        $svgfile = null;
        try
        {
            if (method_exists('XenForo_Helper_DevelopmentXml', 'scanFile'))
            {
                $svgfile = XenForo_Helper_DevelopmentXml::scanFile($filename);
            }
            else
            {
                $svgfile =  new SimpleXMLElement($filename, 0, true);
            }
        }
        catch(Exception $e)
        {
            $svgfile= null;
        }
        if (empty($svgfile))
        {
            return null;
        }
        // check for bad tags
        $options = XenForo_Application::getOptions();
        $badTags = array_fill_keys(explode(',',strtolower($options->SV_AttachImpro_badTags)), true);
        $badAttributes = array_fill_keys(explode(',',strtolower($options->SV_AttachmentImprovements_badAttributes)), true);

        return $this->_scanSVG($svgfile, $badTags, $badAttributes);
    }

    protected function _scanSVG(SimpleXMLElement $node, array $badTags, array $badAttributes)
    {
        $attributes = $node->attributes();
        foreach($attributes as $key => $val)
        {
            if (isset($badAttributes[strtolower($key)]))
            {
                return null;
            }
            $val = $this->_scanSVG($val, $badTags, $badAttributes);
            if (empty($val))
            {
                return null;
            }
        }

        $children = $node->children();
        foreach($children as $key => $val)
        {
            if (isset($badTags[strtolower($key)]))
            {
                return null;
            }
            $val = $this->_scanSVG($val, $badTags, $badAttributes);
            if (empty($val))
            {
                return null;
            }
        }
        return $node;
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