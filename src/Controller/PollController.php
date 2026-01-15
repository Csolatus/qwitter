<?php

namespace App\Controller;

use App\Entity\Poll;
use App\Entity\PollOption;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PollController extends AbstractController
{
    #[Route('/poll/vote/{id}', name: 'app_poll_vote', methods: ['POST'])]
    public function vote(PollOption $option, Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $poll = $option->getPoll();

        // Check if user has already voted in this poll
        foreach ($poll->getOptions() as $pollOption) {
            if ($pollOption->getVoters()->contains($user)) {
                // User already voted
                $this->addFlash('error', 'Vous avez déjà voté pour ce sondage.');
                return $this->redirect($request->headers->get('referer'));
            }
        }

        // Add vote
        $option->addVoter($user);
        $em->flush();

        return $this->redirect($request->headers->get('referer'));
    }

    #[Route('/poll/remove-vote/{id}', name: 'app_poll_remove_vote', methods: ['POST'])]
    public function removeVote(Poll $poll, Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        foreach ($poll->getOptions() as $option) {
            if ($option->getVoters()->contains($user)) {
                $option->removeVoter($user);
            }
        }
        $em->flush();

        return $this->redirect($request->headers->get('referer'));
    }
}
