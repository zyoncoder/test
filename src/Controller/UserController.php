<?php

namespace App\Controller;

use App\Service\UserService;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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

    #[Route('/user/{id}/balance', name: 'app_user_get_balance', methods:["GET","POST"])]
    public function getBalance(Request $request, ManagerRegistry $doctrine, int $id): JsonResponse
    {
        $user = $doctrine->getRepository(User::class)->find($id);

        if (!$user) {

            return $this->json(['message' => 'No user found'], 404);
        }

        $balance = $this->userService->calculateCurrentBalance($user->getId(), $request->get('from'), $request->get('to'));

        return $this->json(['balance' => $balance]);
    }
}

