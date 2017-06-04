<?php

class SV_AttachmentImprovements_XenForo_ControllerPublic_Editor extends XFCP_SV_AttachmentImprovements_XenForo_ControllerPublic_Editor
{
    public function actionDialog()
    {
        $response = parent::actionDialog();

        $dialog = $this->_input->filterSingle('dialog', XenForo_Input::STRING);

        if ($dialog == "image")
        {
            $input = $this->_input->filter(array(
                'hash' => XenForo_Input::STRING,
                'content_type' => XenForo_Input::STRING,
                'content_data' => array(XenForo_Input::UINT, 'array' => true),
                'key' => XenForo_Input::STRING
            ));
            // Assumption: Always has hash as opposed to temp_hash

            $attachmentData = $this->_getAttachmentData($input);
            $response->params = array_merge($response->params, $attachmentData);
            // $response->params["attachmentParams"] = $attachmentData;
        }

        return $response;
        // Next step: change template
    }


    protected function _getAttachmentData($input)
    {
        if (!$input)
        {
            return array();
        }

        // XenForo_Error::debug(json_encode($attachmentParams));
        $this->_assertCanUploadAndManageAttachments($input['hash'], $input['content_type'], $input['content_data']);

        $attachmentModel = $this->_getAttachmentModel();
        $attachmentHandler = $attachmentModel->getAttachmentHandler($input['content_type']); // known to be valid
        $contentId = $attachmentHandler->getContentIdFromContentData($input['content_data']);

        $existingAttachments = ($contentId
            ? $attachmentModel->getAttachmentsByContentId($input['content_type'], $contentId)
            : array()
        );
        $newAttachments = $attachmentModel->getAttachmentsByTempHash($input['hash']);

        $constraints = $attachmentHandler->getAttachmentConstraints();
        if ($constraints['count'] <= 0)
        {
            $canUpload = true;
            $remainingUploads = true;
        }
        else
        {
            $remainingUploads = $constraints['count'] - (count($existingAttachments) + count($newAttachments));
            $canUpload = ($remainingUploads > 0);
        }

        return array(
            'attachmentConstraints' => $constraints,
            'existingAttachments' => $existingAttachments,
            'newAttachments' => $newAttachments,
            'recentAttachments' => $attachmentModel->getRecentAttachments($input),

            'canUpload' => $canUpload,
            'remainingUploads' => $remainingUploads,

            'hash' => $input['hash'],
            'contentType' => $input['content_type'],
            'contentData' => $input['content_data'],
            'attachmentParams' => array(
                'hash' => $input['hash'],
                'content_type' => $input['content_type'],
                'content_data' => $input['content_data']
            ),
            'key' => $input['key']
        );
    }

    /**
     * Asserts that the viewing user can upload and manage attachments.
     *
     * @param string $hash Unique hash
     * @param string $contentType
     * @param array $contentData
     */
    protected function _assertCanUploadAndManageAttachments($hash, $contentType, array $contentData)
    {
        if (!$hash)
        {
            throw $this->getNoPermissionResponseException();
        }

        $attachmentHandler = $this->_getAttachmentModel()->getAttachmentHandler($contentType);
        if (!$attachmentHandler || !$attachmentHandler->canUploadAndManageAttachments($contentData))
        {
             throw $this->getNoPermissionResponseException();
        }
    }


    /**
     * @return XenForo_Model_Attachment
     */
    protected function _getAttachmentModel()
    {
        return $this->getModelFromCache('XenForo_Model_Attachment');
    }
}