<?php

namespace App\Controller;

use App\Service\ImageManager;
use App\Service\JsonResponseHelper;
use App\Service\ValidatorHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validation;
use Twig\Environment;


class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="default")
     */
    public function index(ImageManager $imageManager)
    {
        $url = 'https://tile.expert';
//        $url = 'https://dig.ua/';

        $tplData = [
//            'test' => $imageManager->grabPageImages($url, 0, 0),
            'test' => 111,
        ];

        return $this->render('default/index.html.twig', $tplData);

    }



    /**
     *
     * @Route(
     *     "/ajax/images/get",
     *     name="ajax_order_form_submit",
     *     methods={"POST"},
     *     condition="request.isXmlHttpRequest()"
     * )
     */
    public function ajaxImagesGet(Request $request, JsonResponseHelper $jsonResponseHelper, ValidatorHelper $validatorHelper, Environment $twig)
    {

        $response = $jsonResponseHelper->prepareJsonResponse();
        $responseData = [
            'status' => true,
            'data'   => [],
            'errors' => [],
        ];
//
//        $formData = $request->request->all();
//
//        // Validation
//        //------------------------------
//        $validator = Validation::createValidator();
//        $groups = new Assert\GroupSequence(['Default', 'custom']);
//        $constraint = new Assert\Collection([
//            'name'  => [
//                new Assert\NotBlank(),
//                new Assert\Regex(['pattern' => "/^[A-ZА-Яa-zа-я'-]{2,20}$/"]),
//            ],
//            'phone' => [
//                new Assert\NotBlank(),
//                new Assert\Regex(['pattern' => "/^\+[1-9]{1}[0-9]{3,14}$/"]),
//            ],
//            'email' => [
//                new Assert\Email(),
//            ],
//        ]);
//
//        $violations = $validator->validate($formData, $constraint, $groups);
//        $errors = $validatorHelper->getValidatorErrors($violations);
//
//        if ($errors) {
//            $responseData['status'] = false;
//            $responseData['errors'] = $errors;
//            $response->setData($responseData);
//            return $response;
//        }
//        //------------------------------
//
//        $formData['name'] = twig_capitalize_string_filter($twig, $formData['name']);
//
//        // Send API
//        //------------------------------

        $responseData['data']['type'] = 1;
        $response->setData($responseData);

        return $response;
    }

}
