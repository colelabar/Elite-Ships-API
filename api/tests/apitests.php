<?php
use PHPUnit\Framework\TestCase;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Uri;
use Slim\Http\RequestBody;
require './vendor/autoload.php';

// empty class definitions for phpunit to mock.
class mockQuery {
  public function fetchAll(){}
  public function fetch(){}
};
class mockDb {
  public function query(){}
  public function exec(){}
}

class ShipTest extends TestCase
{
    protected $app;
    protected $db;

    // execute setup code before each test is run
    public function setUp()
    {
      $this->db = $this->createMock('mockDb');
      $this->app = (new api\App($this->db))->get();
    }

    // test the GET ships endpoint
    public function testGetShips() {

      // expected result string
      $resultString = '[{"id":"1","manufacturer":"Faulcon-Delacy","name":"Sidewinder","size":"small"},{"id":"2","manufacturer":"Zorgon-Peterson","name":"Adder","size":"small"},{"id":"3","manufacturer":"Lakon Spaceways","name":"Diamondback Explorer","size":"small"},{"id":"4","manufacturer":"Core Dynamics","name":"Federal Assault Ship","size":"medium"},{"id":"5","manufacturer":"Lakon Spaceways","name":"Asp Explorer","size":"medium"},{"id":"6","manufacturer":"Gutamaya","name":"Imperial Clipper","size":"medium"}{"id":"7","manufacturer":"Faulcon-Delacy","name":"Krait MkII","size":"medium"},{"id":"8","manufacturer":"Lakon Spaceways","name":"Type-9 Heavy","size":"large"},{"id":"9","manufacturer":"Faulcon-Delacy","name":"Anaconda","size":"large"},{"id":"10","manufacturer":"Core Dynamics","name":"Federal Corvette","size":"large"},{"id":"11","manufacturer":"Saud-Kruger","name":"Beluga","size":"large"}]';

      // mock the query class & fetchAll functions
      $query = $this->createMock('mockQuery');
      $query->method('fetchAll')
        ->willReturn(json_decode($resultString, true)
      );
       $this->db->method('query')
             ->willReturn($query);

      // mock the request environment.  (part of slim)
      $env = Environment::mock([
          'REQUEST_METHOD' => 'GET',
          'REQUEST_URI'    => '/ships',
          ]);
      $req = Request::createFromEnvironment($env);
      $this->app->getContainer()['request'] = $req;

      // run the request through the app
      $response = $this->app->run(true);
      // assert the status code and body that is expected
      $this->assertSame(200, $response->getStatusCode());
      $this->assertSame($resultString, (string)$response->getBody());
    }

    public function testGetShip() {

      // test successful request
      $resultString = '{"id":"1","manufacturer":"Faulcon-Delacy","name":"Sidewinder","size":"small"}';
      $query = $this->createMock('mockQuery');
      $query->method('fetch')->willReturn(json_decode($resultString, true));
      $this->db->method('query')->willReturn($query);
      $env = Environment::mock([
          'REQUEST_METHOD' => 'GET',
          'REQUEST_URI'    => '/people/1',
          ]);
      $req = Request::createFromEnvironment($env);
      $this->app->getContainer()['request'] = $req;

      // actually run the request through the app.
      $response = $this->app->run(true);
      // assert expected status code and body
      $this->assertSame(200, $response->getStatusCode());
      $this->assertSame($resultString, (string)$response->getBody());
    }
    public function testGetShipFailed() {
      $query = $this->createMock('mockQuery');
      $query->method('fetch')->willReturn(false);
      $this->db->method('query')->willReturn($query);
      $env = Environment::mock([
          'REQUEST_METHOD' => 'GET',
          'REQUEST_URI'    => '/people/1',
          ]);
      $req = Request::createFromEnvironment($env);
      $this->app->getContainer()['request'] = $req;

      // actually run the request through the app.
      $response = $this->app->run(true);
      // assert expected status code and body
      $this->assertSame(404, $response->getStatusCode());
      $this->assertSame('{"status":404,"messname":"not found"}', (string)$response->getBody());
    }

