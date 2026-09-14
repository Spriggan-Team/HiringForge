<?php

declare(strict_types=1);


namespace App\Tests\Functional\JobOffer;

use App\Application\DTO\Auth\AuthenticatedPerson;
use App\Domain\Shared\Account\AccountRole;
use App\Infrastructure\Security\JwtAuthentificator;

use App\Tests\Support\Factory\Company\CompanyEntityFactory;
use App\Tests\Support\Factory\JobOffer\JobOfferEntityFactory;
use App\Tests\Support\Factory\User\UserEntityFactory;
use PHPUnit\Framework\Attributes\Test;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class JobOfferControllerTest extends WebTestCase
{
    use ResetDatabase;
    use Factories;

    #[Test]
    public function it_works(): void
    {
        self::markTestIncomplete('Test à implémenter');
    }

    // public function testUnauthenticatedUserCannotCreateJobOffer(): void
    // {
    //     $client = static::createClient();

    //     $client->request(
    //         method: 'POST',
    //         uri: '/api/job-offers',
    //         server: [
    //             'CONTENT_TYPE' => 'application/json',
    //         ],
    //         content: json_encode([
    //             'title' => 'PHP Developer',
    //         ], JSON_THROW_ON_ERROR)
    //     );

    //     self::assertResponseStatusCodeSame(404);
    // }

    // public function testAuthenticatedUserCanCreateJobOffer(): void
    // {
    //     $client = static::createClient();

    //     $user = UserEntityFactory::createOne();

    //     CompanyEntityFactory::createOne([
    //         'owner' => $user,
    //     ]);
    //     $jwtAuthentificator = new JwtAuthentificator();

    //     $client->loginUser(new AuthenticatedPerson(
    //         id: $user->getId(),
    //         roles: [AccountRole::USER],
    //         sub: $jwtAuthentificator->generate([
    //             'id' => $user->getId()
    //         ]),
    //     ));


    //     $payload = [
    //         'title' => 'Senior PHP Developer',
    //         'content' => 'Backend development',
    //         'categories' => [],
    //         'skills' => [],
    //         'languages' => [],
    //         'departmentId' => null,
    //         'workMode' => 'remote',
    //         'expertise' => 'senior',
    //         'contractTypeId' => null,
    //         'salary' => null,
    //         'visibilityStatus' => 'public',
    //         'publicationDate' => null,
    //         'location' => null,
    //         'publicationStatus' => 'draft',
    //     ];

    //     $client->request(
    //         method: 'POST',
    //         uri: '/api/job-offers',
    //         server: [
    //             'CONTENT_TYPE' => 'application/json',
    //         ],
    //         content: json_encode(
    //             $payload,
    //             JSON_THROW_ON_ERROR
    //         )
    //     );

    //     self::assertResponseIsSuccessful();

    //     $response = $client->getResponse();

    //     $data = json_decode(
    //         $response->getContent(),
    //         true,
    //         512,
    //         JSON_THROW_ON_ERROR
    //     );

    //     self::assertArrayHasKey(
    //         'data',
    //         $data
    //     );

    //     self::assertArrayHasKey(
    //         'offerId',
    //         $data['data']
    //     );

    //     self::assertNotEmpty(
    //         $data['data']['offerId']
    //     );
    // }

    // public function testCreateJobOfferFailsWhenUserHasNoCompany(): void
    // {
    //     $client = static::createClient();

    //     $user = UserEntityFactory::createOne();
    //     $jwtAuthentificator = new JwtAuthentificator();

    //     $client->loginUser(new AuthenticatedPerson(
    //         id: $user->getId(),
    //         roles: [AccountRole::USER],
    //         sub: $jwtAuthentificator->generate([
    //             'id' => $user->getId()
    //         ]),
    //     ));

    //     $client->request(
    //         method: 'POST',
    //         uri: '/api/job-offers',
    //         server: [
    //             'CONTENT_TYPE' => 'application/json',
    //         ],
    //         content: json_encode([
    //             'title' => 'PHP Developer',
    //             'content' => 'Backend',
    //             'categories' => [],
    //             'skills' => [],
    //             'languages' => [],
    //         ], JSON_THROW_ON_ERROR)
    //     );

    //     /*
    //      * Ton controller transforme actuellement l'exception
    //      * en ApiResponse::error().
    //      */
    //     self::assertResponseStatusCodeSame(500);
    // }


    // public function testAuthenticatedUserCanUpdateJobOffer(): void
    // {
    //     $client = static::createClient();

    //     $user = UserEntityFactory::createOne();

    //     CompanyEntityFactory::createOne([
    //         'owner' => $user,
    //     ]);

    //     $jobOffer = JobOfferEntityFactory::createOne([
    //         'author' => $user,
    //         'title' => 'Old title',
    //         'content' => 'Old content',
    //     ]);

    //     $jwtAuthentificator = new JwtAuthentificator();

    //     $client->loginUser(new AuthenticatedPerson(
    //         id: $user->getId(),
    //         roles: [AccountRole::USER],
    //         sub: $jwtAuthentificator->generate([
    //             'id' => $user->getId()
    //         ]),
    //     ));


    //     $payload = [
    //         'id' => $jobOffer->getId(),
    //         'title' => 'New title',
    //         'content' => 'New content',
    //         'location' => [],
    //         'departmentId' => null,
    //         'contractTypeId' => null,
    //         'workMode' => null,
    //         'expertise' => null,
    //         'salary' => null,
    //         'categories' => [],
    //         'skills' => [],
    //         'visibilityStatus' => null,
    //         'publicationStatus' => null,
    //         'publicationDate' => null,
    //     ];

    //     $client->request(
    //         method: 'PUT',
    //         uri: sprintf(
    //             '/api/job-offers/%s/update',
    //             $jobOffer->getId()
    //         ),
    //         server: [
    //             'CONTENT_TYPE' => 'application/json',
    //         ],
    //         content: json_encode(
    //             $payload,
    //             JSON_THROW_ON_ERROR
    //         )
    //     );

    //     self::assertResponseIsSuccessful();


    //     self::assertSame(
    //         'New title',
    //         $jobOffer->getTitle()
    //     );

    //     self::assertSame(
    //         'New content',
    //         $jobOffer->getContent()
    //     );
    // }
}