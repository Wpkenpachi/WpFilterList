<?php

namespace Wpkenpachi\Wpfilterlist;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class FilterList extends Controller
{
    // Input inicial
    private $input              = null;

    // Configurações de filtro e ordenação
    private $agrupamentos       = null;
    private $ordens             = null; 

    // arrays temporários
    private $agrupados          = null;
    private $ordenados          = null;

    // Array final
    private $return             = null;

    // public function __construct(array $input){
    //     $this->input = $input;
    // }

    // public static function boot(array $array){
    //     return new self( $array );
    // }

    public function __boot(array $input){
        $this->input = $input;
        return $this;
    }

    public static function boot( array $array ){
        return (new self())->__boot($array);
    }

    public function agrupamentos($agrupamentos){
        if( is_array($agrupamentos) ){
            $this->agrupamentos = $agrupamentos;
        }else{
            $this->agrupamentos[] = func_get_args();
        }
        $this->countAgrupamentos = count( $agrupamentos );
        return $this;
    }

    public function ordenamentos(array $ordens){
        $this->ordens = $ordens;
        $this->countOrdens = count( $ordens );
        return $this;
    }

    public function get(){
        $this->process();
        if( !is_null($this->ordenados) ){
            foreach( $this->ordenados as $grupo ){
                foreach( $grupo as $agrupado ){
                    $this->return[] = $agrupado;
                }
            }
        }elseif( !is_null($this->agrupados) ){
            foreach( $this->agrupados as $grupo ){
                foreach( $grupo as $agrupado ){
                    $this->return[] = $agrupado;
                }
            }
        }

        return $this->return;
    }

    public function dump($func){
        $this->process();
        if( !is_null($this->ordenados) ){
            foreach( $this->ordenados as $grupo ){
                foreach( $grupo as $agrupado ){
                    $this->return[] = $agrupado;
                }
            }
        }elseif( !is_null($this->agrupados) ){
            foreach( $this->agrupados as $grupo ){
                foreach( $grupo as $agrupado ){
                    $this->return[] = $agrupado;
                }
            }
        }

        return $this->$func;
    }

    private function process(){

        if( is_array( $this->agrupamentos ) && count( $this->agrupamentos ) ){
            $this->agrupando();
        }

        if( is_array( $this->ordens ) && count( $this->ordens ) ){
            $this->ordenando();
        }
    }

    private function agrupando(){
        $_array = [];
        $_array_plano = [];
        $_counter = 0;

        $counter = 0;
        
        foreach( $this->agrupamentos as $agrupamento => $valor){

            // É um array de formas de agrupamento
            if( is_array( $valor ) ){
                $counter = $agrupamento + 1;
                $chaves_agrupamento =@ array_keys( $valor );
                foreach( $this->input as $input ){
                    $chaves_array = array_keys( $input );
    
                    if( $this->arrayAlreadyExists( $chaves_array, $chaves_agrupamento, 1)->leftIsNotInsideCount > 0 ){
                        exit($this->error('agrupamento_param_nao_existe', $this->arrayAlreadyExists( $chaves_array, $chaves_agrupamento, 1)->leftIsNotInside));
                    }
    
    
                    $countIntersec = count(array_intersect_assoc( $input, $valor ));
                    $countRules    = count( $valor );
                    if( $countIntersec == $countRules && $this->arrayAlreadyExists( [$input], $_array_plano)->leftIsInsideCount <= 0 ){
                        $_array_plano[] = $input;
                        $_array[$agrupamento][] = $input;
                    }
                }
            }else{
                foreach( $this->input as $input ){
                    $agrupamentos[$agrupamento] = $valor;
                    $chaves_array = array_keys( $input );
                    $check = $this->arrayAlreadyExists( $chaves_array, [$agrupamento], 1);
                    if( $check->leftIsNotInsideCount > 0 ){
                        exit( $this->error('agrupamento_param_nao_existe', $check->leftIsNotInside) );
                    }

                    $countIntersec = count(array_intersect_assoc( $input, $agrupamentos ));
                    $countRules    = count( $agrupamentos );
                    if( $countIntersec == $countRules && $this->arrayAlreadyExists( [$input], $_array_plano)->leftIsInsideCount <= 0 ){
                        $_array_plano[] = $input;
                        $_array[ $counter ][] = $input;
                    }
                }
            }
        }

        $this->agrupados = $_array;
        $this->agrupados[] = $this->arrayAlreadyExists($this->input, $_array_plano)->rightIsNotInside; // INDEFINIDOS
        $_array_plano[] = $this->arrayAlreadyExists($this->input, $_array_plano)->rightIsNotInside;
    }

    private function ordenando(){
        $_array = [];
        if( !is_null($this->agrupados) ){
            $_array = $this->agrupados;
        }else{
            $_array[] = $this->input;
        }

        //print_r( $_array );die;

        $_parametros_conhecidos = ['asc', 'desc'];
        $_current_order = 'asc';

        foreach($this->ordens as $chave => $valor){
            foreach( $_array as $id_grupo => $grupo){
                
                if( array_key_exists( $chave, $this->ordens ) )
                {   
                    $_current_order = $valor;

                }else
                {
                    exit( $this->error('ordenamento_param_nao_existente', $chave) );
                }
    
                if( in_array($_current_order, $_parametros_conhecidos) )
                {
                    $this->ordenados[] = $this->ordemAscDesc( $grupo, $chave, $_current_order);
                }
    
            }
        }
    }

