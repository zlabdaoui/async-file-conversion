<?php
namespace App\Command;

use App\Consumer\OutboxMessageConsumer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:consume-outbox',
    description: 'Consume messages from the Outbox and dispatch them asynchronously'
)]
class OutboxMessageCommand extends Command
{
    private OutboxMessageConsumer $consumer;

    public function __construct(OutboxMessageConsumer $consumer)
    {
        parent::__construct();
        $this->consumer = $consumer;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->success('Starting to consume outbox messages...');

        while (true) {
            $this->consumer->consume();
            sleep(5);
        }
    }
}
