<?php

class SV_SVGAttachment_XenForo_DataWriter_AttachmentData extends XFCP_SV_SVGAttachment_XenForo_DataWriter_AttachmentData
{
    protected function _preSave()
    {
        $dimensions = SV_SVGAttachment_Globals::$forcedDimensions;
        if ($dimensions)
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
        }
        parent::_preSave();
    }
}