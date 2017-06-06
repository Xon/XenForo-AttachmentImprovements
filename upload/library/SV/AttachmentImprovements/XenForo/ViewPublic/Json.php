<?php

class SV_AttachmentImprovements_XenForo_ViewPublic_Json extends XenForo_ViewPublic_Base
{
    public function renderJson()
    {
        return json_encode($this->_params);
    }
}