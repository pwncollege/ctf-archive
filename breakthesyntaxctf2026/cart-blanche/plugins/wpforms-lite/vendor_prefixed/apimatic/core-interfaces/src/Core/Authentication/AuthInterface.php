<?php

namespace WPForms\Vendor\CoreInterfaces\Core\Authentication;

use WPForms\Vendor\CoreInterfaces\Core\Request\RequestSetterInterface;
use WPForms\Vendor\CoreInterfaces\Core\Request\TypeValidatorInterface;
use InvalidArgumentException;
interface AuthInterface
{
    /**
     * @throws InvalidArgumentException
     */
    public function validate(TypeValidatorInterface $validator) : void;
    public function apply(RequestSetterInterface $request) : void;
}
