<?php

class SV_AttachmentImprovements_XenForo_ControllerAdmin_Tools extends XFCP_SV_AttachmentImprovements_XenForo_ControllerAdmin_Tools
{
    public function actionTriggerDeferred()
    {
        $this->_assertPostOnly();
        $this->assertAdminPermission('rebuildCache');

        $input = $this->_input->filter(array(
            'cache' => XenForo_Input::STRING,
            'options' => XenForo_Input::ARRAY_SIMPLE,
        ));

        if ($input['cache'] == 'AttachmentThumb')
        {
            XenForo_Application::defer('SV_AttachmentImprovements_Deferred_SVGAttachmentThumb', $input['options'], 'Rebuild' . $input['cache'], true);
        }

        return parent::actionTriggerDeferred();
    }
}