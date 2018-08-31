<?php

namespace Pyrite\OutputBuilder;

use Pyrite\Response\ResponseBag;

class BinaryOutputBuilder implements OutputBuilder
{
    const FILEPATH = 'output.binary.file_path';
    const VISIBILITY_PUBLIC = 'output.binary.public';
    const CONTENT_DISPOSITION = 'output.binary.content_disposition';
    const AUTO_ETAG = 'output.binary.auto_etag';
    const AUTO_LAST_MODIFIED = 'output.binary.auto_last_modified';

    public function buildOutput(ResponseBag $bag)
    {
    }
}
