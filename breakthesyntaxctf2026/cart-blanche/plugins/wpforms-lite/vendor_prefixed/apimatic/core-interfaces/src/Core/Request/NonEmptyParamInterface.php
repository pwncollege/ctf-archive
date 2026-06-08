<?php

namespace WPForms\Vendor\CoreInterfaces\Core\Request;

interface NonEmptyParamInterface extends ParamInterface
{
    public function requiredNonEmpty();
}
