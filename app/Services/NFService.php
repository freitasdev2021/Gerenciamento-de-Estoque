<?php

namespace App\Services;

use NFePHP\NFe\Make;
use NFePHP\NFe\Tools;
use NFePHP\Common\Certificate;
use NFePHP\NFe\Common\Standardize;

class NFService{
    public function gerarNfeParaVenda($venda)
    {
        try {
            $nfe = new Make();

            // 1. Tag de Informações básicas
            $std = new \stdClass();
            $std->versao = '4.00'; 
            $nfe->taginfNFe($std);

            // 2. Tag de Identificação da Nota
            $std = new \stdClass();
            $std->cUF = 31; // Código do estado (ex: 35 = SP)
            $std->cNF = str_pad($venda->id, 8, '0', STR_PAD_LEFT); // Número aleatório da nota
            $std->natOp = 'VENDA DE MERCADORIA';
            $std->mod = 55; // 55 = NF-e (A4) ou 65 = NFC-e (Cupom)
            $std->serie = 1;
            $std->nNF = $venda->id; // Usando o ID da venda como número provisório
            $std->dhEmi = date('Y-m-d\TH:i:sP');
            $std->tpNF = 1; // Saída
            $std->idDest = 1; // Destino interno (dentro do estado)
            $std->cMunFG = 3131307; // Código IBGE do município da sua filial
            $std->tpImp = 1; // Retrato
            $std->tpEmis = 1; // Normal
            $std->tpAmb = 2; // 2 = HOMOLOGAÇÃO (Sem valor fiscal para testes)
            $std->finNFe = 1;
            $std->indFinal = 1; // Consumidor final
            $std->indPres = 1; // Presencial
            $nfe->tagide($std);

            // 3. Emitente (Sua empresa/Filial)
            // Aqui você busca os dados de $venda->IDFilial no banco
            $std = new \stdClass();
            $std->xNome = 'Sua Razao Social LTDA';
            $std->CNPJ = '00000000000000'; // Sem pontos ou traços
            $std->IE = '000000000000';
            $std->CRT = 1; // 1 = Simples Nacional
            // Endereço do emitente...
            $nfe->tagemit($std);

            // 4. Destinatário (Cliente)
            // Aqui você busca os dados de $venda->IDCliente
            $std = new \stdClass();
            $std->xNome = 'NF-E EMITIDA EM AMBIENTE DE HOMOLOGACAO - SEM VALOR FISCAL'; // Obrigatório para testes
            $std->CPF = '00000000000'; 
            $std->indIEDest = 9; // Não contribuinte
            $nfe->tagdest($std);

            // 5. Itens da Venda (Loop pelos produtos)
            // No seu caso atual, você tem um produto direto na venda: $venda->IDProduto
            $std = new \stdClass();
            $std->item = 1;
            $std->cProd = $venda->IDProduto;
            $std->xProd = 'Nome do Produto de Teste';
            $std->NCM = '84713012'; // Precisa ser um NCM válido de 8 dígitos
            $std->CFOP = '5102'; // Venda dentro do estado
            $std->uCom = 'UN';
            $std->qCom = $venda->NUUnidadesVendidas;
            $std->vUnCom = 10.00; // Valor unitário
            $std->vProd = $venda->NUUnidadesVendidas * 10.00; // Valor total do item
            $std->indTot = 1;
            $nfe->tagprod($std);

            // Seus impostos (ICMS, PIS, COFINS) vão aqui...
            
            // 6. Fechamento e Totais da nota
            $std = new \stdClass();
            $std->vBC = 0.00;
            $std->vICMS = 0.00;
            $std->vProd = $venda->NUUnidadesVendidas * 10.00;
            $std->vNF = $venda->NUUnidadesVendidas * 10.00;
            $nfe->tagICMSTot($std);

            // Gera o XML
            $xmlGerado = $nfe->getXML();

            // Retorna indicando sucesso na montagem do XML (próxima etapa: assinar e transmitir)
            return [
                'sucesso' => true,
                'mensagem' => 'XML Gerado com sucesso!',
                'xml' => $xmlGerado
            ];

        } catch (\Exception $e) {
            return [
                'sucesso' => false,
                'mensagem' => 'Erro ao gerar XML: ' . $e->getMessage()
            ];
        }
    }
}