<?php

namespace Common\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:first', description: 'Первая команда')]
class FirstCommand extends Command
{

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {

        $io = new SymfonyStyle($input, $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output);

		$io->success('Успех!');

        return 0;
    }
}
