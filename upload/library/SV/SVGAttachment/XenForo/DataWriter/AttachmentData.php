<?php

class SV_SVGAttachment_XenForo_DataWriter_AttachmentData extends XFCP_SV_SVGAttachment_XenForo_DataWriter_AttachmentData
{
    protected function _preSave()
    {
        $dimensions = SV_SVGAttachment_Globals::$forcedDimensions;
        if ($forcedDimensions)
        {
            $this->set('width', $dimensions['width']);
            $this->set('height', $dimensions['height']);
            $tempThumbFile = SV_SVGAttachment_Globals::$tempThumbFile;
            if ($tempThumbFile)
            {
                $this->setExtraData(self::DATA_TEMP_THUMB_FILE, $tempThumbFile);
                $this->set('thumbnail_width', $dimensions['thumbnail_width']);
                $this->set('thumbnail_height', $dimensions['thumbnail_height']);
            }
            if (!file_exists($tempThumbFile) || !is_readable($tempThumbFile))
            {
                $this->set('thumbnail_width', 0);
                $this->set('thumbnail_height', 0);

                $this->setExtraData(self::DATA_TEMP_THUMB_FILE, '');
                $this->setExtraData(self::DATA_THUMB_DATA, null);
            }
        }
    }
}