<?php declare(strict_types=1);

require 'cdc.php';

function getLeftBoxHTMLText(float $precoAVista,float $precoAPrazo,int $numParcelas,float $taxaDeJuros,bool $temEntrada,int $mesesAVoltar):string{
    $precoAprazoTemp = numberToFixed((float) $precoAPrazo,2);
    $precoAVistaTemp = numberToFixed((float) $precoAVista,2);
    $numParcelasTemp = (int) $numParcelas;
    $mesesAVoltarTemp = (int) $mesesAVoltar;
    $taxaDeJurosTemp = numberToFixed((float) $taxaDeJuros,4);
    $taxaDeJurosAnual = converterJurosMensalParaAnual($taxaDeJuros);

    $coeficienteFinanciamento = calcularCoeficienteFinanciamento($taxaDeJuros,$numParcelas);

    $pmt = numberToFixed(calcularPMT($precoAVista,$coeficienteFinanciamento), 2);
    $valorAVoltar = numberToFixed(calcularValorAVoltar($pmt,$numParcelasTemp,$mesesAVoltarTemp), 2);

    $textoParcelamento = $temEntrada ? " (+ 1)": "";
    $textoTemEntrada = $temEntrada ? "Sim" : "Não";

    $taxaDeJurosTemp *= 100;
    return "<p><b>Parcelamento:</b> {$numParcelas} {$textoParcelamento} </p>
    <p><b>Taxa:</b> {$taxaDeJurosTemp}% Ao Mês ({$taxaDeJurosAnual}% Ao Ano) </p>
    <p><b>Valor Financiado:</b> $ {$precoAVistaTemp} </p>
    <p><b>Valor Final:</b> $ {$precoAprazoTemp}</p>
    <p><b>Meses a Voltar(Adiantados):</b> {$mesesAVoltar} </p>
    <p><b>Valor a voltar(Adiantamento da dívida):</b> $ {$valorAVoltar} </p>
    <p><b>Entrada:</b> {$textoTemEntrada} </p> ";
}

function getRightBoxHTMLText(float $precoAVista,float $precoAPrazo,int $numParcelas,float $taxaDeJuros,bool $temEntrada,float $valorCorrigido):string{
    $jurosReal = 0;

    $precoAprazoTemp = numberToFixed((float) $precoAPrazo,2);
    $precoAVistaTemp = numberToFixed((float) $precoAVista,2);
    $numParcelasTemp = (int) $numParcelas;
    $jurosReal = calcularTaxaDeJuros($precoAVista,$precoAPrazo, $numParcelas,$temEntrada) * 100;

    $coeficienteFinanciamento = calcularCoeficienteFinanciamento($taxaDeJuros,$numParcelas);
    $jurosReal = numberToFixed($jurosReal,4);
    $pmt = toFixed(calcularPMT($precoAVista,$coeficienteFinanciamento),2);
 
    $jurosEmbutido = (($precoAPrazo - $precoAVista) / $precoAVista) * 100;
    $jurosEmbutido = numberToFixed($jurosEmbutido,2);
    $desconto = (($precoAPrazo - $precoAVista) / $precoAPrazo) * 100;
    $desconto = numberToFixed($desconto,2);
    $fatorAplicado = toFixed(calcularFatorAplicado($temEntrada,$numParcelas,$coeficienteFinanciamento,$taxaDeJuros),6);
    $coeficienteFinanciamento = numberToFixed($coeficienteFinanciamento,6);
    return "
    <p><b>Prestação:</b> $ {$pmt}</p>
    <p> <b>Taxa Real:</b>  {$jurosReal}%</p>
    <p> <b>Coeficiente de Financiamento:</b> {$coeficienteFinanciamento} </p>
    <p><b>Fator Aplicado:</b> {$fatorAplicado}</p>
    <p> <b>Valor Corrigido:</b> $ {$valorCorrigido} </p>
    <p> <b>Juros Embutido:</b> {$jurosEmbutido}% </p>
    <p> <b>Desconto:</b>  {$desconto}% </p>
    ";
}


function getTabelaPriceHTMLText(array $tabelaPrice):string{
    $table = "";
    for($i = 0; $i < count($tabelaPrice); $i++){

       if($i == 0){
           $table .= "<thead><tr>";

            foreach($tabelaPrice[$i] as $itemTabela){
                $table .= "<th> {$itemTabela} </th>"; 
            }

         
            $table .= "</tr></thead>";
       }
       else{
           $table .= "<tr>";
           foreach($tabelaPrice[$i] as $itemTabelaa){
            if($i == count($tabelaPrice) - 1){
               $table .= "<td> <b>  $itemTabelaa   </b> </td>";
            }
            else{
                $table .= "<td>  $itemTabelaa </td>";
            }

           }
           $table .= "</tr>";
       }
       
   }
   return $table;
}

