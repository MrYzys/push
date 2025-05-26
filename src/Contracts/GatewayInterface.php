<?php


namespace BetterUs\Push\Contracts;


use BetterUs\Push\AbstractMessage;


interface GatewayInterface
{
    public function getName();

    public function getGatewayName();

    public function getAuthToken();

    public function pushNotice($to, AbstractMessage $message, array $options = []);
}