<?php
namespace Avency\Neos\Botman\Service;

use Avency\Neos\Botman\Conversation\ConversationInterface;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Cache\RedisCache;
use BotMan\BotMan\Drivers\DriverManager;
use BotMan\BotMan\Middleware\ApiAi;
use Neos\Eel\FlowQuery\FlowQuery;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Http\Request as FlowRequest;
use Neos\Flow\Reflection\ReflectionService;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

/**
 * Service for botman
 *
 * @Flow\Scope("singleton")
 */
class BotmanService
{
    /**
     * @Flow\InjectConfiguration(path="driverConfig")
     * @var array
     */
    protected $driverConfig;

    /**
     * @Flow\InjectConfiguration(path="dialogflowToken")
     * @var string
     */
    protected $dialogflowToken;

    /**
     * @Flow\InjectConfiguration(path="driverClassNames")
     * @var array
     */
    protected $driverClassNames;

    /**
     * @Flow\Inject
     * @var ReflectionService
     */
    protected $reflectionService;

    /**
     * Run botman
     *
     * @param FlowRequest $request
     * @return void
     */
    public function runBotman(FlowRequest $request)
    {
        $this->loadDrivers();

        $botman = BotManFactory::create(
            $this->driverConfig,
            new RedisCache('redis', 6379),
            $this->getRequest($request)
        );

        $dialogflow = ApiAi::create($this->dialogflowToken)->listenForAction();
        $botman->middleware->received($dialogflow);

        $conversations = $this->reflectionService->getAllImplementationClassNamesForInterface(ConversationInterface::class);

        foreach ($conversations as $conversation) {
            $botman->hears($conversation::$startPhrase, function (BotMan $bot) use ($conversation) {
                $bot->startConversation(new $conversation);
            })->middleware($dialogflow);
            // });
        }

        $botman->fallback(function(BotMan $bot) {
            $bot->reply('Sorry, I did not understand these commands. Here is a list of commands I understand: ...');
        });

        // Give the bot something to listen for.
        // $botman->hears('vehiclesearch', function (BotMan $bot) {
        //     $extras = $bot->getMessage()->getExtras();
        //
        //     #$attachment = new Image('https://picsum.photos/500/500');
        //     #$message = OutgoingMessage::create('This is my text')->withAttachment($attachment);
        //     #$bot->reply($message);
        //
        //     $bot->reply('Sie suchen nach "' . $extras['apiParameters']['vehicle'] . '", richtig?');
        //
        // })->middleware($dialogflow);

        $botman->listen();
    }

    /**
     * Load Drivers
     *
     * @return void
     */
    protected function loadDrivers(): void
    {
        foreach ($this->driverClassNames as $driverClassName => $active) {
            if ($active) {
                DriverManager::loadDriver($driverClassName);
            }
        }

    }

    /**
     * Get Request
     *
     * @param FlowRequest $request
     * @return SymfonyRequest
     */
    public function getRequest(FlowRequest $request): SymfonyRequest
    {
        return new SymfonyRequest(
            [],
            $request->getMethod() == 'GET' ? $request->getArguments() : $request->getParsedBody(),
            $request->getAttributes(),
            $request->getCookies(),
            $request->getUploadedFiles(),
            $request->getServerParams(),
            $request->getContent()
        );
    }
}
