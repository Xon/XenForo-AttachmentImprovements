<?php

class SV_AttachmentImprovements_XenForo_BbCode_Formatter_Base extends XFCP_SV_AttachmentImprovements_XenForo_BbCode_Formatter_Base
{
    public function renderTagAttach(array $tag, array $rendererStates)
    {       
		$id = intval($this->stringifyTree($tag['children']));
        //debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        //echo "<br/>";
		if (!$id ||
            !$this->_view ||
            !($params = $this->_view->getParams()) ||
            empty($params['attachments'][$id]))
		{
			return '1'.parent::renderTagAttach($tag, $rendererStates);
		}

        $attachment = $params['attachments'][$id];
        // no thumbnail == not an image
        if (empty($attachment['thumbnailUrl']))
        {
            return $this->renderInvalidTag($tag, $rendererStates);
        }

        if (strtolower($tag['option']) == 'full')
        {
            $css = 'full';
            $url = XenForo_Link::buildPublicLink('full:attachments', $attachment);
        }
        else
        {
            $css = 'Thumb';
            $url = $attachment['thumbnailUrl'];
        }

        return '<img src="' . $url . '" class="attachFull bbCodeImage" alt="attach' . $css . $id . '">';
    }
}