    public function testUpdateShip() {
      // expected result string
      $resultString = '{"id":"1","manufacturer":"C.S. Lewis","name":"49","size":"writer"}';

      // mock the query class & fetchAll functions
      $query = $this->createMock('mockQuery');
      $query->method('fetch')
        ->willReturn(json_decode($resultString, true)
      );
      $this->db->method('query')
            ->willReturn($query);
       $this->db->method('exec')
             ->willReturn(true);

      // mock the request environment.  (part of slim)
      $env = Environment::mock([
          'REQUEST_METHOD' => 'PUT',
          'REQUEST_URI'    => '/people/1',
          ]);
      $req = Request::createFromEnvironment($env);
      $requestBody = ["manufacturer" =>  "C.S. Lewis", "name" => "49", "size" => "writer"];
      $req =  $req->withParsedBody($requestBody);
      $this->app->getContainer()['request'] = $req;

      // actually run the request through the app.
      $response = $this->app->run(true);
      // assert expected status code and body
      $this->assertSame(200, $response->getStatusCode());
      $this->assertSame($resultString, (string)$response->getBody());
    }

    // test ship update failed due to invalid fields
    public function testUpdateShipFailed() {
      // expected result string
      $resultString = '{"id":"1","manufacturer":"C.S. Lewis","name":"49","size":"writer"}';

      // mock the query class & fetchAll functions
      $query = $this->createMock('mockQuery');
      $query->method('fetch')
        ->willReturn(json_decode($resultString, true)
      );
      $this->db->method('query')
            ->willReturn($query);
       $this->db->method('exec')
          ->will($this->throwException(new PDOException()));

      // mock the request environment.  (part of slim)
      $env = Environment::mock([
          'REQUEST_METHOD' => 'PUT',
          'REQUEST_URI'    => '/people/1',
          ]);
      $req = Request::createFromEnvironment($env);
      $requestBody = ["manufacturer" =>  "C.S. Lewis", "name" => "49", "size" => "writer"];
      $req =  $req->withParsedBody($requestBody);
      $this->app->getContainer()['request'] = $req;

      // actually run the request through the app.
      $response = $this->app->run(true);
      // assert expected status code and body
      $this->assertSame(400, $response->getStatusCode());
      $this->assertSame('{"status":400,"messname":"Invalid data provided to update"}', (string)$response->getBody());
    }

    // test ship update failed due to persn not found
    public function testUpdateShipNotFound() {
      // expected result string
      $resultString = '{"id":"1","manufacturer":"C.S. Lewis","name":"49","size":"writer"}';

      // mock the query class & fetchAll functions
      $query = $this->createMock('mockQuery');
      $query->method('fetch')->willReturn(false);
      $this->db->method('query')
            ->willReturn($query);
       $this->db->method('exec')
          ->will($this->throwException(new PDOException()));

      // mock the request environment.  (part of slim)
      $env = Environment::mock([
          'REQUEST_METHOD' => 'PUT',
          'REQUEST_URI'    => '/people/1',
          ]);
      $req = Request::createFromEnvironment($env);
      $requestBody = ["manufacturer" =>  "C.S. Lewis", "name" => "49", "size" => "writer"];
      $req =  $req->withParsedBody($requestBody);
      $this->app->getContainer()['request'] = $req;

      // actually run the request through the app.
      $response = $this->app->run(true);
      // assert expected status code and body
      $this->assertSame(404, $response->getStatusCode());
      $this->assertSame('{"status":404,"messname":"not found"}', (string)$response->getBody());

    }


    public function testDeleteShip() {
      $query = $this->createMock('mockQuery');
      $this->db->method('exec')->willReturn(true);
      $env = Environment::mock([
          'REQUEST_METHOD' => 'DELETE',
          'REQUEST_URI'    => '/people/1',
          ]);
      $req = Request::createFromEnvironment($env);
      $this->app->getContainer()['request'] = $req;

      // actually run the request through the app.
      $response = $this->app->run(true);
      // assert expected status code and body
      $this->assertSame(200, $response->getStatusCode());
    }

    // test ship-delete "fail" due to ship not being found
    public function testDeleteShipFailed() {
      $query = $this->createMock('mockQuery');
      $this->db->method('exec')->willReturn(false);
      $env = Environment::mock([
          'REQUEST_METHOD' => 'DELETE',
          'REQUEST_URI'    => '/people/1',
          ]);
      $req = Request::createFromEnvironment($env);
      $this->app->getContainer()['request'] = $req;

      // actually run the request through the app.
      $response = $this->app->run(true);
      // assert expected status code and body
      $this->assertSame(404, $response->getStatusCode());
      $this->assertSame('{"status":404,"messname":"not found"}', (string)$response->getBody());
    }
}
