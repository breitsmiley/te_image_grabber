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
use Symfony\Component\Validator\Constraints as Assert;


class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="default")
     */
    public function index(ImageManager $imageManager)
    {
//        $url = 'https://tile.expert';
//        $url = 'https://dig.ua/';

        $tplData = [
//            'test' => $imageManager->grabPageImages($url, 0, 0),
            'images' => $imageManager->getImageSrcList(),
        ];

        return $this->render('default/index.html.twig', $tplData);

    }



    /**
     *
     * @Route(
     *     "/ajax/images/grab_form",
     *     name="ajax_images_grab_form",
     *     methods={"POST"},
     *     condition="request.isXmlHttpRequest()"
     * )
     */
    public function ajaxImagesGet(Request $request, JsonResponseHelper $jsonResponseHelper, ValidatorHelper $validatorHelper, Environment $twig, ImageManager $imageManager)
    {
//        sleep(2);

        $response = $jsonResponseHelper->prepareJsonResponse();
        $responseData = [
            'status' => true,
            'data'   => [],
            'errors' => [],
        ];

        $formData = $request->request->all();

        // Validation
        //------------------------------
        $validator = Validation::createValidator();
        $groups = new Assert\GroupSequence(['Default', 'custom']);
        $constraint = new Assert\Collection([
            'url'  => [
                new Assert\NotBlank(),
                new Assert\Url(),
            ],
            'minWidth' => [
                new Assert\NotBlank(),
                new Assert\Range(['min' => 200]),
            ],
            'minHeight' => [
                new Assert\NotBlank(),
                new Assert\Range(['min' => 200]),
            ],
        ]);

        $violations = $validator->validate($formData, $constraint, $groups);
        $errors = $validatorHelper->getValidatorErrors($violations);

        if ($errors) {
            $responseData['status'] = false;
            $responseData['errors'] = $twig->render(
                'default/_image_control_panel_error.html.twig',
                ['errors' => $errors]
            );
            $response->setData($responseData);
            return $response;
        }
//        //------------------------------
//
//        $formData['name'] = twig_capitalize_string_filter($twig, $formData['name']);
//
//        // Send API
//        //------------------------------

//        $responseData['data'] = $imageManager->grabPageImages($formData['url'], $formData['minWidth'], $formData['minHeight']);
        $responseData['data'] = $twig->render(
            'default/_image_grid.html.twig',
            ['images' => $imageManager->grabPageImages($formData['url'], $formData['minWidth'], $formData['minHeight'])]
        );
        $response->setData($responseData);

        return $response;
    }

}
