<?php

namespace App\Validator\Constraints;

use App\Repository\TrickRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniqueSlugValidator extends ConstraintValidator
{
    private TrickRepository $trickRepository;

    public function __construct(TrickRepository $trickRepository)
    {
        $this->trickRepository = $trickRepository;
    }

    public function validate(mixed $value, Constraint $constraint)
    {
        if (!$constraint instanceof UniqueSlug) {
            throw new UnexpectedTypeException($constraint, UniqueSlug::class);
        }
        if (null === $value || '' === $value) {
            return;
        }
        if (!is_string($value)) {
            throw new UnexpectedTypeException($value, 'string');
        }

        // Get the object that is being validated
        $trick = $this->context->getObject();

        $existingTrick = $this->trickRepository->findOneBy(['slug' => $value]);

        // Only raise a violation if a different Trick is using the same slug
        if ($existingTrick instanceof \App\Entity\Trick && $trick instanceof \App\Entity\Trick && $existingTrick->getId() !== $trick->getId()) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ string }}', $value)
                ->addViolation();
        }
    }
}
