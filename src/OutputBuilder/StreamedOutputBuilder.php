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
        if (!$bag->has(self::FILENAME)) {
            throw new \Exception('Missing filename for streamed response');
        }

        return [
            'X-Sendfile: '. $bag->get(self::FILENAME),
            'X-Accel-Buffering: no',
            'Transfer-Encoding: chunked',
            'Content-Type: application/force-download',
            'Content-Disposition: '. sprintf('%s; filename="%s"', $bag->get(self::ATTACHMENT_DISPOSITION, 'attachment'), $bag->get(self::FILENAME))
        ];
    }

    public function buildOutput(ResponseBag $bag)
    {
    }
}
