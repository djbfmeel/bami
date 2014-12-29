<?php

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPRuntimeException;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * @author Evert Harmeling <evertharmeling@gmail.com>
 */
class SocketManager
{
    const MESSAGE_TYPE = 'topic';
    const CHANNEL_NAME = 'amq.topic';
    const TOPIC_NAME = 'php-client';

    /**
     * @var AMQPStreamConnection
     */
    protected $conn;

    /**
     * @var AMQPChannel
     */
    protected $channel;

    /**
     * @var string
     */
    protected $queueName;

    public function __construct()
    {
        $this->conn = new AMQPStreamConnection('192.168.2.203', '5672', 'guest', 'guest');

        $this->channel = $this->conn->channel();
        list($this->queueName) = $this->channel->queue_declare('', false, true, true, false);
        $this->channel->exchange_declare(self::TOPIC_NAME, self::MESSAGE_TYPE, false, true, true, false);
    }

    /**
     * @param  AMQPMessage $message
     * @param  string $routingKey
     * @return bool
     */
    public function publish(AMQPMessage $message, $routingKey = '')
    {
        try {
            $this->channel->basic_publish($message, self::CHANNEL_NAME, $routingKey);

            return true;
        } catch (\Exception $e) {

            var_dump($e); die;
        }
    }

    /**
     * @param callable $callback
     * @param string $routingKey , default to all ('#')
     */
    public function consume(callable $callback, $routingKey = '#')
    {
        $this->channel->queue_bind($this->queueName, self::CHANNEL_NAME, $routingKey);
        $this->channel->basic_consume($this->queueName, '', false, true, false, false, $callback);
    }

    /**
     * @return array
     */
    public function receive()
    {
        return $this->channel->callbacks;
    }

    /**
     * @throws AMQPRuntimeException
     * @return mixed
     */
    public function wait()
    {
        return $this->channel->wait();
    }

    /**
     * Closes the channel and connection
     */
    public function close()
    {
        $this->channel->close();

        return (bool)$this->conn->close();
    }

}
