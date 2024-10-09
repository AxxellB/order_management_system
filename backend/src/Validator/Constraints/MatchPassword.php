<?php
namespace App\Validator\Constraints;

use Attribute;
use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
#[Attribute]
class MatchPassword extends Constraint
{
    public string $message = 'Passwords do not match.';
}
