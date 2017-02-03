<?php

namespace CheapRoute\Command;

use CheapRoute\Service\Parser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\{
    Table, TableCell, TableSeparator
};

class ParseCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('flypgs:parse')
            ->setDescription('Command to test parse procedure');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $html = file_get_contents(
            __DIR__ . '/../../res.html'
        );

        $output->writeln('<info>Departure Flights</info>');
        $departureRes = Parser::parse($html, Parser::DEPARTURE_CLASSES);

        $this->printNew($output, $departureRes);

        $output->writeln('<info>Return Flights</info>');
        $returnRes = Parser::parse($html, Parser::RETURN_CLASSES);
        $this->printNew($output, $returnRes);
    }

    protected function printNew(OutputInterface $output, array $res)
    {
        $table = new Table($output);
        $table->setHeaders(['Date', 'Departure', 'Arrival', 'Price']);

        $rows = [];

        foreach ($res as $key => $data) {
            $rows[] = [
                $data['departureDate']->format('d F l'),
                $data['departureDate']->format('H:i'),
                $data['arrivalDate']->format('H:i'),
                $data['price']['amount'] . ' ' . $data['price']['currency'],
            ];
        }
            //$rows[] = new TableSeparator();

        $table->setRows($rows)->render();
    }

    protected function print(OutputInterface $output, array $res)
    {
        $table = new Table($output);
        $table->setHeaders(['Date', 'Departure', 'Arrival', 'Price']);

        $rows = [];

        foreach ($res as $date => $data) {

            $dateCell = new TableCell(
                $date,
                ['rowspan' => count($data)]
            );

            $add = true;
            foreach ($data as $item) {
                if ($add) {
                    $rows[] = [
                        $dateCell, $item['dep']->format('H:i'), $item['arr']->format('H:i'), $item['price'],
                    ];
                } else {
                    $rows[] = [
                        $item['dep']->format('H:i'), $item['arr']->format('H:i'), $item['price'],
                    ];
                }

                $add = false;
            }
            $rows[] = new TableSeparator();
        }

        $table->setRows($rows)->render();
    }
}
