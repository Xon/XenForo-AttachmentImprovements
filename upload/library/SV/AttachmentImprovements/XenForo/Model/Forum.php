<?php

class SV_AttachmentImprovements_XenForo_Model_Forum extends XFCP_SV_AttachmentImprovements_XenForo_Model_Forum
{
    public function canUploadAndManageAttachment(array $forum, &$errorPhraseKey = '', array $nodePermissions = null, array $viewingUser = null)
    {
        SV_AttachmentImprovements_Globals::$nodePermissionsForAttachments = $nodePermissions;
        return parent::canUploadAndManageAttachment($forum, $errorPhraseKey, $nodePermissions, $viewingUser);
    }
}