    private function ordemAscDesc(array $array, $item, $ordem = 'asc'){
        $tipo_ordenamento = $ordem == 'asc' ? SORT_ASC : SORT_DESC;

        if( $tipo_ordenamento == SORT_ASC ){
            usort($array, function ($a, $b) use ($item, $ordem){
                    if( !array_key_exists($item, $a) && !array_key_exists($item, $b) ){
                        throw new \Exception("Chave ({$item}) nao existe nos ordenamentos.", 1);
                    }
                    return $a[ $item ] - $b[ $item ];
            });
        }else{
            usort($array, function ($a, $b) use ($item, $ordem){
                if( !array_key_exists($item, $a) && !array_key_exists($item, $b) ){
                    throw new \Exception("Chave ({$item}) nao existe nos ordenamentos.", 1);
                }
                return $b[ $item ] - $a[ $item ];
            });
        }

        // print_r( $array ); 
        return $array;
    }

    public function arrayAlreadyExists(array $array_target, array $array_list, $result = null){
        $_result = [];
    
        $left = function() use (&$_result, &$array_target, &$array_list){
            $_counter_isNotInside = 0;
            $_counter_isInside = 0;

            $_result['leftIsNotInside'] = [];
            $_result['leftIsInside'] = [];
            $_result['leftIsNotInsideCount'] = 0;
            $_result['leftIsInsideCount'] = 0;

            foreach($array_list as $item){
                $_notFound = 1;
                foreach($array_target as $target){
                    $_isEqual = 0;
                    if( is_array($item) && is_array($target) ){
                        $_HowManyEqual = array_intersect_assoc($target, $item);
                        $_isEqual = count( array_values($_HowManyEqual) ) == count( array_keys($item) );
                    }else{
                        $_HowManyEqual = array_intersect_assoc([$target], [$item]);
                        $_isEqual = count( array_values($_HowManyEqual) ) == count( array_values([$item]) );
                    }
                    
                    if( $_isEqual ){
                        $_notFound = 0;
                    }
                }

                if( $_notFound == 1 ){
                    //$_counter_isNotInside++;
                    $_result['leftIsNotInside'][] = $item;
                    
                }else{
                    //$_counter_isInside++;
                    $_result['leftIsInside'][] = $item;
                }
            }
            $_result['leftIsNotInsideCount'] = count( $_result['leftIsNotInside'] );
            $_result['leftIsInsideCount'] = count( $_result['leftIsInside'] );
        };
        
        $right = function() use (&$_result, &$array_target, &$array_list){
            $_counter_isNotInside = 0;
            $_counter_isInside = 0;
            $_result['rightIsNotInside'] = [];
            $_result['rightIsInside'] = [];
            $_result['rightIsNotInsideCount'] = 0;
            $_result['rightIsInsideCount'] = 0;

            foreach($array_target as $target){
                $_notFound = 1;
                foreach($array_list as $item){
                    
                    if( is_array($item) && is_array($target) ){
                        $_HowManyEqual = array_intersect_assoc( $item, $target );
                        $_isEqual = count( array_keys($_HowManyEqual) ) == count( array_keys($item) );
                    }else{
                        $_HowManyEqual = array_intersect_assoc( [$item], [$target] );
                        $_isEqual = count( array_values($_HowManyEqual) ) == count( array_values([$item]) );
                    }
                    
                    if( $_isEqual ){
                        $_notFound = 0;
                    }	
                }
                
                if( $_notFound ){
                    // $_counter_isNotInside++;
                    $_result['rightIsNotInside'][] = $target;
                    
                }else{
                    // $_counter_isInside++;
                    $_result['rightIsInside'][] = $target;
                }
            }
            $_result['rightIsNotInsideCount'] = count( $_result['rightIsNotInside'] );
            $_result['rightIsInsideCount'] = count( $_result['rightIsInside'] );
        };
            
        if( $result == 1 ){
            
            $left(); 
                
        }elseif( $result == 2){
            $right();
        }else{
            $left();
            $right();
        }
        
        $_result = (object) $_result;
        
        return $_result;
    }


    private function error($errno, $params = null){
        $_params = null;
        if( !is_null($params) ){
            if( is_array($params) ){
                $_params = implode( ',', $params );
            }else{
                $_params = $params;
            }
        }
        switch( $errno ){
            case 'agrupamento_param_nao_existe':
                throw new \Exception("Parametro(s) " . ($_params ? "({$_params})" : '' ). " de agrupamento nao existente ou informado de forma incorreta", 1);
                break;
            case 'ordenamento_param_nao_existente':
                throw new \Exception("Parametro(s) " . ($_params ? "({$_params})" : '') . " de ordenamento nao existente", 1);
                break;

            default:
                return json_encode([
                    'algo_errado'
                ]);
        }
    }
}
