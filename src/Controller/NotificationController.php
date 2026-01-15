<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class NotificationController extends AbstractController
{
    #[Route('/notifications', name: 'app_notifications')]
    public function index(): Response
    {
        // Fake data generation
        $now = new \DateTime();

        $notifications = [
            [
                'id' => 1,
                'type' => 'like',
                'author' => [
                    'name' => 'Jane Doe',
                    'handle' => '@janedoe',
                    'avatar_color' => 'bg-purple-100 text-purple-600',
                ],
                'message' => 'liked your post',
                'post_preview' => 'Just setting up my qwitter!',
                'created_at' => (clone $now)->modify('-2 minutes'),
                'read' => false,
            ],
            [
                'id' => 2,
                'type' => 'follow',
                'author' => [
                    'name' => 'John Smith',
                    'handle' => '@jsmith',
                    'avatar_color' => 'bg-green-100 text-green-600',
                ],
                'message' => 'followed you',
                'created_at' => (clone $now)->modify('-1 hour'),
                'read' => true,
            ],
            [
                'id' => 3,
                'type' => 'mention',
                'author' => [
                    'name' => 'Alice Wonder',
                    'handle' => '@alice',
                    'avatar_color' => 'bg-orange-100 text-orange-600',
                ],
                'message' => 'mentioned you in a post',
                'post_preview' => 'Hey @user, check this out!',
                'created_at' => (clone $now)->modify('-1 day'),
                'read' => true,
            ],
            // More Likes
            [
                'id' => 4,
                'type' => 'like',
                'author' => [
                    'name' => 'Bob Builder',
                    'handle' => '@bob',
                    'avatar_color' => 'bg-blue-100 text-blue-600',
                ],
                'message' => 'liked your post',
                'post_preview' => 'Thinking about the new project structure...',
                'created_at' => (clone $now)->modify('-3 hours'),
                'read' => true,
            ],
            [
                'id' => 5,
                'type' => 'like',
                'author' => [
                    'name' => 'Sarah Connor',
                    'handle' => '@sarah',
                    'avatar_color' => 'bg-red-100 text-red-600',
                ],
                'message' => 'liked your reply',
                'post_preview' => 'Absolutely agree with this point.',
                'created_at' => (clone $now)->modify('-5 hours'),
                'read' => true,
            ],
            // More Follows
            [
                'id' => 6,
                'type' => 'follow',
                'author' => [
                    'name' => 'Mike Ross',
                    'handle' => '@mike',
                    'avatar_color' => 'bg-yellow-100 text-yellow-600',
                ],
                'message' => 'followed you',
                'created_at' => (clone $now)->modify('-2 days'),
                'read' => true,
            ],
            // More Mentions
            [
                'id' => 7,
                'type' => 'mention',
                'author' => [
                    'name' => 'Jessica Pearson',
                    'handle' => '@jessica',
                    'avatar_color' => 'bg-gray-100 text-gray-600',
                ],
                'message' => 'mentioned you in a comment',
                'post_preview' => '@user We need to discuss the merger details.',
                'created_at' => (clone $now)->modify('-3 days'),
                'read' => true,
            ],
            [
                'id' => 8,
                'type' => 'mention',
                'author' => [
                    'name' => 'Louis Litt',
                    'handle' => '@louis',
                    'avatar_color' => 'bg-pink-100 text-pink-600',
                ],
                'message' => 'mentioned you in a post',
                'post_preview' => 'Does anyone know if @user is available for a mud bath?',
                'created_at' => (clone $now)->modify('-1 week'),
                'read' => true,
            ],
        ];

        // Process for display (calculate relative time)
        foreach ($notifications as &$notification) {
            $notification['time_ago'] = $this->timeElapsedString($notification['created_at']);
        }

        return $this->render('notification/index.html.twig', [
            'notifications' => $notifications,
        ]);
    }

    private function timeElapsedString(\DateTime $datetime, $full = false)
    {
        $now = new \DateTime();
        $ago = $datetime;
        $diff = $now->diff($ago);

        $weeks = floor($diff->d / 7);
        $diff->d -= $weeks * 7;

        $parts = [];
        if ($diff->y)
            $parts['y'] = $diff->y . ' year' . ($diff->y > 1 ? 's' : '');
        if ($diff->m)
            $parts['m'] = $diff->m . ' month' . ($diff->m > 1 ? 's' : '');
        if ($weeks)
            $parts['w'] = $weeks . ' week' . ($weeks > 1 ? 's' : '');
        if ($diff->d)
            $parts['d'] = $diff->d . ' day' . ($diff->d > 1 ? 's' : '');
        if ($diff->h)
            $parts['h'] = $diff->h . ' hour' . ($diff->h > 1 ? 's' : '');
        if ($diff->i)
            $parts['i'] = $diff->i . ' minute' . ($diff->i > 1 ? 's' : '');
        if ($diff->s)
            $parts['s'] = $diff->s . ' second' . ($diff->s > 1 ? 's' : '');

        if (!$full)
            $parts = array_slice($parts, 0, 1);
        return $parts ? implode(', ', $parts) . ' ago' : 'just now';
    }
}
