<?php
/**
 * @author: RunnerLee
 * @email: runnerleer@gmail.com
 * @time: 16-7-14 下午6:45
 */
require __DIR__ . '/../vendor/autoload.php';

$sendcloud = new \Runner\GatlingMail\Drivers\SendCloudDriver([
]);

//$sendcloud->useTemplate(true);

$mailer = new \Runner\GatlingMail\Mailer($sendcloud);

$email = (new \Runner\GatlingMail\Email())
            ->to('runnerleer@gmail.com')
            ->from('master@gov.cn', '收水费了')
            ->content(function(\Runner\GatlingMail\Message $message) {
                $message->setName('test message')
                        ->setContent('test content by %username%')
                        ->setParameters([
                            'username' => ['runnerlee'],
                        ]);
            })
            ->subject('this is a test email')
            ->tag('18133')
            ->attach('address.txt', __DIR__ . '/address.txt')
            ->attach('union.jpeg', __DIR__ . '/bgUnion.jpg');

var_dump($mailer->setEmail($email)->send());