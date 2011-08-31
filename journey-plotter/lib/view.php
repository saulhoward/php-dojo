<?php

/**
 * Journey Plotter
 *
 **/

abstract class plotterView
{
    protected $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function renderPage($format)
    {
        $params = NULL;
        if (isset($_GET))
        {
            $params = $_GET;
        }
        switch ($format) {
        case 'json':
            header("Content-Type: json");
            echo $this->getJson($params);
            break;
        case 'html':
        default:
            header("Content-Type: html");
            echo $this->getHtml($params);
        }
    }

    abstract function getHtml($params);
}


