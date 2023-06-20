<?php

namespace App\Validator\Constraints;

use Attribute;
use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
#[\Attribute]
class UniqueSlug extends Constraint
{
    public string $message = 'The slug "{{ string }}" is already in use.';
}
