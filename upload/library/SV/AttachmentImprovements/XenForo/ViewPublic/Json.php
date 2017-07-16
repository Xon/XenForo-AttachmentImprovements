<?php

class SV_AttachmentImprovements_XenForo_ViewPublic_Json extends XenForo_ViewPublic_Base
{
    public function renderJson()
    {
        if (isset($this->_params['_attachment']))
        {
            $attachment = $this->_params['_attachment'];
            unset($this->_params['_attachment']);

            $template = $this->createTemplateObject('attachment_editor_attachment', array('attachment' => $attachment));
            $templateHtml = $template->render();
            $this->_params['uploaderJson'] = array('templateHtml' => $templateHtml, 'attachment_id' => $attachment['attachment_id']);
        }
        return json_encode($this->_params);
    }
}