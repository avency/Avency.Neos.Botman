<?php
namespace Avency\Neos\Botman\Controller;

use Avency\Neos\Botman\Service\BotmanService;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Drivers\DriverManager;
use BotMan\BotMan\Messages\Attachments\Image;
use BotMan\BotMan\Messages\Outgoing\OutgoingMessage;
use BotMan\BotMan\Middleware\ApiAi;
use BotMan\Drivers\Web\WebDriver;
use FilippoToso\BotMan\Drivers\RocketChat\RocketChatDriver;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Controller\ActionController;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for interactions with botman
 *
 * @Flow\Scope("singleton")
 */
class BotmanController extends ActionController
{
    /**
     * @Flow\Inject
     * @var BotmanService
     */
    protected $botmanService;

    /**
     * Index action
     *
     * @return void
     * @throws \Exception
     */
    public function indexAction()
    {
        $this->botmanService->runBotman(
            $this->getControllerContext()->getRequest()->getMainRequest()->getHttpRequest()
        );

        throw new \Exception('Botman listen failed.', 1524829385);
    }

    /**
     * Load botman drivers
     *
     * @return void
     */
    protected function loadDrivers(): void
    {

    }
}
