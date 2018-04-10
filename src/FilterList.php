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

    public function agrupamentos(array $agrupamentos){
        $this->agrupamentos = $agrupamentos;
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
        foreach( $this->agrupamentos as $agrupamento => $valor){
            $_counter++;
            // É um array de formas de agrupamento
            if( is_array( $valor ) )
            {
                $chaves_agrupamento =@ array_keys( $valor );
                foreach( $this->input as $input ){
                    $chaves_array = array_keys( $input );
                    if( $this->arrayAlreadyExists( $chaves_array, $chaves_agrupamento)->leftIsNotInsideCount > 0 ){
                        die($this->error('agrupamento_param_nao_existe'));
                    }


                    $countIntersec = count(array_intersect_assoc( $input, $valor ));
                    $countRules    = count( $valor );
                    if( $countIntersec == $countRules && $this->arrayAlreadyExists( [$input], $_array_plano)->leftIsInsideCount <= 0 ){
                        $_array_plano[] = $input;
                        $_array[$agrupamento][] = $input;
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
                    $this->error('ordenamento_param_nao_existente');
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
                    return $a[ $item ] - $b[ $item ];
            });
        }else{
            usort($array, function ($a, $b) use ($item, $ordem){
                return $b[ $item ] - $a[ $item ];
            });
        }

        // print_r( $array ); 
        return $array;
    }

    public function arrayAlreadyExists(array $array_target, array $array_list, $result = null){
        $_result = [];
        $_counter_isNotInside = 0;
        $_counter_isInside = 0;
    
        $left = function() use (&$_result, &$_counter_isNotInside, &$_counter_isInside, &$array_target, &$array_list){
            foreach($array_list as $item){
                $_notFound = 1;
                foreach($array_target as $target){
                    
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
                
                if( $_notFound ){
                    $_counter_isNotInside++;
                    $_result['leftIsNotInside'][] = $item;
                    
                }else{
                    $_counter_isInside++;
                    $_result['leftIsInside'][] = $item;
                }
            }
            $_result['leftIsNotInsideCount'] = $_counter_isNotInside;
            $_result['leftIsInsideCount'] = $_counter_isInside;
        };
        
        $right = function() use (&$_result, &$_counter_isNotInside, &$_counter_isInside, &$array_target, &$array_list){
            foreach($array_target as $target){
                $_notFound = 1;
                foreach($array_list as $item){
                    
                    if( is_array($item) && is_array($target) ){
                        $_HowManyEqual = array_intersect_assoc($item, $target);
                        $_isEqual = count( array_keys($_HowManyEqual) ) == count( array_keys($item) );
                    }else{
                        $_HowManyEqual = array_intersect_assoc([$item], [$target]);
                        $_isEqual = count( array_values($_HowManyEqual) ) == count( array_values([$item]) );
                    }
                    
                    if( $_isEqual ){
                        $_notFound = 0;
                    }	
                }
                
                if( $_notFound ){
                    $_counter_isNotInside++;
                    $_result['rightIsNotInside'][] = $target;
                    
                }else{
                    $_counter_isInside++;
                    $_result['rightIsInside'][] = $target;
                }
            }
            $_result['rightIsNotInsideCount'] = $_counter_isNotInside;
            $_result['rightIsInsideCount'] = $_counter_isInside;
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


    private function error($errno){
        switch( $errno ){
            case 'agrupamento_param_nao_existe':
                return json_encode([
                    'status' => 0,
                    'error'  => 'Parametro de agrupamento nao existente'
                ]);
                break;
            
            case 'ordenamento_param_nao_existente':
                return json_encode([
                    'status' => 0,
                    'error'  => 'Parametro de ordenamento nao existente'
                ]);
                break;

            default:
                return json_encode([
                    'algo_errado'
                ]);
        }
    }
}
