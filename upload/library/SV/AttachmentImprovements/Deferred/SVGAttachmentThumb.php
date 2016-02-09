<?php

class SV_AttachmentImprovements_Deferred_SVGAttachmentThumb extends XenForo_Deferred_Abstract
{
    public function execute(array $deferred, array $data, $targetRunTime, &$status)
    {
        $data = array_merge(array(
            'batch' => 100,
            'position' => 0
        ), $data);

        /* @var $attachmentModel XenForo_Model_Attachment */
        $attachmentModel = XenForo_Model::create('XenForo_Model_Attachment');

        $s = microtime(true);

        $dataIds = $attachmentModel->getAttachmentDataIdsInRange($data['position'], $data['batch']);
        if (sizeof($dataIds) == 0)
        {
            return false;
        }

        foreach ($dataIds AS $dataId)
        {
            $data['position'] = $dataId;

            $dw = XenForo_DataWriter::create('XenForo_DataWriter_AttachmentData', XenForo_DataWriter::ERROR_SILENT);
            if ($dw->setExistingData($dataId)
                && $dw->get('width')
                && XenForo_Helper_File::getFileExtension($dw->get('filename')) == 'svg'
            )
            {
                $attach = $dw->getMergedData();
                $attachFile = $attachmentModel->getAttachmentDataFilePath($attach);
                list($svgfile, $dimensions) = $attachmentModel->extractDimensionsForSVG($attachFile);
                if ($svgfile && $dimensions)
                {
                    if ($dimensions['thumbnail_width'] && $dimensions['thumbnail_height'])
                    {
                        // update the width/height attributes
                        $svgfile['width'] = (string)$dimensions['thumbnail_width'];
                        $svgfile['height'] = (string)$dimensions['thumbnail_height'];
                        $thumbData = $svgfile->asXML();
                    }
                    else
                    {
                        // no resize necessary, use the original
                        $thumbData = file_get_contents($attachFile);
                    }

                    $dw->set('thumbnail_width', $dimensions['thumbnail_width']);
                    $dw->set('thumbnail_height', $dimensions['thumbnail_height']);
                    $dw->setExtraData(XenForo_DataWriter_AttachmentData::DATA_THUMB_DATA, $thumbData);
                    try
                    {
                        $dw->save();
                    }
                    catch (Exception $e)
                    {
                        XenForo_Error::logException($e, false, "Thumb rebuild for #$dataId: ");
                    }

                    unset($svgfile);
                }
            }

            if ($targetRunTime && microtime(true) - $s > $targetRunTime)
            {
                break;
            }
        }

        $actionPhrase = new XenForo_Phrase('rebuilding');
        $typePhrase = new XenForo_Phrase('attachment_thumbnails');
        $status = sprintf('%s... %s (%s)', $actionPhrase, $typePhrase, XenForo_Locale::numberFormat($data['position']));

        return $data;
    }

    public function canCancel()
    {
        return true;
    }
}
