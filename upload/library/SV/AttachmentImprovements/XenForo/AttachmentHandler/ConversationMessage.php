<?php

class SV_AttachmentImprovements_XenForo_AttachmentHandler_ConversationMessage extends XFCP_SV_AttachmentImprovements_XenForo_AttachmentHandler_ConversationMessage
{
    public function getAttachmentConstraints()
    {
        $constraints = parent::getAttachmentConstraints();
        $visitor = XenForo_Visitor::getInstance();

        $size = $visitor->hasPermission('conversation', 'attach_size');
        if ($size > 0 && $size < $constraints['size'])
        {
            $constraints['size'] = $size * 1024;
        }
        $count = $visitor->hasPermission('conversation', 'attach_count');
        if ($count > 0 && $count < $constraints['count'])
        {
            $constraints['count'] = $count;
        }

        return $constraints;
    }
}