<?php namespace App\Api\Item\Main;

use App\Api\Exception\ApiDisabledException;
use App\Api\Exception\AuthException;
use App\Api\Exception\InvalidTokenException;
use App\Api\Exception\NotFoundException;
use App\Api\Exception\ValidationException;
use App\Entity\User;
use Ewll\DBBundle\Repository\RepositoryProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Controller extends AbstractController
{
    private $repositoryProvider;

    public function __construct(RepositoryProvider $repositoryProvider)
    {
        $this->repositoryProvider = $repositoryProvider;
    }

    public function action(Request $request, array $handler, int $id = null)
    {
        try {
            try {
                list($userId, $secret) = $this->parseAuthorization($request->headers->get('authorization', ''));
            } catch (InvalidTokenException $e) {
                throw new AuthException();
            }

            /** @var User|null $user */
            $user = $this->repositoryProvider->get(User::class)->findById($userId);
            if (null === $user) {
                throw new AuthException();
            } elseif (!$user->isApiEnabled) {
                throw new ApiDisabledException();
            } elseif ($user->apiKey !== $secret) {
                throw new AuthException();
            }

            //@TODO USER BLOCKED

            $function = [$this->container->get($handler[0]), $handler[1]];
            $data = [$request, $user];
            if (null !== $id) {
                $data[] = $id;
            }
            $result = call_user_func_array($function, $data);

            $response = new JsonResponse($result);
        } catch (AuthException $e) {
            return new JsonResponse('', Response::HTTP_UNAUTHORIZED);
        } catch (ApiDisabledException $e) {
            return new JsonResponse('', Response::HTTP_FAILED_DEPENDENCY);
        } catch (ValidationException $e) {
            $response = new JsonResponse(['errors' => $e->getErrors()], Response::HTTP_BAD_REQUEST);
        } catch (NotFoundException $e) {
            $response = new JsonResponse([], Response::HTTP_NOT_FOUND);
        }

        return $response;
    }

    /** @throws InvalidTokenException */
    private function parseAuthorization(string $auth): array
    {
        if (!preg_match('/^Bearer (\d+)\.(.{64})$/i', $auth, $matches)) {
            throw new InvalidTokenException();
        }
        $userId = (int)$matches[1];
        $secret = $matches[2];

        return [$userId, $secret];
    }
}
