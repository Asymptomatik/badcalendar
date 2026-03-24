<?php

namespace App\Tests\Security;

use App\Entity\User;
use App\Security\UserChecker;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

/**
 * Tests unitaires pour UserChecker.
 */
#[CoversClass(UserChecker::class)]
class UserCheckerTest extends TestCase
{
    private UserChecker $checker;

    protected function setUp(): void
    {
        $this->checker = new UserChecker();
    }

    public function testCheckPreAuthThrowsExceptionWhenUserIsNotVerified(): void
    {
        $user = (new User())
            ->setEmail('unverified@example.fr')
            ->setFirstName('Non')
            ->setLastName('Validé')
            ->setIsVerified(false);

        $this->expectException(CustomUserMessageAuthenticationException::class);
        $this->expectExceptionMessage('Votre compte est en attente de validation par un administrateur.');

        $this->checker->checkPreAuth($user);
    }

    public function testCheckPreAuthDoesNotThrowWhenUserIsVerified(): void
    {
        $user = (new User())
            ->setEmail('verified@example.fr')
            ->setFirstName('Validé')
            ->setLastName('Membre')
            ->setIsVerified(true);

        // Aucune exception attendue
        $this->checker->checkPreAuth($user);
        $this->addToAssertionCount(1);
    }

    public function testCheckPreAuthIgnoresNonAppUser(): void
    {
        $user = $this->createMock(\Symfony\Component\Security\Core\User\UserInterface::class);

        // Aucune exception pour un utilisateur non-App\Entity\User
        $this->checker->checkPreAuth($user);
        $this->addToAssertionCount(1);
    }

    public function testCheckPostAuthDoesNothing(): void
    {
        $user = (new User())
            ->setEmail('any@example.fr')
            ->setFirstName('Any')
            ->setLastName('User');

        $this->checker->checkPostAuth($user);
        $this->addToAssertionCount(1);
    }
}
