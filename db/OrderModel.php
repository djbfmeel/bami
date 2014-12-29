<?php

require_once('DataInterface.php');

/**
 * OrderModel
 *
 * @author Dennis van Meel <dennis.van.meel@freshheads.com>
 */
class OrderModel extends DataInterface
{
    function __construct()
    {
        $this->database = 'bamidb';
        $this->table = 'ORDERS';
    }

    public function placeOrder($userId, $amount)
    {
        $data = [];
        $data['AMOUNT'] = $amount;
        $data['ORDER_DATE'] = date('Y-m-d');
        $data['USER'] = $userId;

        $this->insert($data);
    }

    public function getByDate($date = null)
    {
        if ($date == null) {
            $date = date('Y-m-d');
        }

        $where = "ORDER_DATE = '" . $date . "'";

        $result = $this->select($where);

        $return = $this->parseOrder($result);

        if(!isset($return[0])){
            return null;
        }else{
            return $return;
        }
    }

    public function deleteByDate($date = null){
        if ($date == null) {
            $date = date('Y-m-d');
        }

        $where = "ORDER_DATE = '" . $date . "'";

        $this->delete($where);

        return true;
    }

    private function parseOrder($input)
    {
        $result = [];
        while ($row = $input->fetchArray()) {
            $order = [];

            $order['id'] = $row['ID'];
            $order['amount'] = $row['AMOUNT'];
            $order['date'] = $row['ORDER_DATE'];
            $order['user'] = $row['USER'];

            $result[] = $order;
        }

        return $result;
    }

}
