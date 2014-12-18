<?php

/**
 * RestInterface
 *
 * @author Dennis van Meel <dennis.van.meel@freshheads.com>
 */
abstract class RestInterface
{
    /**
     * Method
     * The HTTP request method (GET, PUT, POST, DELETE)
     * @var string
     */
    protected $method = '';

    /**
     * Collection
     * The collection requested in the URI
     * @var string
     */
    protected $collection = '';

    /**
     * Resource
     * The resource requested in the URI
     */
    protected $resource = null;

    /**
     * Args
     * Additional arguments added to the URI
     * @var array
     */
    protected $args = [];

    /**
     * File
     * Stores the input of a PUT request
     */
    protected $file = null;

    public function __construct($request)
    {
        header("Access-Control-Allow-Orgin: *");
        header("Access-Control-Allow-Methods: *");
        header("Content-Type: application/json");

        $this->args = explode('/', rtrim($request, '/'));
        $this->collection = array_shift($this->args);
        if (array_key_exists(0, $this->args) && !is_numeric($this->args[0])) {
            $this->resource = array_shift($this->args);
        }

        $this->method = $_SERVER['REQUEST_METHOD'];
        if ($this->method == 'POST' && array_key_exists('HTTP_X_HTTP_METHOD', $_SERVER)) {
            if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'DELETE') {
                $this->method = 'DELETE';
            } else if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'PUT') {
                $this->method = 'PUT';
            } else {
                throw new Exception("Unexpected Header");
            }
        }

        switch ($this->method) {
            case 'DELETE':
            case 'POST':
                $this->request = $this->_cleanInputs($_POST);
                break;
            case 'GET':
                $this->request = $this->_cleanInputs($_GET);
                break;
            case 'PUT':
                $this->request = $this->_cleanInputs($_GET);
                $this->file = file_get_contents("php://input");
                break;
            default:
                $this->_response('Invalid Method', 405);
                break;
        }
    }

    public function processAPI()
    {
        if ((int)method_exists($this, $this->collection) > 0) {
            return $this->_response($this->{$this->collection}($this->args));
        }

        return $this->_response("No Endpoint: $this->collection", 404);
    }

    private function _response($data, $status = 200)
    {
        header("HTTP/1.1 " . $status . " " . $this->_requestStatus($status));

        return json_encode($data);
    }

    private function _cleanInputs($data)
    {
        $clean_input = Array();
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $clean_input[$k] = $this->_cleanInputs($v);
            }
        } else {
            $clean_input = trim(strip_tags($data));
        }

        return $clean_input;
    }

    private function _requestStatus($code)
    {
        $status = array(
            200 => 'OK',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            500 => 'Internal Server Error',
        );

        return ($status[$code]) ? $status[$code] : $status[500];
    }

}
