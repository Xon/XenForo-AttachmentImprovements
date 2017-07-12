<?php

class SV_AttachmentImprovements_XenForo_Model_Attachment extends XFCP_SV_AttachmentImprovements_XenForo_Model_Attachment
{
    public function insertUploadedAttachmentData(XenForo_Upload $file, $userId, array $extra = array())
    {
        $filename = $file->getFileName();
        $extension = XenForo_Helper_File::getFileExtension($filename);
        if ($extension == 'svg')
        {
            list($svgfile, $dimensions) = $this->extractDimensionsForSVG($file->getTempFile(), XenForo_Application::getOptions()->SV_RejectAttachmentWithBadTags);
            if (!empty($svgfile) && !empty($dimensions))
            {
                if ($dimensions['thumbnail_width'] && $dimensions['thumbnail_height'])
                {
                    $tempThumbFile = tempnam(XenForo_Helper_File::getTempDir(), 'xf');
                    if ($tempThumbFile)
                    {
                        // update the width/height attributes
                        $svgfile['width'] = (string)$dimensions['thumbnail_width'];
                        $svgfile['height'] = (string)$dimensions['thumbnail_height'];
                        $svgfile->asXML($tempThumbFile);
                        SV_AttachmentImprovements_Globals::$tempThumbFile = $tempThumbFile;
                    }
                }

                SV_AttachmentImprovements_Globals::$forcedDimensions = $dimensions;
            }
        }

        return parent::insertUploadedAttachmentData($file, $userId, $extra);
    }

    protected function extractDimension($svgfile, $dimensionName)
    {
        $dimension = (string)$svgfile[$dimensionName];
        if (strrpos($dimension , 'px') === strlen($dimension) - 2)
        {
            $dimension = substr($dimension, 0, -2);
        }
        return intval($dimension);
    }

    public function extractDimensionsForSVG($filename, $throwOnBadSVG = true)
    {
        $svgfile = $this->parseSVG($filename);
        if (empty($svgfile))
        {
            if ($throwOnBadSVG)
            {
                throw new XenForo_Exception(new XenForo_Phrase('sv_bad_svg_data'), true);
            }
            return array(null, null);
        }

        $width = $this->extractDimension($svgfile, 'width');
        $height = $this->extractDimension($svgfile, 'height');

        $dimensions = array(
            'width' => $width,
            'height' => $height,
            'thumbnail_width' => 0,
            'thumbnail_height' => 0,
        );

        if ($width && $height)
        {
            $attachmentThumbnailDimensions = XenForo_Application::getOptions()->attachmentThumbnailDimensions;
            $aspectRatio = $width / $height;
            if ($width > $height && $width > $attachmentThumbnailDimensions)
            {
                $dimensions['thumbnail_width'] = $attachmentThumbnailDimensions;
                $dimensions['thumbnail_height'] = intval($attachmentThumbnailDimensions / $aspectRatio);
            }
            else if ($height > $attachmentThumbnailDimensions)
            {
                $dimensions['thumbnail_height'] = $attachmentThumbnailDimensions;
                $dimensions['thumbnail_width'] = intval($attachmentThumbnailDimensions * $aspectRatio);
            }
        }
        return array($svgfile, $dimensions);
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
            XenForo_Error::logException($e, false);
            $svgfile = null;
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

    public function getRecentAttachments($input, $limit = 5, $offset = 0, array $viewingUser = null)
    {
        $this->standardizeViewingUserReference($viewingUser);

        // xf_attachment_data - attachment data
        // xf_attachment - link between attachment data & content
        return $this->fetchAllKeyed($this->_getDb()->limit('
			SELECT attachment.*,
				' . self::$dataColumns . '
			FROM xf_attachment AS attachment
			INNER JOIN xf_attachment_data AS data ON
				(data.data_id = attachment.data_id)
            WHERE data.user_id = ?
            GROUP BY data.file_hash
            ORDER BY attachment.attach_date DESC
        ', $limit, $offset), 'attachment_id', array($viewingUser['user_id']));
    }


    public function makeNewAttachment($oldAttachmentID, $hash)
    {
        if (empty($hash))
        {
            return null;
        }
        $oldAttachment = $this->getAttachmentById($oldAttachmentID);
        if (empty($oldAttachment))
        {
            return null;
        }

        // re-use the existing attachment on this content
        if ($oldAttachment['temp_hash'] == $hash)
        {
            return $oldAttachment;
        }

        $dataId = $oldAttachment['data_id'];
        $existingAttachmentId = $this->_getDb()->fetchOne('
            select attachment_id
            from xf_attachment
            where data_id = ? and temp_hash = ?
            limit 1
        ', array($dataId, $hash));
        if ($existingAttachmentId)
        {
            return $this->getAttachmentById($existingAttachmentId);
        }

        /** @var XenForo_DataWriter_Attachment $attachmentDw */
        $attachmentDw = XenForo_DataWriter::create('XenForo_DataWriter_Attachment');
        $attachmentDw->bulkSet(array(
            'data_id'      => $dataId,
            'temp_hash'    => $hash
        ));
        $attachmentDw->save();

        return $this->getAttachmentById($attachmentDw->get('attachment_id'));
    }

    private function _replaceExtenstion($path, $ext)
    {
        $pos = strrpos($path , '.');
        return $pos === false ? $path : substr_replace($path, $ext, $pos +1);
    }
}