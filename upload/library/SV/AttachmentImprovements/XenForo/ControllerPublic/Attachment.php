<?php

class SV_AttachmentImprovements_XenForo_ControllerPublic_Attachment extends XFCP_SV_AttachmentImprovements_XenForo_ControllerPublic_Attachment
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

    // public function actionDoUpload()
    // {
    //     $response = parent::actionDoUpload();

    //     $dialogUpload = $this->_input->filterSingle('imageDialogUploader', XenForo_Input::BOOLEAN);
    //     if ($dialogUpload && is_a($response, "XenForo_ControllerResponse_View"))
    //     {
    //         return $this->responseView('XenForo_ViewPublic_Attachment_DoUpload', '', $response->params);
    //     }

    //     return $response;
    // }

    protected function _getAttachmentData($input)
    {
        $params = parent::_getAttachmentData($input);
        if (!empty($params['canUpload']))
        {
            $params['recentAttachments'] = $attachmentModel->getRecentAttachments($input);
        }
        return $params;
    }
}