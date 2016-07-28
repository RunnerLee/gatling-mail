# gatling-mail

加特林机枪, 突突突突突突突突突

还是抄的

想法来源于laravel内置的邮件服务， 集成了sendcloud的发送驱动以及smtp的发送驱动。

smtp也是抄的，抄PHPMailer的。

### 安装
```
composer -vvv require runner/gatling-mail
```

### 使用



内置了支持简单替换变量的模板

例如，当使用 sendcloud 的普通发送或者 smtp 发送时，可以这么用：

```
(new Email())->content(function(Message $message) {
    $message->setName('test message')
            ->setContent('test content by {username}')
            ->setParameters([
                'username' => 'runnerlee',
            ]);
});
```



当使用 sendcloud 的模板发送时，则因为 `xsmtpapi` 字段的原因，需要把这么配置：

```

(new Email())->content(function(Message $message) {
    $message->setName('test message')
            ->setContent('test content by %username%')
            ->setDelimiter('%', '%')
            ->setParameters([
                'username' => ['runnerlee'],
            ]);
});

```

具体原因请参考 sendcloud 的接口文档


```

// 实例化发送驱动,
$sendcloud = new \Runner\GatlingMail\Drivers\SendCloudDriver([
    'api_user' => 'shuai',
    'api_key'  => 'bi',
]);

// 实例化发送器, 装入发送驱动,
$mailer = new \Runner\GatlingMail\Mailer($sendcloud);

// 实例化邮件, 类似于用客户端发送邮件时的新建邮件动作
$email = (new \Runner\GatlingMail\Email())
            ->subject('this is a test email')
            ->to('runnerleer@gmail.com')
            ->from('master@gov.cn', '收水费了')
            ->content(function(\Runner\GatlingMail\Message $message) {
                $message->setName('test message')
                        ->setContent('test content by %username%')
                        ->setDelimiter('%', '%')
                        ->setParameters([
                            'username' => 'runnerlee',
                        ]);
            })
            ->tag('10086')
            ->attach('address.txt', __DIR__ . '/address.txt');

// 装入邮件, biubiubiu
$mailer->setEmail($email)->send();

```


### 在 fastD(~1.4) 中使用

##### 注册为辅助服务
`# app/Application.php`
```
public function registerService()
{
    // 配置发送驱动
    $sendCloud = new \Runner\GatlingMail\Drivers\SendCloudDriver([
        'api_user' => '',
        'api_key'  => '',
    ]);
    $email = (new \Runner\GatlingMail\Email())->from('contact@runnerlee.com', 'runnerlee');
    return [
        'mailer' => (new \Runner\GatlingMail\Mailer($sendCloud)),
    ];
}
```

`# src/DemoBundle/Events/Demo.php`
```

public function indexAction()
{
    $this->get('mailer')
         ->getEmail()
         ->to('runnerleer@gmail.com')
         ->content(function(\Runner\GatlingMail\Message $message) {
             $message->setName('test message')
                 ->setContent('test content by {username}')
                 ->setParameters([
                     'username' => 'runnerlee',
                 ]);
         })
         ->subject('this is a test email');

    $this->get('mailer')->send();
}

```

### 参考
[http://www.cnblogs.com/sdgwc/p/3324368.html](http://www.cnblogs.com/sdgwc/p/3324368.html)

[https://mozillazg.com/2013/07/python-send-email-set-priority.html](https://mozillazg.com/2013/07/python-send-email-set-priority.html)