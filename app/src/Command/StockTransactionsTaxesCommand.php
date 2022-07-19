<?php

namespace App\Command;

use App\Helpers\Taxes;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class StockTransactionsTaxesCommand extends Command
{
    protected static $defaultName = 'StockTransactionsTaxes';
    protected static $defaultDescription = 'Command to calculate stock transactions';

    protected function configure(): void
    {
        $this->addArgument('orders', InputArgument::IS_ARRAY, 'Orders');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $orders = $input->getArgument('orders');

        if ($orders) {
            $io->note(sprintf('Calculating taxes for provided orders: %s', json_encode($orders)));
        }

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
