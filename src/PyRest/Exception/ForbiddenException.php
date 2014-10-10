<?php

namespace Pyrite\PyRest\Exception;


class ForbiddenException extends HTTPException
{
    const CODE = 403;
}