<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
#[\Attribute]
class AlphanumericUsername extends Constraint
{
    public string $message = 'username.alphanumeric';
}
