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
/*
TipoId
RC - Registro Civil -1
TI - Tarjeta de identidad. -2
CC - Cédula de ciudadanía -3
CE - Cédula de extranjería -4
PA - Pasaporte -5 
*/
$app1->post('/insertarCliente', function (Request $request, Response $response) {
    $name = $request->getAttribute('name');
    $postData = $request->getParsedBody();
    $gump = new GUMP('es');
    $gump->validation_rules(
            array(
            'tipoId' => 'required|contains,1 2 3 4 5',
            'Id' => 'required|max_len,15',
            'nombre' => 'required|max_len,255',
            'telefono' => 'required|max_len,50',
            'e_mail' => 'required|valid_email|max_len,50',
            'direccion' => 'required|max_len,255',
            'idEmpresa' => 'required|numeric'
            )
        );
    $gump->filter_rules(
            array(
            'tipoId' => 'trim',
            'Id' => 'trim',
            'nombre' => 'trim',
            'telefono' => 'trim',
            'e_mail' => 'trim',
            'direccion' => 'trim',
            'idEmpresa' => 'trim'
            )
        );
    $postData = $gump->sanitize($postData);
    $validated_data = $gump->run($postData);
     if ($validated_data === false) {
            $resultado['codigo_respuesta'] = 400;
            $resultado['error'] = 1;
            $resultado['mensaje'] = $gump->get_errors_array();
            //print_r($resultado);
        }else{
            $cliente=consultar_cliente($postData['tipoId'],$postData['Id'],$postData['idEmpresa']);
            $empresa=consultar_empresa($postData['idEmpresa']);
            if(count($cliente)>0){
                $resultado['codigo_respuesta'] = 400;
                $resultado['error'] = 2;
                $resultado['mensaje'] = "Este cliente ya se encuentra registrado, no es necesario volverlo a registrar";
            }else{
                  if(count($empresa)==0){
                $resultado['codigo_respuesta'] = 400;
                $resultado['error'] = 2;
                $resultado['mensaje'] = "Esta empresa no existe, por favor registrar la empresa primero.";
                  }else{
                    $resultado=insertar_cliente($postData);
                  }
                
            }
        }

    return $response->withJson($resultado);
});

$app1->post('/ConsultarCliente', function (Request $request, Response $response) {
    $name = $request->getAttribute('name');
    $postData = $request->getParsedBody();
    $gump = new GUMP('es');
    $gump->validation_rules(
            array(
            'tipoId' => 'required|contains,1 2 3 4 5',
            'Id' => 'required|max_len,15',
            'idEmpresa' => 'required|numeric'
            )
        );
    $gump->filter_rules(
            array(
            'tipoId' => 'trim',
            'Id' => 'trim',
            'idEmpresa' => 'trim'
            )
        );
    $postData = $gump->sanitize($postData);
    $validated_data = $gump->run($postData);
     if ($validated_data === false) {
        $resultado['codigo_respuesta'] = 400;
        $resultado['error'] = 1;
        $resultado['mensaje'] = $gump->get_errors_array();
     }else{
        $cliente=consultar_cliente($postData['tipoId'],$postData['Id'],$postData['idEmpresa']);
        if(count($cliente)>0){
            $resultado=$cliente;

        }else{
           $resultado['codigo_respuesta'] = 400;
           $resultado['error'] = 5;
           $resultado['mensaje'] = "Este cliente no existe"; 
        }

     }
    return $response->withJson($resultado);

    });

$app1->post('/EditarCliente', function (Request $request, Response $response) {
    $name = $request->getAttribute('name');
    $postData = $request->getParsedBody();
    $gump = new GUMP('es');
    $gump->validation_rules(
            array(
            'tipoId' => 'required|contains,1 2 3 4 5',
            'Id' => 'required|max_len,15',
            'nombre' => 'required|max_len,255',
            'telefono' => 'required|max_len,50',
            'e_mail' => 'required|valid_email|max_len,50',
            'direccion' => 'required|max_len,255',
            'idEmpresa' => 'required|numeric'
            )
        );
    $gump->filter_rules(
            array(
            'tipoId' => 'trim',
            'Id' => 'trim',
            'nombre' => 'trim',
            'telefono' => 'trim',
            'e_mail' => 'trim',
            'direccion' => 'trim',
            'idEmpresa' => 'trim'
            )
        );
    $postData = $gump->sanitize($postData);
    $validated_data = $gump->run($postData);
     if ($validated_data === false) {
            $resultado['codigo_respuesta'] = 400;
            $resultado['error'] = 1;
            $resultado['mensaje'] = $gump->get_errors_array();
            //print_r($resultado);
        }else{
           $cliente=consultar_cliente($postData['tipoId'],$postData['Id'],$postData['idEmpresa']); 
           if(count($cliente)>0){
            $resultado=actualizar_cliente($postData);

        }else{
           $resultado['codigo_respuesta'] = 400;
           $resultado['error'] = 5;
           $resultado['mensaje'] = "Este cliente no existe"; 
        }

        }
        return $response->withJson($resultado);
    });


function actualizar_cliente($postData){
    try{
    $myPDO = new PDO('sqlite:../bd/creditos.db');
    $stmt = $myPDO->prepare("UPDATE cliente SET Nombre=?, Telefono=?, email=?, direccion=? WHERE  tipoId=? AND Id=? AND idEmpresa=?");
    $stmt->execute([$postData['nombre'], $postData['telefono'], $postData['e_mail'], $postData['direccion'], $postData['tipoId'],$postData['Id'], $postData['idEmpresa']]);
    $resultado['codigo_respuesta'] = 200;
    $resultado['mensaje'] = "cliente actualizado correctamente";
    }catch(Exception $p){
        $resultado['codigo_respuesta'] = 400;
        $resultado['error'] = 3;
        $resultado['mensaje'] = "Se presentó un error en BD: ".$p->getMessage();
    }
   return $resultado;

}

function insertar_cliente($postData){
    try{
    $myPDO = new PDO('sqlite:../bd/creditos.db');
    $stmt = $myPDO->prepare("INSERT INTO cliente(tipoId, Id, Nombre, Telefono, email, direccion, idEmpresa) VALUES(?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$postData['tipoId'],$postData['Id'], $postData['nombre'], $postData['telefono'], $postData['e_mail'], $postData['direccion'], $postData['idEmpresa']]);
    $resultado['codigo_respuesta'] = 200;
    $resultado['mensaje'] = "cliente registrado correctamente";
    }catch(Exception $p){
        $resultado['codigo_respuesta'] = 400;
        $resultado['error'] = 3;
        $resultado['mensaje'] = "Se presentó un error en BD: ".$p->getMessage();
    }
   return $resultado;
}

function consultar_cliente($tipoId, $id, $idEmpresa){
    $myPDO = new PDO('sqlite:../bd/creditos.db');
    $stmt = $myPDO->prepare('SELECT * FROM cliente WHERE tipoId=:tipoId AND Id=:id AND idEmpresa=:idEmpresa');
    $stmt->bindValue(':tipoId', $tipoId);
    $stmt->bindValue(':id', $id);
    $stmt->bindValue(':idEmpresa', $idEmpresa);
    $stmt->execute();
    // Fetch the records so we can display them in our template.
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $result;
}

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
