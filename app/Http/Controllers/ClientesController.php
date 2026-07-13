<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Crediario;
use App\Models\Cupom;
use App\Models\Devedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClientesController extends Controller
{
    /**
     * Retorna os serviços de um cliente com todos os detalhes relacionados.
     * Query complexa com múltiplos JOINs e subquery JSON - usa DB::select para compatibilidade.
     *
     * @param  int  $ID  ID do Cliente
     * @return array
     */
    public static function getServicosCliente($ID)
    {
        $SQL = <<<SQL
            SELECT 
                e.NMRazaoEmpresa as empresa,
                f.DSEnderecoJSON as endereco,
                c.NMCliente as cliente,
                f.NMFilial as filial,
                s.DSTipoServico as servico,
                os.DSOrdemServico as previa,
                os.DSServico as descricao,
                col.NMColaborador as atendente,
                os.IDOrdem as codigo,
                os.DTSaida saida,
                os.DSNota mensagem,
                os.DTServico as dataHora,
                pag.QTParcelas as parcelas,
                pag.NUJuros as juros,
                pag.DSMetodo as metodo,
                (SELECT
                    CONCAT('[',
                        GROUP_CONCAT(
                        '{'
                        ,'"produto":"',prod.NMProduto,'"'
                        ,',"valor":"',prod.NUValorProduto,'"'
                        ,',"quantidade":"',custos.NUQuantidade,'"'
                        ,',"id":"',prod.IDProduto,'"'
                        ,'}' 
                    SEPARATOR ','),
                ']')
                FROM custosordem custos 
                INNER JOIN produtos prod USING(IDProduto) 
                LEFT JOIN promocionais k USING(IDProduto) 
                LEFT JOIN promocoes y USING(IDPromocao) 
                WHERE custos.IDOrdem = os.IDOrdem ) as maodeobra,
                e.NUCnpjEmpresa as cnpj,
                s.VLBase as mobra,
                pag.IDPagamento as id_pagamento
            FROM empresas e
            LEFT JOIN filiais f USING(IDEmpresa)
            LEFT JOIN clientes c USING(IDFilial)
            LEFT JOIN ordemservico os USING(IDCliente)
            LEFT JOIN colaboradores as col USING(IDColaborador)
            LEFT JOIN servicos s USING(IDServico)
            LEFT JOIN custosordem cst USING(IDOrdem)
            LEFT JOIN produtos prv USING(IDProduto)
            LEFT JOIN pagamentos pag USING(IDPagamento)
            WHERE os.IDCliente = ? AND os.STServico = 1
        SQL;

        return DB::select($SQL, [$ID]);
    }

    /**
     * Sincroniza/Cria um cliente baseado no CPF.
     * Se não existir, insere um novo registro.
     *
     * @param  array  $dados  Dados do cliente (NMCliente, NMEmailCliente, NUTelefoneCliente, NUCpfCliente, IDFilial)
     * @return void
     */
    public static function sincronizaCliente($dados)
    {
        Cliente::firstOrCreate(
            ['NUCpfCliente' => $dados['NUCpfCliente']],
            [
                'NMCliente'         => $dados['NMCliente'],
                'NMEmailCliente'    => $dados['NMEmailCliente'],
                'NUTelefoneCliente' => $dados['NUTelefoneCliente'],
                'IDFilial'          => $dados['IDFilial'],
            ]
        );
    }

    /**
     * Retorna as compras (cupons) de um cliente.
     *
     * @param  int  $IDCliente
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getCompras($IDCliente)
    {
        return Cupom::with('cliente')
            ->where('IDCliente', $IDCliente)
            ->get(['ANCupom', 'IDCliente']);
    }

    /**
     * Retorna os dados de pendências (dívidas) de um cliente.
     *
     * @param  int  $IDCliente
     * @return array  Retorna array com 'temDivida' e 'valorDivida' formatado
     */
    public static function getPendencias($IDCliente)
    {
        $divida = Devedor::where('IDCliente', $IDCliente)->first();

        if ($divida) {
            return [
                'temDivida'   => true,
                'valorDivida' => $divida->VLDivida,
            ];
        }

        return [
            'temDivida'   => false,
            'valorDivida' => 0,
        ];
    }

    /**
     * Retorna lista de clientes de uma filial, incluindo informação de dívida.
     *
     * @param  int  $IDFilial
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function listarClientes($IDFilial)
    {
        return Cliente::with('devedor')
            ->where('IDFilial', $IDFilial)
            ->get()
            ->map(function ($cliente) {
                $cliente->divida = $cliente->devedor ? $cliente->devedor->VLDivida : 0;
                return $cliente;
            });
    }

    /**
     * Retorna lista de clientes devedores de uma filial.
     *
     * @param  int  $IDFilial
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function listarDevedores($IDFilial)
    {
        return Devedor::with('cliente')
            ->whereHas('cliente', function ($query) use ($IDFilial) {
                $query->where('IDFilial', $IDFilial);
            })
            ->get();
    }

    /**
     * Retorna clientes que NÃO são devedores e NÃO possuem crediário
     * (disponíveis para se tornarem devedores).
     *
     * @param  int  $IDFilial
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getSelectDevedores($IDFilial)
    {
        $idsComCrediario = Crediario::whereHas('cliente', function ($query) use ($IDFilial) {
            $query->where('IDFilial', $IDFilial);
        })->pluck('IDCliente');

        $idsComDivida = Devedor::whereHas('cliente', function ($query) use ($IDFilial) {
            $query->where('IDFilial', $IDFilial);
        })->pluck('IDCliente');

        return Cliente::where('IDFilial', $IDFilial)
            ->whereNotIn('IDCliente', $idsComCrediario)
            ->whereNotIn('IDCliente', $idsComDivida)
            ->select('IDCliente', 'NMCliente')
            ->get();
    }

    /**
     * Retorna clientes que NÃO são devedores e NÃO possuem crediário
     * (disponíveis para receber crediário).
     *
     * @param  int  $IDFilial
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getSelectCrediarios($IDFilial)
    {
        $idsComCrediario = Crediario::whereHas('cliente', function ($query) use ($IDFilial) {
            $query->where('IDFilial', $IDFilial);
        })->pluck('IDCliente');

        $idsComDivida = Devedor::whereHas('cliente', function ($query) use ($IDFilial) {
            $query->where('IDFilial', $IDFilial);
        })->pluck('IDCliente');

        return Cliente::where('IDFilial', $IDFilial)
            ->whereNotIn('IDCliente', $idsComDivida)
            ->whereNotIn('IDCliente', $idsComCrediario)
            ->select('IDCliente', 'NMCliente')
            ->get();
    }

    /**
     * Retorna lista de clientes com crediário de uma filial.
     *
     * @param  int  $IDFilial
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function listarCrediarios($IDFilial)
    {
        return Crediario::with('cliente')
            ->whereHas('cliente', function ($query) use ($IDFilial) {
                $query->where('IDFilial', $IDFilial);
            })
            ->get();
    }

    /**
     * Retorna um cliente específico pelo ID.
     *
     * @param  int  $IDCliente
     * @return \App\Models\Cliente|null
     */
    public function listarCliente($IDCliente)
    {
        return Cliente::find($IDCliente);
    }

    /**
     * Retorna um devedor específico com os dados do cliente.
     *
     * @param  int  $IDDevedor
     * @return \App\Models\Devedor|null
     */
    public function listarDevedor($IDDevedor)
    {
        return Devedor::with('cliente')->find($IDDevedor);
    }

    /**
     * Retorna um crediário específico com os dados do cliente.
     *
     * @param  int  $IDCrediario
     * @return \App\Models\Crediario|null
     */
    public function listarCrediario($IDCrediario)
    {
        return Crediario::with('cliente')->find($IDCrediario);
    }

    /**
     * Exclui um cliente (com verificação de dívidas e créditos).
     *
     * @param  int  $IDCliente
     * @param  int  $confirma  0 = verificar pendências, 1 = forçar exclusão
     * @return string  JSON encoded
     */
    public function excluirCliente($IDCliente, $confirma)
    {
        $temDevedor  = Devedor::where('IDCliente', $IDCliente)->exists();
        $temCrediario = Crediario::where('IDCliente', $IDCliente)->exists();

        if ($confirma == 0) {
            if ($temDevedor) {
                $retorno['podeExcluir'] = false;
                $retorno['msg'] = "Você não pode excluir esse cliente pois ele tem uma dívida";
            } elseif ($temCrediario) {
                $retorno['podeExcluir'] = false;
                $retorno['msg'] = "Você não pode excluir esse cliente pois ele tem créditos";
            } else {
                $retorno['podeExcluir'] = true;
            }
        } else {
            $retorno['podeExcluir'] = true;
            Cliente::destroy($IDCliente);
        }

        return json_encode($retorno);
    }

    /**
     * Exclui um crediário específico.
     *
     * @param  int  $IDCrediario
     * @return bool|null
     */
    public function excluirCrediario($IDCrediario)
    {
        return Crediario::destroy($IDCrediario);
    }

    /**
     * Exclui um devedor específico.
     *
     * @param  int  $IDDevedor
     * @return bool|null
     */
    public function excluirDevedor($IDDevedor)
    {
        return Devedor::destroy($IDDevedor);
    }

    /**
     * Salva ou atualiza um cliente.
     *
     * @param  array  $dados  Dados do cliente (IDCliente, nomeCliente, emailCliente, telefoneCliente, cpfCliente, filial)
     * @return \App\Models\Cliente
     */
    public function salvarCliente($dados)
    {
        // Determina a filial: sessão ou parâmetro
        $filial = $_SESSION['login']['filial'] ?? $dados['filial'];

        if (!empty($dados['IDCliente'])) {
            // Atualização
            $cliente = Cliente::find($dados['IDCliente']);
            if ($cliente) {
                $cliente->update([
                    'NMCliente'         => $dados['nomeCliente'],
                    'NMEmailCliente'    => $dados['emailCliente'],
                    'NUTelefoneCliente' => $dados['telefoneCliente'],
                ]);
            }
        } else {
            // Criação
            $cliente = Cliente::create([
                'NMCliente'         => $dados['nomeCliente'],
                'NMEmailCliente'    => $dados['emailCliente'],
                'NUTelefoneCliente' => $dados['telefoneCliente'],
                'NUCpfCliente'      => $dados['cpfCliente'],
                'IDFilial'          => $filial,
            ]);
        }

        return $cliente;
    }

    /**
     * Salva ou atualiza um crediário.
     *
     * @param  array  $dados  Dados do crediário (IDCrediario, nomeCrediario, creditoCrediario, creditoAte)
     * @return \App\Models\Crediario
     */
    public function salvarCrediario($dados)
    {
        if (!empty($dados['IDCrediario'])) {
            // Atualização
            $crediario = Crediario::find($dados['IDCrediario']);
            if ($crediario) {
                $crediario->update([
                    'NUCredito'        => $dados['creditoCrediario'],
                    'DTTerminoCredito' => $dados['creditoAte'],
                ]);
            }
        } else {
            // Criação
            $crediario = Crediario::create([
                'IDCliente'        => $dados['nomeCrediario'],
                'NUCredito'        => $dados['creditoCrediario'],
                'DTTerminoCredito' => $dados['creditoAte'],
            ]);
        }

        return $crediario;
    }

    /**
     * Salva ou atualiza um devedor.
     *
     * @param  array  $dados  Dados do devedor (IDDevedor, nomeDevedor, valorDivida)
     * @return \App\Models\Devedor
     */
    public function salvarDevedor($dados)
    {
        if (!empty($dados['IDDevedor'])) {
            // Atualização
            $devedor = Devedor::find($dados['IDDevedor']);
            if ($devedor) {
                $devedor->update([
                    'VLDivida' => $dados['valorDivida'],
                ]);
            }
        } else {
            // Criação
            $devedor = Devedor::create([
                'IDCliente' => $dados['nomeDevedor'],
                'VLDivida'  => $dados['valorDivida'],
            ]);
        }

        return $devedor;
    }
}