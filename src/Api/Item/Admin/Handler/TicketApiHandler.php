<?php namespace App\Api\Item\Admin\Handler;

use App\Controller\IndexController;
use App\Crud\Unit\TicketMessage\TicketMessageDto;
use App\Entity\Event;
use App\Entity\Ticket;
use App\Entity\User;
use App\Factory\EventFactory;
use App\Guzzle\Guzzle;
use App\Repository\TicketRepository;
use Ewll\DBBundle\DB\Client as DbClient;
use Ewll\DBBundle\Repository\Repository;
use Ewll\DBBundle\Repository\RepositoryProvider;
use Ewll\MailerBundle\Mailer;
use Ewll\MailerBundle\Template;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Loader\FilesystemLoader;

class TicketApiHandler implements AdminApiHandlerInterface
{
    const LETTER_NAME_SUPPORT_ANSWER = 'letterSupportAnswer';

    private $repositoryProvider;
    private $formFactory;
    private $validator;
    private $translator;
    private $siteName;
    private $cdn;
    private $adminApiDomain;
    private $adminApiSecret;
    private $guzzle;
    private $defaultDbClient;
    private $eventFactory;
    private $mailer;
    private $router;
    private $domain;

    public function __construct(
        RepositoryProvider $repositoryProvider,
        FormFactoryInterface $formFactory,
        ValidatorInterface $validator,
        TranslatorInterface $translator,
        string $siteName,
        string $cdn,
        string $adminApiDomain,
        string $adminApiSecret,
        Guzzle $guzzle,
        DbClient $defaultDbClient,
        EventFactory $eventFactory,
        Mailer $mailer,
        RouterInterface $router,
        string $domain
  ) {
        $this->repositoryProvider = $repositoryProvider;
        $this->formFactory = $formFactory;
        $this->validator = $validator;
        $this->translator = $translator;
        $this->siteName = $siteName;
        $this->cdn = $cdn;
        $this->adminApiDomain = $adminApiDomain;
        $this->adminApiSecret = $adminApiSecret;
        $this->guzzle = $guzzle;
        $this->defaultDbClient = $defaultDbClient;
        $this->eventFactory = $eventFactory;
        $this->mailer = $mailer;
        $this->router = $router;
        $this->domain = $domain;
    }

    public function newMessage(Request $request): array
    {
        /** @var TicketRepository $ticketRepository */
        $ticketRepository = $this->repositoryProvider->get(Ticket::class);
        $ticketId = $request->request->getInt('ticketId');

        $this->defaultDbClient->beginTransaction();
        try {
            /** @var Ticket $ticket */
            $ticket = $ticketRepository->findById($ticketId, Repository::FOR_UPDATE);
            if ($ticket === null) {
                throw new \LogicException("Ticket with #$ticketId does not exist");
            }
            /** @var User $user */
            $user = $this->repositoryProvider->get(User::class)->findById($ticket->userId);
            $ticket->lastMessageTs = new \DateTime();
            $ticket->messagesNum++;
            if ($ticket->hasUnreadMessage === false) {
                $ticket->hasUnreadMessage = true;
                $this->eventFactory->create(
                    $ticket->userId,
                    Event::TYPE_ID_UNSUCCESSFUL_SUPPORT_ANSWER,
                    $ticket->id,
                    ['ticketSubject' => $ticket->subject]
                );
                $detailsLink = 'https:' . $this->router->generate(
                        IndexController::ROUTE_PRIVATE_SUPPORT_TICKET,
                        ['ticketId' => $ticket->id],
                        UrlGeneratorInterface::NETWORK_PATH
                    );
                $templateData = [
                    'domain' => $this->domain,
                    'detailsLink' => $detailsLink,
                ];
                $template = new Template(
                    self::LETTER_NAME_SUPPORT_ANSWER,
                    FilesystemLoader::MAIN_NAMESPACE,
                    $templateData
                );
                $this->mailer->createForUser($user, $template);
                //@TODO Отправлять в очередь на 5 минут, если не прочитано, то высылать письмо и создавать событие
            }
            $ticketRepository->update($ticket, ['lastMessageTs', 'messagesNum', 'hasUnreadMessage']);

            $this->defaultDbClient->commit();
        } catch (\Exception $e) {
            $this->defaultDbClient->rollback();

            throw new \RuntimeException("Transaction fail: {$e->getMessage()}", 0, $e);
        }

        return [];
    }

    public function inverseGetMessages(Ticket $ticket): array
    {
        try {
            $response = $this->guzzle->get("https://{$this->adminApiDomain}/api/ticket/{$ticket->id}", [
                'timeout' => 10,
                'connect_timeout' => 10,
                'headers' => [
                    'Authorization' => "Bearer {$this->adminApiSecret}",
                ],
            ]);
            $messages = json_decode($response->getBody()->getContents(), true);

            return $messages;
        } catch (RequestException $e) {
            throw new \RuntimeException("Get ticket #{$ticket->id} messages error: '{$e->getMessage()}'", 0, $e);
        }
    }

    public function inverseCreate(int $ticketId, string $message): void
    {
        /** @var TicketRepository $ticketRepository */
        $ticketRepository = $this->repositoryProvider->get(Ticket::class);
        /** @var Ticket|null $ticket */
        $ticket = $ticketRepository->findById($ticketId);
        if (null === $ticket) {
            throw new \RuntimeException("Ticket #$ticketId not found");
        }

        try {
            $this->guzzle->post("https://{$this->adminApiDomain}/api/ticket", [
                'timeout' => 10,
                'connect_timeout' => 10,
                'headers' => [
                    'Authorization' => "Bearer {$this->adminApiSecret}",
                ],
                'form_params' => [
                    'id' => $ticket->id,
                    'userId' => $ticket->userId,
                    'subject' => $ticket->subject,
                    'message' => $message
                ],
            ]);
        } catch (RequestException $e) {
            throw new \RuntimeException("Create ticket error: '{$e->getMessage()}'", 0, $e);
        }
        $ticketRepository->increaseMessagesSendingNum($ticket, TicketRepository::ACTION_DECREASE);
    }

    public function inverseCreateMessage(int $ticketId, string $message)
    {
        /** @var TicketRepository $ticketRepository */
        $ticketRepository = $this->repositoryProvider->get(Ticket::class);
        /** @var Ticket|null $ticket */
        $ticket = $ticketRepository->findById($ticketId);
        try {
            $this->guzzle->post("https://{$this->adminApiDomain}/api/ticket/$ticket->id/message", [
                'timeout' => 10,
                'connect_timeout' => 10,
                'headers' => [
                    'Authorization' => "Bearer {$this->adminApiSecret}",
                ],
                'form_params' => [
                    'text' => $message,
                ],
            ]);
        } catch (RequestException $e) {
            $error = "Create ticket message for ticket #$ticket->id error: '{$e->getMessage()}'";
            throw new \RuntimeException($error, 0, $e);
        }
        $ticketRepository->increaseMessagesSendingNum($ticket, TicketRepository::ACTION_DECREASE);
    }
}
