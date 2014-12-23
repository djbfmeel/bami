#!/usr/bin/env php
<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/db/OrderModel.php';
require_once __DIR__ . '/db/UserModel.php';

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\DialogHelper;

$console = new Application();

$console
    ->register('add')
    ->setDefinition(array(
        new InputArgument('amount', InputArgument::REQUIRED, 'The amount you want to order'),
        new InputArgument('name', InputArgument::REQUIRED, 'Short name for who the order is'),
    ))
    ->setDescription('Add\'s bami disk(s) to the bami order')
    ->setCode(function (InputInterface $input, OutputInterface $output) {
        $amount = $input->getArgument('amount');
        $name = $input->getArgument('name');

        $userModel = new UserModel();
        $user = $userModel->getByShortName($name);

        if ($user == null) {
            $output->writeln(sprintf('<error>User "%s" does not exist, placing bami disk order failed!</error>', $name));
            die;
        }

        $orderModel = new OrderModel();
        $orderModel->placeOrder($user['id'], $amount);

        if ($amount == 1) {
            $bamiText = 'bami disk';
        } else {
            $bamiText = 'bami disks';
        }

        $output->writeln(sprintf('Ordered <info>%s</info> %s in name of <info>%s</info>', $amount, $bamiText, $name));

    });

$console
    ->register('status')
    ->setDescription('Returns today\'s bami status')
    ->setCode(function (InputInterface $input, OutputInterface $output) {

        $orderModel = new OrderModel();
        $orders = $orderModel->getByDate();

        $userModel = new UserModel();

        $amount = 0;
        $outputMessages = array();
        foreach ($orders as $order) {
            $amount += (int)$order['amount'];

            if ((int)$order['amount'] == 1) {
                $bamiText = 'bami disk';
            } else {
                $bamiText = 'bami disks';
            }

            $user = $userModel->getById($order['user']);

            $outputMessages[] = sprintf(
                '<info>%s</info> has added <info>%d</info> %s to the order.',
                $user['first_name'].' '.$user['last_name'],
                $order['amount'],
                $bamiText
            );

        }

        if ($amount == 1) {
            $bamiText = "bami disk";
        } else {
            $bamiText = "bami disks";
        }

        $output->writeln(sprintf('Today, <info>%d</info> %s have been ordered.', $amount, $bamiText));
        $output->writeln($outputMessages);
    });

