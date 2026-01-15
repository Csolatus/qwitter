<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/follow')]
class FollowController extends AbstractController
{
    #[Route('/{id}', name: 'app_follow', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function follow(User $userToFollow, EntityManagerInterface $entityManager, NotificationService $notificationService, Request $request): Response
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        if ($userToFollow === $currentUser) {
            return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('app_accueil'));
        }

        if ($currentUser->isFollowing($userToFollow)) {
            $currentUser->removeFollowing($userToFollow);
            // Optionally remove notification? Usually we keep "started following" history.
        } else {
            $currentUser->addFollowing($userToFollow);

            $notificationService->notify(
                $userToFollow,
                'follow',
                "a commencé à vous suivre",
                $currentUser,
                $currentUser->getId() // Related ID could be the follower's ID so we can link to profile
            );
        }

        $entityManager->flush();

        return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('app_profile_show', ['slug' => $userToFollow->getSlug()]));
    }
}
