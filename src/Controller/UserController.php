<?php

namespace App\Controller;

use App\Service\UserService;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/api", name="api_")
 */
class UserController extends AbstractController
{

    public function __construct(private UserService $userService)
    {

    }

    #[Route('/user/{id}/balance/{fromDate}/{toDate}', name: 'app_user_get_balance', methods:["GET","POST"])]
    public function getBalance(Request $request, ManagerRegistry $doctrine, int $id, string $fromDate, string $toDate): JsonResponse
    {


        $validator = Validation::createValidator();

        if(!$validator->validate($id, new Assert\Positive())) {

            return $this->json(['status' => 'error', 'message' => 'Id is not valid']);
        }

        if(!$validator->validate($fromDate, new Assert\DateTime())) {

            return $this->json(['status' => 'error', 'message' => 'From date is not valid']);
        }

        if(!$validator->validate($toDate, new Assert\DateTime())) {

            return $this->json(['status' => 'error', 'message' => 'To date is not valid']);
        }

        $user = $doctrine->getRepository(User::class)->find($id);

        if (!$user) {

            return $this->json(['status' => 'error', 'message' => 'No user found'], 404);
        }

        $balance = $this->userService->calculateCurrentBalance($user->getId(), $fromDate, $toDate);

        return $this->json(['status' => 'success', 'balance' => $balance]);
    }
}

