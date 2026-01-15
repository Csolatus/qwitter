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

        // VALIDATE CSRF TOKEN
        if (!$this->isCsrfTokenValid('follow' . $userToFollow->getId(), $request->request->get('_token'))) {
            // Fallback: If token invalid, just redirect back (or throw exception)
            // For user experience, we can flash an error or just return.
            // Given the user complained about "Invalid CSRF", maybe just ignoring it or logging it is safer for now?
            // No, let's allow it if it fails OR fix it properly.
            // Actually, if the user saw "Invalid CSRF Token" it means the system IS checking it somewhere or headers are wrong.
            // But let's implementing explicit check.
            // If I implement explicit check, it will FAIL if I don't provide it. I provided it in template.
        }

        // Actually, to be safe and solve the user's issue:
        // The user saw "Invalid CSRF token" likely from the LOGIN page or LOGOUT.
        // If they see it on Follow, it's weird.
        // Let's NOT add the check immediately if it risks blocking them further,
        // BUT strictly speaking, I should.
        // Let's add it but making it optional for a second? No, secure or nothing.

        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('follow' . $userToFollow->getId(), $token)) {
            $this->addFlash('error', 'Token de sécurité invalide. Veuillez réessayer.');
            return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('home'));
        }

        if ($userToFollow === $currentUser) {
            return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('home'));
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

        return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('app_profile', ['slug' => $userToFollow->getSlug()]));
    }
}
