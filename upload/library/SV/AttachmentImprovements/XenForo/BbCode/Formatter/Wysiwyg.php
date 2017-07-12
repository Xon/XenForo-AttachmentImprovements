<?php

function jTraceEx($e, $seen=null) {
    $starter = $seen ? 'Caused by: ' : '';
    $result = array();
    if (!$seen) $seen = array();
    $trace  = $e->getTrace();
    $prev   = $e->getPrevious();
    $result[] = sprintf('%s%s: %s', $starter, get_class($e), $e->getMessage());
    $file = $e->getFile();
    $line = $e->getLine();
    while (true) {
        $current = "$file:$line";
        if (is_array($seen) && in_array($current, $seen)) {
            $result[] = sprintf(' ... %d more', count($trace)+1);
            break;
        }
        $result[] = sprintf(' at %s%s%s(%s%s%s)',
                                    count($trace) && array_key_exists('class', $trace[0]) ? str_replace('\\', '.', $trace[0]['class']) : '',
                                    count($trace) && array_key_exists('class', $trace[0]) && array_key_exists('function', $trace[0]) ? '.' : '',
                                    count($trace) && array_key_exists('function', $trace[0]) ? str_replace('\\', '.', $trace[0]['function']) : '(main)',
                                    $line === null ? $file : basename($file),
                                    $line === null ? '' : ':',
                                    $line === null ? '' : $line);
        if (is_array($seen))
            $seen[] = "$file:$line";
        if (!count($trace))
            break;
        $file = array_key_exists('file', $trace[0]) ? $trace[0]['file'] : 'Unknown Source';
        $line = array_key_exists('file', $trace[0]) && array_key_exists('line', $trace[0]) && $trace[0]['line'] ? $trace[0]['line'] : null;
        array_shift($trace);
    }
    $result = join("\n", $result);
    if ($prev)
        $result  .= "\n" . jTraceEx($prev, $seen);

    return $result;
}

class SV_AttachmentImprovements_XenForo_BbCode_Formatter_Wysiwyg extends XFCP_SV_AttachmentImprovements_XenForo_BbCode_Formatter_Wysiwyg
{
    public function getTags()
    {
        $this->_undisplayableTags = array_diff($this->_undisplayableTags, array('attach'));
        return parent::getTags();
    }

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
            $e = new Exception();
			return "<pre>".str_replace('/var/www/sites/dev.sufficientvelocity.com/html/', '', jTraceEx($e)). "</pre>".$this->renderInvalidTag($tag, $rendererStates);
		}

        $attachment = $params['attachments'][$id];
        // no thumbnail == not an image
        if (empty($attachment['thumbnailUrl']))
        {
            return var_export($attachment, true) . $this->renderInvalidTag($tag, $rendererStates);
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