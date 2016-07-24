<?php

/*
    MistAPI ver. 0.1beta
    --------------------
    Extend this class to create your API.
*/
abstract class MistAPI{

    private $endpoint = '';
    private $method = '';
    private $args = Array();

    /**
     * MistAPI constructor.
     * @param $request
     * @param array $allow
     * @throws Exception
     */
    public function __construct($request, $allow = ['*'])
    {
        $this->CORS($allow);

        $this->args = explode('/', rtrim($request, '/'));
        array_shift($this->args);

        $this->endpoint = array_shift($this->args);

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

        switch($this->method) {
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

            case 'OPTIONS':
                header("Allow: " . implode(", ", $allow));
                break;

            default:
                $this->_response(["error" => "invalid-method", "method" => $this->method], 405);
                break;
        }

    }

    private function CORS($allow) {
        header("Access-Control-Allow-Headers: Accept, Content-Type, Origin");
        header("Access-Control-Allow-Methods: " . implode(", ", $allow));
        header("Access-Control-Allow-Origin: *");
        header("Content-Type: application/json");
    }

    public function ProcessAPI() {
        if ($this->method == "OPTIONS") return $this->_response();

        $res = false;
        $func = strtolower($this->method) . '_' . $this->endpoint;
        if (method_exists($this, $func)) $res = $this->{$func}($this->args);

        if ($res)
            return $this->_response($res);
        else
            return $this->_response(["error" => "not-found", "endpoint" => $this->endpoint], 404);
    }

    private function _response($data = false, $status = 200) {
        header("HTTP/1.1 " . $status . " " . $this->_requestStatus($status));
        if ($data) return json_encode($data);
        else return "";
    }

    private function _cleanInputs($data) {
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

    private function _requestStatus($code) {
        $status = array(
            200 => 'OK',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            500 => 'Internal Server Error',
        );
        return ($status[$code])?$status[$code]:$status[500];
    }

}

