<?php

/**
 * DataInterface
 *
 * @author Dennis van Meel <dennis.van.meel@freshheads.com>
 */
abstract class DataInterface
{
    protected $database;

    protected $table;

    public function getConnection()
    {
        return new SQLite3('db/' . $this->database);
    }

    public function select($where = null)
    {
        $query = "SELECT * FROM  " . $this->table;

        if ($where != null) {
            $query = $query . " WHERE " . $where;
        }

        $connection = $this->getConnection();

        return $connection->query($query);
    }

    public function insert($data)
    {
        $columns = array_keys($data);

        $escape = function ($string) {
            return sprintf("'%s'", $string);
        };

        $query = "INSERT INTO " . $this->table . " (" . implode(',', $columns) . ") VALUES (" . implode(',', array_map($escape, $data)) . ");";

        $connection = $this->getConnection();

        return $connection->query($query);
    }

    public function update($data)
    {

    }

    public function delete($where)
    {
        $query = "DELETE FROM " . $this->table . " WHERE " . $where . ";";

        $connection = $this->getConnection();

        $connection->query($query);
    }
}
