<?php

namespace Pyrite\PyRest\Exception;

class MethodNotAllowedException extends HTTPException
{
    const CODE = 405;
}
