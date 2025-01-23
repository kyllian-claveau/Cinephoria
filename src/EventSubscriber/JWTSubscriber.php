<?php

namespace App\EventSubscriber;

use App\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class JWTSubscriber implements EventSubscriberInterface
{
    public function onLexikJwtAuthenticationOnJwtCreated($event): void
    {
        $data = $event->getData();
        $user = $event ->getUser();
        if ($user instanceof User)
        {
            $data['email'] = $user->getEmail();
            $data['id'] = $user->getId();
            $data['isTemporaryPassword'] = $user->getIsTemporaryPassword();
        }
        $event->setData($data);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'lexik_jwt_authentication.on_jwt_created' => 'onLexikJwtAuthenticationOnJwtCreated',
        ];
    }
}
