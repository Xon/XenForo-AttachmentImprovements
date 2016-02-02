<?php

class SV_AttachmentImprovements_XenForo_AttachmentHandler_Post extends XFCP_SV_AttachmentImprovements_XenForo_AttachmentHandler_Post
{
    public function getAttachmentConstraints()
    {
        $constraints = parent::getAttachmentConstraints();
        $visitor = XenForo_Visitor::getInstance();

        if (SV_AttachmentImprovements_Globals::$nodePermissionsForAttachments)
        {
            $nodePermissions = SV_AttachmentImprovements_Globals::$nodePermissionsForAttachments;

            $size = XenForo_Permission::hasContentPermission($nodePermissions, 'attach_size');
            if ($size > 0 && $size < $constraints['size'])
            {
                $constraints['size'] = $size * 1024;
            }
            $count = XenForo_Permission::hasContentPermission($nodePermissions, 'attach_count');
            if ($count > 0 && $count < $constraints['count'])
            {
                $constraints['count'] = $count;
            }
        }

        return $constraints;
    }
}