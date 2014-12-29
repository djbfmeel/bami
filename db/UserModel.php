<?php

require_once('DataInterface.php');

/**
 * UserModel
 *
 * @author Dennis van Meel <dennis.van.meel@freshheads.com>
 */
class UserModel extends DataInterface
{
    function __construct()
    {
        $this->database = 'bamidb';
        $this->table = 'USERS';
    }

    public function add($firstName, $lastName, $shortName, $image = 'default.jpg', $tag = null)
    {
        $data = [];
        $data['FIRST_NAME'] = $firstName;
        $data['LAST_NAME'] = $lastName;
        $data['SHORT_NAME'] = $shortName;
        $data['IMAGE'] = $image;
        $data['TAG'] = $tag;

        $this->insert($data);
    }

    public function getAll()
    {
        $result = $this->select();

        echo json_encode($this->parseUser($result));
    }

    public function getByShortName($shortName)
    {
        $where = "SHORT_NAME = '" . $shortName . "'";

        $result = $this->select($where);
        $return = $this->parseUser($result);

        if(!isset($return[0])){
            return null;
        }else{
            return $return[0];
        }
    }

    public function getById($id)
    {
        $where = "ID = '" . $id . "'";

        $result = $this->select($where);
        $return = $this->parseUser($result);

        if(!isset($return[0])){
            return null;
        }else{
            return $return[0];
        }
    }

    public function getByTag($tagId)
    {
        $where = "TAG = '" . $tagId . "'";

        $result = $this->select($where);

        echo json_encode($this->parseUser($result));
    }

    private function parseUser($input)
    {
        $result = [];
        while ($row = $input->fetchArray()) {
            $user = [];

            $user['id'] = $row['ID'];
            $user['first_name'] = $row['FIRST_NAME'];
            $user['last_name'] = $row['LAST_NAME'];
            $user['short_name'] = $row['SHORT_NAME'];
            $user['image'] = $row['IMAGE'];
            $user['tag_id'] = $row['TAG'];

            $result[] = $user;
        }

        return $result;
    }

}
