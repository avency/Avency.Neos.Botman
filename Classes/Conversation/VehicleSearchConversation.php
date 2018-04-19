<?php
namespace Avency\Neos\Botman\Conversation;

use BotMan\BotMan\BotMan;
use BotMan\BotMan\Messages\Conversations\Conversation;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Outgoing\Question;
use Neos\Flow\Annotations as Flow;

/**
 * VehicleSearch conversatioon
 */
class VehicleSearchConversation extends Conversation implements ConversationInterface
{
    /**
     * @var string
     */
    static public $startPhrase = 'vehiclesearch';

    /**
     * Ask start
     *
     * @return void
     */
    public function askStart(): void
    {
        $extras = $this->getBot()->getMessage()->getExtras();

        $this->ask('Du suchst nach "' . $extras['apiParameters']['vehicle'] . '", richtig?', function(Answer $answer) use ($extras) {
            switch ($answer->getMessage()->getExtras('apiAction')) {
                case 'Fahrzeugsuche.Fahrzeugsuche-yes':
                    $this->say('Okay, dann zeige ich dir jetzt Ergebnisse');
                    break;
                default:
                    $this->say('Okay, dann nicht');
                    break;
            }
        });
    }

    /**
     * @return void
     */
    public function run(): void
    {
        $this->askStart();
    }
}
