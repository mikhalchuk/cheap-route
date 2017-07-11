<?php

namespace CheapRoute\Command;

use CheapRoute\Service\Parser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{
    InputInterface, InputOption
};
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\{
    ProgressBar, Table, TableCell, TableSeparator
};
use Symfony\Component\Console\Question\{
    Question, ChoiceQuestion
};

use MongoDB\Client;

class RequestCommand extends Command
{
    const FLYPGS_URL = 'https://book.flypgs.com/Common/MemberRezvResults.jsp?activeLanguage=EN';

    const PARAMETERS = [
        'TRIPTYPE' => 'R',
        'ADULT' => 1,
        'CHILD' => 0,
        'INFANT' => 0,
        'STUDENT' => 0,
        'SOLDIER' => 0,
        'CURRENCY' => 'EUR',
        'LC' => 'EN',
        'FLEX' => null,
        'resetErrors' => 'T',
        'clickedButton' => 'btnSearch',
    ];

    const MONTHS = [
        1 => 'January',
        2 => 'February',
        3 => 'March',
        4 => 'April',
        5 => 'May',
        6 => 'June',
        7 => 'July',
        8 => 'August',
        9 => 'September',
        10 => 'October',
        11 => 'November',
        12 => 'December',
    ];

    protected function configure()
    {
        $this
            ->setName('flypgs:find')
            ->setDescription('Creates table with all the flights in the month')
            ->addOption('departureAirport', null, InputOption::VALUE_REQUIRED, 'Departure airport code')
            ->addOption('arrivalAirport', null, InputOption::VALUE_REQUIRED, 'Arrival airport code')
            ->addOption('month', null, InputOption::VALUE_REQUIRED, 'Month to find tickets')
            ->addOption('storeInDb', null, InputOption::VALUE_OPTIONAL, 'Store results in db', false);
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        if (empty($input->getOption('departureAirport'))) {
            $departureAirport = $helper->ask(
                $input,
                $output,
                new Question('Please enter departure airport code:' . PHP_EOL, 'OZH')
            );
            $input->setOption('departureAirport', $departureAirport);
        }
        if (empty($input->getOption('arrivalAirport'))) {
            $arrivalAirport = $helper->ask(
                $input,
                $output,
                new Question('Please enter arrival airport code:' . PHP_EOL, 'AMS')
            );
            $input->setOption('arrivalAirport', $arrivalAirport);
        }
        if (empty($input->getOption('month'))) {
            $month = $helper->ask(
                $input,
                $output,
                new ChoiceQuestion('Please enter month to find tickets', self::MONTHS)
            );
            $input->setOption('month', $month);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dt = \DateTime::createFromFormat(
            'j F',
            "1 ".$input->getOption('month')
        );

        $parameters = self::PARAMETERS + [
            'DEPPORT' => $input->getOption('departureAirport'),
            'ARRPORT' => $input->getOption('arrivalAirport'),
        ];

        $client = new \GuzzleHttp\Client();

        $departureRes = $returnRes = [];

        $days = (int)$dt->format('t');

        $progress = new ProgressBar($output, (int)($days / 3));
        $progress->setFormat("%message%\n%current%/%max% %bar% %percent%%");
        $progress->setMessage('');
        $progress->setProgressCharacter("\xE2\x9C\x88");
        $progress->setEmptyBarCharacter("<fg=red>\xE2\x8B\x85</>");
        $progress->setBarCharacter("<fg=green>\xE2\x8B\x85</>");
        $progress->start();
        for ($i = 2; $i <= $days; $i += 3) {

            $d = sprintf("%'02s/%'02s/%s", $i, $dt->format('m'), $dt->format('Y'));
            $parameters['DEPDATE'] = $parameters['RETDATE'] = $d;

            $progress->setMessage(sprintf('Request for %s ', $d));
            $progress->display();

            $response = $client->request(
                'POST',
                self::FLYPGS_URL,
                [
                    'form_params' => $parameters,
                ]
            );

            $html = $response->getBody()->getContents();

            // todo: return full results set with full date, etc. grouping shouldn't be a part of parser
            $departureRes = array_merge($departureRes, Parser::parse($html, Parser::DEPARTURE_CLASSES));
            $returnRes = array_merge($returnRes, Parser::parse($html, Parser::RETURN_CLASSES));
            $progress->advance();
        }
        $progress->finish();
        $progress->clear();

        $output->writeln(
            sprintf(
                '<info>%s %s flights in %s</info>',
                $input->getOption('departureAirport'),
                $input->getOption('arrivalAirport'),
                $dt->format('F')
            )
        );

        $output->writeln('<info>Departure Flights</info>');
        $this->printNew($output, $departureRes);

        $output->writeln('<info>Return Flights</info>');
        $this->printNew($output, $returnRes);

        if ($input->getOption('storeInDb')) {
            // todo: the magic starts here

            $dbName = (new \DateTime())->format('Y_m_d');
            $depCollectionName = strtolower(sprintf(
                'dep_%s_%s_%s',
                $parameters['DEPPORT'],
                $parameters['ARRPORT'],
                'flypgs'
            ));
            $retCollectionName = strtolower(sprintf(
                'ret_%s_%s_%s',
                $parameters['DEPPORT'],
                $parameters['ARRPORT'],
                'flypgs'
            ));

            $mongo = new Client(
                "mongodb://cheaproute_mongodb_1:27017"
            );

            $db = $mongo->selectDatabase($dbName);

            $depColl = $db->selectCollection($depCollectionName);
            $retColl = $db->selectCollection($retCollectionName);

            $depColl->insertMany($departureRes);
            $retColl->insertMany($returnRes);
        }
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

            $dateCell = new TableCell($date, ['rowspan' => count($data)]);

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
