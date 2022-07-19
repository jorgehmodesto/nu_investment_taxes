<?php

namespace App\Command;

use App\Helpers\Taxes;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

class CapitalGainsCommand extends Command
{
    protected static $defaultName = 'capital-gains';
    protected static $defaultDescription = 'Capital gains taxes calculator';

    protected function configure(): void
    {}

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $orders = $io->askQuestion(new Question('Please, provide the orders'));

        $taxes = new Taxes($orders);
        $taxes->calculate();

        $errors = $taxes->getErrors();

        if (!empty($errors)) {
            $io->error($errors);
        }

        $io->success("Calculated taxes: {$taxes->toJson()}");

        return Command::SUCCESS;
    }
}