$console
    ->register('order')
    ->setDescription('Orders bami disks')
    ->setCode(function (InputInterface $input, OutputInterface $output) {
        $orderModel = new OrderModel();
        $orders = $orderModel->getByDate();

        $amount = 0;
        foreach ($orders as $order) {
            $amount += (int)$order['amount'];
        }

        if ($amount < 6) {
            $output->writeln(sprintf('<error>CANNOT ORDER</error> At least <info>6</info> bami disks have to be ordered, current amount is <info>%d</info>', $amount));

            return;
        }

        $dialog = new DialogHelper();

        if (!$dialog->askConfirmation(
            $output,
            sprintf('<question>Question:</question> Are you sure you want to order <info>%d</info> bami disks? (yes/no)', $amount),
            false
        )
        ) {
            $output->writeln('<error>Bami disks order aborted</error>');

            return;
        }

        $postPostalCodeResult = `curl 'http://www.cafetaria-online.nl/'              -c test.jar -H 'Content-Type: application/x-www-form-urlencoded' -H 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8' -H 'Connection: keep-alive' --data 'postcode=5041eb&checkcode=Binnen+bezorggebied%3F'`;
        $postOrderResult = `curl 'http://www.cafetaria-online.nl/bestelonline' -b test.jar -c test.jar -H 'Origin: http://www.cafetaria-online.nl' -H 'Accept-Encoding: gzip,deflate' -H 'Accept-Language: nl,en;q=0.8,de;q=0.6,en-AU;q=0.4,en-US;q=0.2,de-AT;q=0.2' -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2062.122 Safari/537.36' -H 'Content-Type: application/x-www-form-urlencoded' -H 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8' -H 'Cache-Control: max-age=0' -H 'Referer: http://www.cafetaria-online.nl/bestelonline' -H 'Connection: keep-alive' --data 'dishamount%5B10077%5D=1&dishamount%5B10083%5D=1&dishamount%5B10078%5D=1&dishamount%5B10079%5D=1&dishamount%5B10080%5D=1&garnish%5B10080%5D%5B%5D=253&dishamount%5B25%5D=1&dishamount%5B26%5D=1&dishamount%5B27%5D=1&dishamount%5B29%5D=1&dishamount%5B28%5D=1&dishamount%5B30%5D=1&dishamount%5B31%5D=1&dishamount%5B10011%5D=1&dishamount%5B32%5D=1&dishamount%5B33%5D=1&dishamount%5B44%5D=1&dishamount%5B45%5D=1&dishamount%5B47%5D=1&dishamount%5B48%5D=1&dishamount%5B49%5D=1&dishamount%5B50%5D=1&dishamount%5B51%5D=1&dishamount%5B10010%5D=1&dishamount%5B52%5D=1&dishamount%5B53%5D=1&dishamount%5B3%5D=1&dishamount%5B7%5D=1&dishamount%5B4%5D=1&dishamount%5B10089%5D=1&dishamount%5B138%5D=1&dishamount%5B8%5D=1&dishamount%5B9%5D=1&dishamount%5B10%5D=1&dishamount%5B11%5D=1&dishamount%5B12%5D=1&dishamount%5B13%5D=1&dishamount%5B14%5D=1&dishamount%5B15%5D=1&dishamount%5B16%5D=1&dishamount%5B17%5D=$amount&dishadd%5B17%5D=voeg+toe&dishamount%5B18%5D=1&dishamount%5B20%5D=1&dishamount%5B21%5D=1&dishamount%5B10039%5D=1&dishamount%5B10040%5D=1&dishamount%5B10041%5D=1&dishamount%5B10026%5D=1&dishamount%5B10028%5D=1&dishamount%5B19%5D=1&garnish%5B19%5D%5B%5D=238&dishamount%5B22%5D=1&garnish%5B22%5D%5B%5D=238&dishamount%5B9999%5D=1&garnish%5B9999%5D%5B%5D=238&dishamount%5B10000%5D=1&garnish%5B10000%5D%5B%5D=238&dishamount%5B10001%5D=1&garnish%5B10001%5D%5B%5D=238&dishamount%5B10084%5D=1&dishamount%5B38%5D=1&garnish%5B38%5D%5B%5D=246&dishamount%5B39%5D=1&garnish%5B39%5D%5B%5D=246&dishamount%5B40%5D=1&garnish%5B40%5D%5B%5D=246&dishamount%5B41%5D=1&garnish%5B41%5D%5B%5D=246&dishamount%5B37%5D=1&garnish%5B37%5D%5B%5D=246&dishamount%5B35%5D=1&garnish%5B35%5D%5B%5D=246&dishamount%5B36%5D=1&dishamount%5B136%5D=1&garnish%5B136%5D%5B%5D=246&dishamount%5B10029%5D=1&garnish%5B10029%5D%5B%5D=246&dishamount%5B10036%5D=1&garnish%5B10036%5D%5B%5D=246&dishamount%5B10038%5D=1&garnish%5B10038%5D%5B%5D=246&dishamount%5B10032%5D=1&garnish%5B10032%5D%5B%5D=246&dishamount%5B23%5D=1&garnish%5B23%5D%5B%5D=246&dishamount%5B34%5D=1&dishamount%5B54%5D=1&dishamount%5B55%5D=1&dishamount%5B56%5D=1&dishamount%5B10067%5D=1&garnish%5B10067%5D%5B%5D=246&dishamount%5B10068%5D=1&garnish%5B10068%5D%5B%5D=217&dishamount%5B10033%5D=1&garnish%5B10033%5D%5B%5D=217&dishamount%5B10086%5D=1&garnish%5B10086%5D%5B%5D=217&dishamount%5B79%5D=1&garnish%5B79%5D%5B%5D=217&garnish%5B79%5D%5B%5D=246&dishamount%5B80%5D=1&garnish%5B80%5D%5B%5D=217&garnish%5B80%5D%5B%5D=246&dishamount%5B132%5D=1&garnish%5B132%5D%5B%5D=217&garnish%5B132%5D%5B%5D=246&dishamount%5B82%5D=1&garnish%5B82%5D%5B%5D=217&garnish%5B82%5D%5B%5D=246&dishamount%5B83%5D=1&garnish%5B83%5D%5B%5D=217&garnish%5B83%5D%5B%5D=246&dishamount%5B86%5D=1&garnish%5B86%5D%5B%5D=217&garnish%5B86%5D%5B%5D=246&dishamount%5B87%5D=1&garnish%5B87%5D%5B%5D=217&dishamount%5B137%5D=1&garnish%5B137%5D%5B%5D=217&garnish%5B137%5D%5B%5D=246&dishamount%5B10030%5D=1&garnish%5B10030%5D%5B%5D=217&garnish%5B10030%5D%5B%5D=246&dishamount%5B84%5D=1&garnish%5B84%5D%5B%5D=217&garnish%5B84%5D%5B%5D=246&dishamount%5B85%5D=1&garnish%5B85%5D%5B%5D=217&garnish%5B85%5D%5B%5D=246&dishamount%5B10035%5D=1&garnish%5B10035%5D%5B%5D=217&garnish%5B10035%5D%5B%5D=246&dishamount%5B10037%5D=1&garnish%5B10037%5D%5B%5D=217&garnish%5B10037%5D%5B%5D=246&dishamount%5B88%5D=1&garnish%5B88%5D%5B%5D=217&dishamount%5B90%5D=1&garnish%5B90%5D%5B%5D=217&dishamount%5B89%5D=1&garnish%5B89%5D%5B%5D=217&dishamount%5B91%5D=1&garnish%5B91%5D%5B%5D=227&dishamount%5B92%5D=1&garnish%5B92%5D%5B%5D=227&dishamount%5B93%5D=1&garnish%5B93%5D%5B%5D=227&dishamount%5B10034%5D=1&garnish%5B10034%5D%5B%5D=227&dishamount%5B96%5D=1&garnish%5B96%5D%5B%5D=227&dishamount%5B97%5D=1&garnish%5B97%5D%5B%5D=227&dishamount%5B98%5D=1&garnish%5B98%5D%5B%5D=227&dishamount%5B94%5D=1&dishamount%5B95%5D=1&dishamount%5B99%5D=1&dishamount%5B100%5D=1&dishamount%5B102%5D=1&dishamount%5B101%5D=1&dishamount%5B114%5D=1&dishamount%5B115%5D=1&dishamount%5B116%5D=1&dishamount%5B117%5D=1&dishamount%5B120%5D=1&dishamount%5B121%5D=1&dishamount%5B122%5D=1&dishamount%5B118%5D=1&dishamount%5B10008%5D=1&dishamount%5B10009%5D=1&dishamount%5B123%5D=1&dishamount%5B124%5D=1&dishamount%5B125%5D=1&dishamount%5B126%5D=1&dishamount%5B119%5D=1&dishamount%5B10027%5D=1&dishamount%5B127%5D=1&garnish%5B127%5D%5B%5D=224&dishamount%5B128%5D=1&garnish%5B128%5D%5B%5D=224&dishamount%5B129%5D=1&garnish%5B129%5D%5B%5D=224&dishamount%5B57%5D=1&dishamount%5B58%5D=1&dishamount%5B10017%5D=1&dishamount%5B59%5D=1&dishamount%5B60%5D=1&dishamount%5B10097%5D=1&dishamount%5B10098%5D=1&dishamount%5B10099%5D=1&dishamount%5B61%5D=1&dishamount%5B63%5D=1&dishamount%5B62%5D=1&dishamount%5B64%5D=1&dishamount%5B65%5D=1&dishamount%5B66%5D=1&dishamount%5B67%5D=1&dishamount%5B10100%5D=1&dishamount%5B10006%5D=1&dishamount%5B10018%5D=1&dishamount%5B10007%5D=1&dishamount%5B68%5D=1&dishamount%5B69%5D=1&dishamount%5B10020%5D=1&dishamount%5B71%5D=1&dishamount%5B70%5D=1&dishamount%5B130%5D=1&dishamount%5B10022%5D=1&dishamount%5B10023%5D=1&dishamount%5B10090%5D=1&dishamount%5B10091%5D=1&dishamount%5B10092%5D=1&dishamount%5B10093%5D=1&dishamount%5B10094%5D=1&dishamount%5B76%5D=1&dishamount%5B78%5D=1&dishamount%5B10081%5D=1&dishamount%5B10059%5D=1&dishamount%5B72%5D=1&dishamount%5B73%5D=1&dishamount%5B74%5D=1&dishamount%5B10096%5D=1&dishamount%5B10095%5D=1&dishamount%5B10061%5D=1&dishamount%5B10013%5D=1&dishamount%5B105%5D=1&dishamount%5B106%5D=1&dishamount%5B10016%5D=1&dishamount%5B104%5D=1&dishamount%5B107%5D=1&dishamount%5B109%5D=1&dishamount%5B133%5D=1&dishamount%5B112%5D=1&dishamount%5B10003%5D=1&dishamount%5B10014%5D=1&dishamount%5B10015%5D=1&dishamount%5B10021%5D=1&dishamount%5B10060%5D=1' --compressed`;
        $getDeliveryDetailResult = `curl 'http://www.cafetaria-online.nl/afronden'    -b test.jar  -c test.jar`;
        preg_match('/[a-z0-9]{40}/', $getDeliveryDetailResult, $matches);
        $csrfToken = $matches[0];
        $postDeliveryDetailsResult = `curl 'http://www.cafetaria-online.nl/afronden'    -b test.jar  -c test.jar -H 'Content-Type: application/x-www-form-urlencoded' -H 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8' -H 'Connection: keep-alive' --data 'menuorder%5Bcustomer_firstname%5D=jorn&menuorder%5Bcustomer_lastname%5D=oomen&menuorder%5Bcustomer_email%5D=jorn%2Btest%40freshheads.com&menuorder%5Bcustomer_phone_areacode%5D=6&menuorder%5Bcustomer_phone_number%5D=12043990&menuorder%5Bcustomer_address_street%5D=2&menuorder%5Bcustomer_address_number%5D=Provincialeweg&menuorder%5Bcustomer_address_suite%5D=&menuorder%5Bcustomer_address_city%5D=tilburg&menuorder%5Bpaymenttype%5D=contant&menuorder%5Bdeliverytime%5D=&menuorder%5Bremarks%5D=&menuorder%5B_token%5D=$csrfToken&newslettersignup=yes&afrondenbutton.x=46&afrondenbutton.y=24&afrondenbutton=finish'`;
        echo $postDeliveryDetailsResult;

        return;

    });

$console
    ->register('clear')
    ->setDefinition(
        array(
            new InputArgument('date', InputArgument::OPTIONAL, 'The date that you want to delete the order for.', 'now')
        )
    )
    ->setDescription('Clears bami orders for a given date')
    ->setCode(function (InputInterface $input, OutputInterface $output) {
        $orderModel = new OrderModel();

        $orders = $orderModel->getByDate();

        if ($orders == null) {
            $output->writeln(sprintf('<error>No bami disks to clear!</error>'));

            return;
        }

        $dialog = new DialogHelper();

        if (!$dialog->askConfirmation(
            $output,
            sprintf('<question>Question:</question> Are you sure you want to clear the order? (yes/no)'),
            false
        )
        ) {
            $output->writeln('<error>Order clear aborted</error>');

            return;
        }

        $orderModel->deleteByDate();

        $output->writeln('Bami disks order cleared!');
    });

$console->run();
