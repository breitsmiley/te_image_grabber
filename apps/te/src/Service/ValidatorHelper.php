<?php


namespace App\Service;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Class ValidatorHelper
 * @package AppBundle\Utils
 */
class ValidatorHelper
{

    public function getValidatorErrors(ConstraintViolationListInterface $violations)
    {

        $messages = [];

        if (count($violations) > 0) {
            foreach ($violations as $violation) {
                $fieldName = trim($violation->getPropertyPath(), "[]");
                $messages[$fieldName] = $violation->getMessage();
            }
        }
        return $messages;
    }
}
