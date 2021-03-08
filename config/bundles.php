<?php

return [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle::class => ['all' => true],
    Symfony\Bundle\TwigBundle\TwigBundle::class => ['all' => true],
    Symfony\Bundle\WebProfilerBundle\WebProfilerBundle::class => ['dev' => true, 'test' => true],
    Symfony\Bundle\MonologBundle\MonologBundle::class => ['all' => true],
    Symfony\Bundle\DebugBundle\DebugBundle::class => ['dev' => true, 'test' => true],
    Symfony\Bundle\MakerBundle\MakerBundle::class => ['dev' => true],
    Ewll\LogExtraDataBundle\EwllLogExtraDataBundle::class => ['all' => true],
    Ewll\DBBundle\EwllDBBundle::class => ['all' => true],
    Ewll\MysqlMessageBrokerBundle\EwllMysqlMessageBrokerBundle::class => ['all' => true],
    Ewll\UserBundle\EwllUserBundle::class => ['all' => true],
    Ewll\MailerBundle\EwllMailerBundle::class => ['all' => true],
    Ewll\CrudBundle\EwllCrudBundle::class => ['all' => true],
    Sentry\SentryBundle\SentryBundle::class => ['all' => true],
];
