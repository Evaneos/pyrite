<?php

namespace Pyrite\OutputBuilder;

use Pyrite\Response\ResponseBag;

class StreamedOutputBuilder implements OutputBuilder
{
    const FILENAME = 'output.streamed.file_download_name';

    /** @see RFC6266 */
    const ATTACHMENT_DISPOSITION = 'output.streamed.attachment_disposition';

    /**
     * @param ResponseBag $bag
     *
     * @throws \Exception
     */
    public function buildOutput(ResponseBag $bag)
    {
        if (!$bag->has(self::FILENAME)) {
            throw new \Exception('Missing filename for streamed response');
        }

        $bag->addHeader('X-Sendfile', $bag->get(self::FILENAME));
        $bag->addHeader('X-Accel-Buffering', 'no');
        $bag->addHeader('Transfer-Encoding', 'chunked');
        $bag->addHeader('Content-Type', 'application/force-download');
        $bag->addHeader('Content-Disposition', sprintf('%s; filename="%s"', $bag->get(self::ATTACHMENT_DISPOSITION, 'attachment'), $bag->get(self::FILENAME)));
    }
}
