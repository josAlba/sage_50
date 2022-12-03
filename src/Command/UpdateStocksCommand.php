<?php

namespace App\Command;

use App\Service\Sage50Service;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name       : 'UpdateStocks',
    description: 'Actualizar stocks de sage50',
)]
class UpdateStocksCommand extends Command
{
    public function __construct(private readonly Sage50Service $sage)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->success('EMPEZANDO LA SINCRONIZACION');

        $this->sage->updateStocks();

        $io->success('STOCKS ACTUALIZADOS');

        return Command::SUCCESS;
    }
}
