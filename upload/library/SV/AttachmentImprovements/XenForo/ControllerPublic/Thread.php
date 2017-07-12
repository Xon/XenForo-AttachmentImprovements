<?php

class SV_AttachmentImprovements_XenForo_ControllerPublic_Thread extends XFCP_SV_AttachmentImprovements_XenForo_ControllerPublic_Thread
{
    public function actionReplyPreview()
    {
        $response = parent::actionReplyPreview();

        if ($response instanceof XenForo_ControllerResponse_View)
        {
            $tempHash = $this->_input->filterSingle('attachment_hash', XenForo_Input::STRING);
            $attachmentModel = $this->getModelFromCache('XenForo_Model_Attachment');
            $response->params['attachments'] = $attachmentModel->prepareAttachments($attachmentModel->getAttachmentsByTempHash($tempHash));
        }

        return $response;
    }
}