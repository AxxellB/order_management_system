<?php
namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
#[\Attribute]
class UniqueEmail extends Constraint
{
    public string $message = 'A user with that email already exists.';
}
