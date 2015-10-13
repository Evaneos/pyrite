<?php

namespace Pyrite\Response;

use Pyrite\OutputBuilder\BinaryOutputBuilder;
use Pyrite\OutputBuilder\StreamedOutputBuilder;

class Utils
{
    /**
     * @param ResponseBag $bag
     * @param string            $callback
     * @param string            $filename
     */
    public static function makeStreamedResponse(ResponseBag $bag, $callback, $filename)
    {
        $bag->setType(ResponseBag::TYPE_STREAMED);
        $bag->setCallback($callback);
        $bag->set(StreamedOutputBuilder::FILENAME, $filename);
    }

    /**
     * @param ResponseBag $bag
     * @param string|null        $filepath
     * @param null|bool        $disposition
     * @param bool|true   $visibility
     * @param bool|false  $autoEtag
     * @param bool|true   $autoLastModified
     */
    public static function makeBinaryResponse(
        ResponseBag $bag,
        $filepath = null,
        $disposition = null,
        $visibility = true,
        $autoEtag = false,
        $autoLastModified = true
    ) {
        $bag->set(BinaryOutputBuilder::AUTO_ETAG, $autoEtag);
        $bag->set(BinaryOutputBuilder::VISIBILITY_PUBLIC, $visibility);
        $bag->set(BinaryOutputBuilder::AUTO_LAST_MODIFIED, $autoLastModified);
        $bag->set(BinaryOutputBuilder::FILEPATH, $filepath);
        $bag->set(BinaryOutputBuilder::CONTENT_DISPOSITION, $disposition);
    }
}
