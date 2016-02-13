<?php

class SV_AttachmentImprovements_XenForo_ControllerAdmin_Attachment extends XFCP_SV_AttachmentImprovements_XenForo_ControllerAdmin_Attachment
{
    public function actionIndex()
    {
        $eTag = $this->_request->getServer('HTTP_IF_NONE_MATCH');
        if ($eTag && substr($eTag, 0, 2) == 'W/')
        {
            $_SERVER['HTTP_IF_NONE_MATCH'] = substr($eTag, 2);
        }

        return parent::actionIndex();
    }
}