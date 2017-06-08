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

            if (isset($content_type))
            {
                if (!$input['hash'])
                {
                    $input['hash'] = $this->_input->filterSingle('temp_hash', XenForo_Input::STRING);
                }
                $attachmentData = $this->_getAttachmentData($input);

                // Get extensions
                $extensions = preg_split('/\s+/', trim(XenForo_Application::getOptions()->attachmentImageExtensions));
                $attachmentData['attachmentConstraints']['extensions'] = $extensions;

                // Filter attachments by extensions
                $extensions = array_flip($extensions);
                $attachmentData["existingAttachments"] = array_filter(
                    $attachmentData["existingAttachments"], function($val) use ($extensions)
                    {
                        return isset($extensions[$val["extension"]]);
                    }
                );

                // Merge in attachment data
                $response->params = array_merge($response->params, $attachmentData);
            }
        }

        return $response;
    }


    public function actionMakeNewAttachmentID()
    {
        $hash = $this->_input->filterSingle('hash', XenForo_Input::STRING);
        $contentType = $this->_input->filterSingle('contentType', XenForo_Input::STRING);
        $attachmentID = $this->_input->filterSingle('attachmentID', XenForo_Input::STRING);

        $attachmentModel = $this->_getAttachmentModel();
        $newID = $attachmentModel->makeNewAttachmentID($contentType, $attachmentID, $hash);

        $this->_routeMatch->setResponseType('json');
        return $this->responseView('SV_AttachmentImprovements_XenForo_ViewPublic_Json', '', array("new_id" => $newID));
    }


    protected function _getAttachmentData($input)
    {
        if (!$input)
        {
            return array();
        }

        $this->_assertCanUploadAndManageAttachments($input['hash'], $input['content_type'], $input['content_data']);

        $attachmentModel = $this->_getAttachmentModel();
        $attachmentHandler = $attachmentModel->getAttachmentHandler($input['content_type']); // known to be valid
        $contentId = $attachmentHandler->getContentIdFromContentData($input['content_data']);

        $existingAttachments = $attachmentModel->getRecentAttachments($input);
        $newAttachments = $attachmentModel->getAttachmentsByTempHash($input['hash']);

        foreach ($existingAttachments as $key => $existingAttachment)
        {
            $existingAttachments[$key] = $attachmentModel->prepareAttachment($existingAttachment);
        }

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