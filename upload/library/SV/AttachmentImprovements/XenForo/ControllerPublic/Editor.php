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

            if (empty($input['content_type']))
            {
                return $response;
            }

            $attachmentModel = $this->_getAttachmentModel();
            $attachmentHandler = $attachmentModel->getAttachmentHandler($input['content_type']);
            if (!$attachmentHandler || !$attachmentHandler->canUploadAndManageAttachments($input['content_data']))
            {
                return $response;
            }
            if (!$input['hash'])
            {
                $input['hash'] = $this->_input->filterSingle('temp_hash', XenForo_Input::STRING);
            }
            $attachmentData = $this->_getAttachmentData($input);
            $attachmentData['href'] = $attachmentModel->sv_getAttachmentUploadURL($input);

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

        return $response;
    }

    public function actionMakeNewAttachment()
    {
        $this->_routeMatch->setResponseType('json');
        $input = $this->_input->filter(array(
            'hash' => XenForo_Input::STRING,
            'content_type' => XenForo_Input::STRING,
            'content_data' => array(XenForo_Input::UINT, 'array' => true),
            'attachmentID' => XenForo_Input::STRING
        ));

        $this->_assertCanUploadAndManageAttachments($input['hash'], $input['content_type'], $input['content_data']);

        $attachmentModel = $this->_getAttachmentModel();
        $attachment = $attachmentModel->makeNewAttachment($input['attachmentID'], $input['hash'], $input['content_type']);
        if (empty($attachment))
        {
            // todo: 404
            return $this->responseView('SV_AttachmentImprovements_XenForo_ViewPublic_Json', '', array());
        }

        // Build response for attachment uploader
        $attachment["hash"] = $attachment["temp_hash"];
        $attachment = $attachmentModel->prepareAttachment($attachment);

        return $this->responseView('SV_AttachmentImprovements_XenForo_ViewPublic_Json', '', array(
            'id' => $attachment['attachment_id'],
            'url' => XenForo_Link::buildPublicLink('full:attachments', $attachment),
            // _attachment is remapped to uploaderJson when rendered
            '_attachment' => $attachment,
        ));
    }


    public function actionGetRecentAttachments()
    {
        $numberOfExisting = $this->_input->filterSingle("existing", XenForo_Input::UINT);
        $input = $this->_input->filter(array(
            'hash' => XenForo_Input::STRING,
            'content_type' => XenForo_Input::STRING,
            'content_data' => array(XenForo_Input::UINT, 'array' => true),
            'key' => XenForo_Input::STRING
        ));

        if (!$input['hash'])
        {
            $input['hash'] = $this->_input->filterSingle('temp_hash', XenForo_Input::STRING);
        }

        $this->_assertCanUploadAndManageAttachments($input['hash'], $input['content_type'], $input['content_data']);

        $attachmentModel = $this->_getAttachmentModel();

        $numberToLoad = 10;
        $nextAttachments = $attachmentModel->getRecentAttachments($input, $numberToLoad, $numberOfExisting);

        foreach ($nextAttachments as $key => $nextAttachment)
        {
            $nextAttachments[$key] = $attachmentModel->prepareAttachment($nextAttachment);
        }

        return $this->responseView('XenForo_ViewPublic_Base',
            'editor_dialog_image_improvements_multi_attachments',
            array(
                "attachments" => $nextAttachments,
                "loadedAll" => sizeof($nextAttachments) != $numberToLoad
            )
        );
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

        $existingAttachments = $attachmentModel->getRecentAttachments($input, 4);
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