<?php

namespace Pyrite\PyRest\Exception;


class ServiceUnavailableException extends HTTPException
{
    const CODE = 503;
}