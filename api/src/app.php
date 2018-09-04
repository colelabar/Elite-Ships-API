<?php
namespace api;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
require './vendor/autoload.php';

//setup the App class and all of its endpoints
class App {

  private $app;
  public function __construct($db) {

    $config['db']['host'] = ['localhost'];
    $config['db']['user'] = ['root'];
    $config['db']['pass'] = ['root'];
    $config['db']['dbname'] = ['apidb'];

    $app = new \Slim\App(['settings' => $config]);

    $container = $app->getContainer();
    $container['db'] = $db;

    // set up the Monolog logger -> something I find to be really helpful 👌🏼
    $container['logger'] = function($c) {
        $logger = new \Monolog\Logger('my_logger');
        $file_handler = new \Monolog\Handler\StreamHandler('./logs/app.log');
        $logger->pushHandler($file_handler);
        return $logger;
    };

    // setting up the CRUD endpoints for the api
    //  get ALL ships
    $app->get('/ships', function (Request $request, Response $response) {
        $this->logger->addInfo("GET /ships");
        $ships = $this->db->query('SELECT * FROM ships')->fetchAll();
        $jsonResponse = $response->withJson($ships);
        return $jsonResponse;
    });

    // get an individual ship by ID
    $app->get('/ships/{id}', function (Request $request, Response $response, array $args) {
        $id = $args['id'];
        $this->logger->addInfo("GET /ships/" . $id);
        $ship = $this->db->query('SELECT * FROM ships WHERE id=' . $id)->fetch();

        if($ship){
          $response =  $response->withJson($ship);
        } else {
          $errorData = array('status' => 404, 'message' => 'not found');
          $response = $response->withJson($errorData, 404);
        }
        return $response;

    });

  }
  /**
   * Get an instance of the application.
   *
   * @return \Slim\App
   */
  public function get()
  {
      return $this->app;
  }
}
