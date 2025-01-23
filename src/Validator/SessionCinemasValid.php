<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class SessionCinemasValid extends Constraint
{
    public string $message = 'Le cinéma "{{ cinema }}" ne projette pas le film "{{ film }}".';
}
