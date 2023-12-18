<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class geralController extends Controller{
    //TRATAMENTO DE VALOR MONETÁRIO DO SERVIDOR
    public static function trataValor($valor,$tratamento){
        switch($tratamento){
            case "0": //TRATA OS VALORES QUE VEM DO BANCO DE DADOS PARA IMPRIMIR NA TELA
                return number_format($valor,2,",",",");
            break;
            case "1": //TRATA OS VALORES QUE VEM DO SISTEMA PARA FAZER CALCULOS OU ENVIAR PARA O BANCO DE DADOS
                $envBanco = $valor; //VALOR
                $firstChar = strtok($envBanco,","); //NUMEROS ANTES DA PRIMEIRA VIRGULA
                $lastChar = strstr($envBanco,","); //NUMEROS DEPOIS DA VIRGULA
                $valorTratado = str_replace(",","",$firstChar.ltrim($lastChar,",")); //JUNTA OS DOIS E RETIRAM AS VIRGULAS JUNTO COM OS CENTAVOS
                $strlenValor = strlen($valorTratado) -2; //BUSCA OS DOIS ULTIMOS CARACTERES QUE SÃO OS CENTAVOS
                return substr_replace($valorTratado,".",$strlenValor,0); //COLOCA UM PONTO NOS DOIS ULTIMOS CARACTERES PARA BUSCAR OS CENTAVOS
            break;
        }
    }
    //TRATAMENTO DE DATAS
    public static function data($data,$tipo){
        return date($tipo, strtotime($data));
    }
}
