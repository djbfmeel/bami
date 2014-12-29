<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);

require_once('RestInterface.php');
require_once('../db/OrderModel.php');
require_once('../db/UserModel.php');

/**
 * Api
 *
 * @author Dennis van Meel <dennis.van.meel@freshheads.com>
 */
class Api extends RestInterface
{
    public function __construct($request)
    {
        parent::__construct($request);
    }

    /**
     * @return string
     */
    protected function orders()
    {
        $userModel = $this->getUserModel();
        $orderModel = $this->getOrderModel();

        if ($this->method == 'GET') {

            $orders = $orderModel->getByDate();

            $result = [];
            foreach ($orders as $order) {
                $orderObject = new stdClass();
                $orderObject->id = $order['id'];
                $orderObject->amount = $order['amount'];
                $orderObject->date = $order['date'];

                $user = $userModel->getById($order['user']);
                $userObject = new stdClass();
                $userObject->firstName = $user['first_name'];
                $userObject->lastName = $user['last_name'];
                $userObject->image = $user['image'];

                $orderObject->user = $userObject;

                $result[] = $orderObject;
            }

            return $result;

        } else {
            return "Only accepts GET requests";
        }
    }

    private function getUserModel()
    {
        $model = new UserModel();
        $model->setPath('../db/');
        return $model;
    }

    private function getOrderModel(){
        $model = new OrderModel();
        $model->setPath('../db/');
        return $model;
    }

}
