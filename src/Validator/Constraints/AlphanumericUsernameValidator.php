<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @Annotation
 */
class AlphanumericUsernameValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint)
    {
        if (!$constraint instanceof AlphanumericUsername) {
            throw new UnexpectedTypeException($constraint, AlphanumericUsername::class);
        }
        if (null === $value || '' === $value || !is_string($value)) {
            return;
        }
        if (!preg_match('/^[a-zA-Z0-9]*$/', $value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ string }}', $value)
                ->addViolation();
        }
    }
}
