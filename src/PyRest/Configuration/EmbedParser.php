<?php

namespace Pyrite\PyRest\Configuration;

use Symfony\Component\HttpFoundation\Request;

class EmbedParser implements Parser
{
    const NAME = __CLASS__;

    public function parse(Request $request)
    {
        if($embed = $request->query->get('embed'))
        {
            return $this->doParse($embed);
        }
        else {
            return array();
        }
    }

    protected function doParse($string)
    {
        $explodedEmbed = explode(",", $string);
        $result = array();

        foreach($explodedEmbed as $key => $value) {
            $arr = explode('.', $value);
            $count = count($arr);

            $pointer = &$result;
            while(false !== current($arr)) {
                $current = current($arr);
                if (!array_key_exists($current, $pointer)) {
                    $pointer[$current] = array();
                }
                $pointer = &$pointer[$current];
                next($arr);
            }
            unset($pointer);
        }
        ksort($result);

        return $result;
    }
}