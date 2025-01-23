<?php

namespace App\Validator;

use App\Entity\Session;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class SessionCinemasValidValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        /* @var $constraint SessionCinemasValid */
        if (!$value instanceof Session) {
            return;
        }

        $film = $value->getFilm();
        foreach ($value->getCinemas() as $cinema) {
            if (!$cinema->getFilms()->contains($film)) {
                $this->context->buildViolation($constraint->message)
                    ->setParameter('{{ cinema }}', $cinema->getName())
                    ->setParameter('{{ film }}', $film->getTitle())
                    ->addViolation();
            }
        }
    }
}
