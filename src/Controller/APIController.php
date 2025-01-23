<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;

class APIController
{
    public function getUserFromToken(Request $request, UserRepository $userRepository)
    {
        $user = null;
        $token = $request->cookies->get('authToken');
        if ($token) {
            $payload = $this->decodeToken($token);
            if ($payload && isset($payload['id'])) {
                $user = $userRepository->find($payload['id']);
            }
        }
        if (!$user instanceof User) {
            $user = new User(); // Crée un utilisateur vide
            $user->setRoles(['']);
        }
        return $user;
    }
    private function decodeToken($token)
    {
        $parts = explode('.', $token);
        if (count($parts) != 3) {
            return null;
        }

        $payload = json_decode(base64_decode($parts[1]), true);

        if (!$payload) {
            return null;
        }

        // Check if token is expired
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return null;
        }

        return $payload;
    }
}