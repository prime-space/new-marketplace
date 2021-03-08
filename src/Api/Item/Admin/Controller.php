<?php namespace App\Api\Item\Admin;

use App\Api\Exception\NotFoundException;
use App\Api\Exception\ValidationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Controller extends AbstractController
{
    private $secret;

    public function __construct(string $secret)
    {
        $this->secret = $secret;
    }

    public function action(Request $request, array $handler, int $id = null)
    {
        try {
            $auth = $request->headers->get('authorization');
            if (null === $auth || $auth !== "Bearer {$this->secret}") {
                return new JsonResponse('', Response::HTTP_UNAUTHORIZED);
            }

            $function = [$this->container->get($handler[0]), $handler[1]];
            $data = [$request];
            if (null !== $id) {
                $data[] = $id;
            }
            $result = call_user_func_array($function, $data);

            $response = new JsonResponse($result);
        } catch (ValidationException $e) {
            $response = new JsonResponse(['errors' => $e->getErrors()], Response::HTTP_BAD_REQUEST);
        } catch (NotFoundException $e) {
            $response = new JsonResponse([], Response::HTTP_NOT_FOUND);
        }
//        } catch (AdminApiException $e) {
//            $response = new JsonResponse($e->getData());
//        } catch (AdminApiEmbeddedFormValidationException $e) {
//            $errors = $this->vueViewCompiler->formErrorsViewCompile($e->getFormErrors());
//            $response = new JsonResponse(['embeddedFormErrors' => $errors], 400);
//        }

        return $response;
    }
}
