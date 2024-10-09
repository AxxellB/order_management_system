<?php
namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class MatchPasswordValidator extends ConstraintValidator
{
public function validate(mixed $value, Constraint $constraint): void
{
$object = $this->context->getObject();

if ($object->password !== $object->confirmPassword) {
$this->context->buildViolation($constraint->message)
->addViolation();
}
}
}