function printPage(string $leftBoxContent,string $rightBoxContent,string $tabelaPriceContent):void{
    $finalText = <<<HTML
    
    <!DOCTYPE html>
    <html lang="pt-br">
    <head>
        <title>Credito Direto ao Consumidor - Tabela Price</title>
            <meta charset="utf8" />
            <meta name="viewport" content="width=device-width, initial-scale=1" />
            <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
            <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
            <link
                rel="stylesheet"
                href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css"
            />
            <script src="js-webshim/minified/polyfiller.js"></script>
            
            
            <style>
                @import url('https://fonts.googleapis.com/css2?family=Roboto+Condensed&display=swap');
                    body{background-color: #f1ebbd;
                        font-family: 'Roboto Condensed', sans-serif;}
                
                    #table-content table, th, td{
                        border: 1px solid black;
                        font-size: 20px;
                        padding: 5px;
                        text-align: center;
                    }

                    #left-box, #right-box{
                        width: 100%;
                        border-style: dotted;
                        padding: 20px;
                        margin-bottom: 30px;
                        border-radius: 10px;
                    }

                    #summary-container{
                        display: block;
                        width: 30%;
                        flex-wrap: wrap;
                        justify-content: space-around;
                        margin-top: 20px;
                    }

                    #table-container{
                        margin-top: 18px;
                        display: flex;
                        flex-wrap: wrap;
                        flex-direction: column;
                        align-items: center;
                        justify-content: center;
                        width: 50%
                    }
                    #result-container{   
                        display: flex;
                    }
                    h1{
                        margin: 0px;
                    }
                    #btn_nova_consulta {
                        width: 80px;
                        height: 50px;
                        border: solid, black, 1px; 
                    }
                    
                    
            </style>
    </head>
    <body>
        <div id="result-container">
                    
                <div id="summary-container">

                    <div id="left-box">
                        {$leftBoxContent}
                    </div>

                    <div id="right-box">
                        {$rightBoxContent}
                    </div>
                    
                </div>

                <div id="table-container">
                
                    <h1>TABELA PRICE</h1>
                    
                    <table id="table-content">
                        {$tabelaPriceContent}
                    </table>
                </div>


             <button id="btn_nova_consulta" link="https://cdc-cyan.vercel.app/">Nova Consulta</button>   
            </div>
        
        </body>
        </html>
    HTML;


    echo $finalText;

}


$numeroParcelas = (int) $_POST["np"];
$juros = (float) $_POST["tax"];
$valorFinanciado = (float) $_POST["pv"];
$valorFinal = (float) $_POST["pp"];
$mesesAVoltar = (int) $_POST["pb"];
$temEntrada = (bool) $_POST["dp"];


$tabelaPrice;
$valorCorrigido;
$coeficienteFinanciamento;
$pmt;


if($juros != 0 && $valorFinal == 0){
    $juros /= 100;

}else{
    $juros = calcularTaxaDeJuros($valorFinanciado,$valorFinal,$numeroParcelas,$temEntrada);
}


$coeficienteFinanciamento = calcularCoeficienteFinanciamento($juros, $numeroParcelas);

if( $valorFinal == 0){
    $valorFinal = calcularValorFuturo($coeficienteFinanciamento,$juros,$valorFinanciado,$numeroParcelas,$temEntrada);
} 
$pmt = calcularPMT($valorFinanciado,$coeficienteFinanciamento);

if($temEntrada){
    $pmt /= 1 + $juros;
    $numeroParcelas--;
    $valorFinanciado -= $pmt;

    
}

$tabelaPrice = getTabelaPrice($valorFinanciado,$pmt,$numeroParcelas,$juros,$temEntrada);

$valorCorrigido = getValorCorrigido($tabelaPrice,$numeroParcelas,$mesesAVoltar);

$tabelaPriceText =  getTabelaPriceHTMLText($tabelaPrice);

$leftBoxText = getLeftBoxHTMLText($valorFinanciado,$valorFinal,$numeroParcelas,$juros,$temEntrada, $mesesAVoltar);
$rightBoxText = getRightBoxHTMLText($valorFinanciado,$valorFinal,$numeroParcelas,$juros,$temEntrada, $valorCorrigido);

printPage($leftBoxText,$rightBoxText,$tabelaPriceText);
?>