<?php


use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require __DIR__ . '/../vendor/autoload.php';
$configuration = [
    'settings' => [
        'displayErrorDetails' => true,
    ],
];

$c = new \Slim\Container($configuration);
$app1 = new \Slim\App($c);

$app1->options('/{routes:.+}', function ($request, $response, $args) {
    return $response;
});

$app1->add(function ($req, $res, $next) {
    $response = $next($req, $res);
    return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization, Auth')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});

$app1->post('/hello', function (Request $request, Response $response) {
    
    $name = $request->getAttribute('name');
    $postData = $request->getParsedBody();
    $gump = new GUMP('es');
    $gump->validation_rules(
            array(
            'nombre' => 'required|max_len,255',
            'nit' => 'required|numeric|max_len,255'
            )
        );
    $gump->filter_rules(
            array(
            'nombre' => 'trim',
            'nit' => 'trim'
            )
        );
    $postData = $gump->sanitize($postData);
    $validated_data = $gump->run($postData);
     if ($validated_data === false) {
            $resultado['codigo_respuesta'] = 400;
            $resultado['error'] = 1;
            $resultado['mensaje'] = $gump->get_errors_array();
            //print_r($resultado);
        } else { 

        }
    return $response->withJson($resultado);
});

$app1->post('/crearEmpresa', function (Request $request, Response $response) {
    $name = $request->getAttribute('name');
    $postData = $request->getParsedBody();
    $gump = new GUMP('es');
    $gump->validation_rules(
            array(
            'nombre' => 'required|max_len,255',
            'nit' => 'required|numeric|max_len,255'
            )
        );
    $gump->filter_rules(
            array(
            'nombre' => 'trim',
            'nit' => 'trim'
            )
        );
    $postData = $gump->sanitize($postData);
    $validated_data = $gump->run($postData);
     if ($validated_data === false) {
            $resultado['codigo_respuesta'] = 400;
            $resultado['error'] = 1;
            $resultado['mensaje'] = $gump->get_errors_array();
            //print_r($resultado);
        } else { 

            $empresa=consultar_empresa($postData['nit']);
            if(count($empresa)>0){
                $resultado['codigo_respuesta'] = 400;
                $resultado['error'] = 2;
                $resultado['mensaje'] = "El nit de la empresa ya existe, cambiarla por favor";
            }else{
                $resultado=insertar_empresa($postData['nit'],$postData['nombre']);
            }
            

        }
    return $response->withJson($resultado);
    
    
    
});

function insertar_empresa($nit, $nombre){
    try{
    $myPDO = new PDO('sqlite:../bd/creditos.db');
    $stmt = $myPDO->prepare("INSERT INTO empresa(nit, nombre) VALUES(?, ?)");
    $stmt->execute([$nit,$nombre]);
    $resultado['codigo_respuesta'] = 200;
    $resultado['mensaje'] = "Empresa registrada correctamente";
    }catch(Exception $p){
        $resultado['codigo_respuesta'] = 400;
        $resultado['error'] = 3;
        $resultado['mensaje'] = "Se presentó un error en BD: ".$p->getMessage();
    }
   return $resultado;
}

function consultar_empresa($nit){
    $myPDO = new PDO('sqlite:../bd/creditos.db');
    $stmt = $myPDO->prepare('SELECT * FROM empresa WHERE nit=:nit');
    $stmt->bindValue(':nit', $nit);
    $stmt->execute();
    // Fetch the records so we can display them in our template.
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $result;

}




$app1->run();
