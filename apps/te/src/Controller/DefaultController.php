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
        $tplData = [
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
    public function ajaxImagesGet(
        Request $request,
        JsonResponseHelper $jsonResponseHelper,
        ValidatorHelper $validatorHelper,
        Environment $twig,
        ImageManager $imageManager
    )
    {
        $response = $jsonResponseHelper->prepareJsonResponse();
        $responseData = [
            'status' => true,
            'data' => [],
            'errors' => [],
        ];

        $formData = $request->request->all();

        // Validation
        //------------------------------
        $validator = Validation::createValidator();
        $groups = new Assert\GroupSequence(['Default', 'custom']);
        $constraint = new Assert\Collection([
            'url' => [
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
            'grab_mode' => [
                new Assert\Choice(['choices' => [$imageManager::GRAB_MODE_FAST, $imageManager::GRAB_MODE_SLOW]]),
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

        // Processing
        //------------------------------
        $tplData = [
            'images' => $imageManager->grabPageImages($formData['url'], $formData['minWidth'], $formData['minHeight'], $formData['grab_mode'])
        ];
        $responseData['data'] = $twig->render(
            'default/_image_grid.html.twig',
            $tplData
        );
        $response->setData($responseData);

        return $response;
    }

}
