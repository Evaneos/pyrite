<?php

namespace Pyrite\OutputBuilder;

use Pyrite\Response\ResponseBag;

class StreamedOutputBuilder implements OutputBuilder
{
    const FILENAME = 'output.streamed.file_download_name';

    /** @see RFC6266 */
    const ATTACHMENT_DISPOSITION = 'output.streamed.attachment_disposition';

    public function getHeaders(ResponseBag $bag)
    {
        if(!$bag->has(self::FILENAME)){
            throw new \Exception('Mission filename for streamed response');
        }

        $bag->addHeader('Content-Type', 'application/force-dowload');
        $bag->addHeader('Content-Disposition', sprintf('%s; filename="%s"', $bag->get(self::ATTACHMENT_DISPOSITION, 'attachment'), $bag->get(self::FILENAME)));

        return $bag->getHeaders();
    }

    public function buildOutput(ResponseBag $bag)
    {

    }
}
