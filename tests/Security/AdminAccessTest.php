<?php

namespace App\Tests\Security;

use App\Entity\User;
use Doctrine\ORM\Tools\SchemaTool;
use PHPUnit\Framework\Attributes\CoversClass;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

#[CoversClass(\App\Security\AccessDeniedHandler::class)]
final class AdminAccessTest extends WebTestCase
{
    public function testMemberIsRedirectedWhenAccessingAdminPage(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $metadata = $entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($entityManager);

        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);

        $user = (new User())
            ->setEmail('member-admin-access@example.fr')
            ->setFirstName('Pierre')
            ->setLastName('Dupont')
            ->setRoles(['ROLE_MEMBER']);

        $entityManager->persist($user);
        $entityManager->flush();

        try {
            $client->loginUser($user);
            $client->request('GET', '/admin');

            self::assertResponseRedirects('/');

            $client->followRedirect();

            self::assertResponseIsSuccessful();
            self::assertSelectorTextContains('body', 'Accès refusé : cette page est réservée aux administrateurs.');
        } finally {
            $managedUser = $entityManager->getRepository(User::class)->findOneBy([
                'email' => 'member-admin-access@example.fr',
            ]);

            if ($managedUser !== null) {
                $entityManager->remove($managedUser);
                $entityManager->flush();
            }
        }
    }
}