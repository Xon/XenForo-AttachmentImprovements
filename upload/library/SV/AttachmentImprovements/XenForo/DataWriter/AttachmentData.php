<?php

class SV_AttachmentImprovements_XenForo_DataWriter_AttachmentData extends XFCP_SV_AttachmentImprovements_XenForo_DataWriter_AttachmentData
{
    protected function _preSave()
    {
        $dimensions = SV_AttachmentImprovements_Globals::$forcedDimensions;
        if ($dimensions)
        {
            $this->set('width', $dimensions['width']);
            $this->set('height', $dimensions['height']);
            $tempThumbFile = SV_AttachmentImprovements_Globals::$tempThumbFile;
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