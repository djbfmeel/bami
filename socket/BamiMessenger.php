<?php

require_once  ('SocketManager.php');

use PhpAmqpLib\Message\AMQPMessage;

/**
 * BamiMessenger
 *
 * @author Dennis van Meel <dennis.van.meel@freshheads.com>
 */
class BamiMessenger
{
    protected $socketManager;

    function __construct()
    {
        $this->socketManager = new SocketManager();
    }

    public function addOrderMessage($firstName, $lastName, $image, $amount){
        $object = new stdClass();
        $object->firstName = $firstName;
        $object->lastName = $lastName;
        $object->image = $image;
        $object->amount = $amount;

        $json = json_encode($object);

        $message = new AMQPMessage($json);
        $this->socketManager->publish($message, 'bami.add');
    }
}
