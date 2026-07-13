<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Colaborador;
use App\Models\Conta;
use App\Models\Contrato;
use App\Models\Crediario;
use App\Models\Cupom;
use App\Models\Devedor;
use App\Models\Empresa;
use App\Models\Filial;
use App\Models\Fornecedor;
use App\Models\OrdemServico;
use App\Models\Pagamento;
use App\Models\Plano;
use App\Models\Produto;
use App\Models\Promocao;
use App\Models\Promocional;
use App\Models\Servico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContratosController extends Controller
{
    /**
     * Retorna todos os planos disponíveis.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getPlanos()
    {
        return Plano::all();
    }

    /**
     * Retorna o ID do colaborador vinculado a um usuário.
     * (Tabela 'usuarios' do legado, sem model - usa DB::select)
     *
     * @param  int  $IDUsuario
     * @return int
     */
    public static function getColaboradorByUser($IDUsuario)
    {
        $result = DB::select(
            "SELECT IDColaborador FROM colaboradores 
             INNER JOIN users USING(IDColaborador) 
             WHERE users.id = ?",
            [$IDUsuario]
        );

        return !empty($result) ? $result[0]->IDColaborador : 0;
    }

    /**
     * Retorna lista de contratos, com restrição de nível para usuários comuns.
     *
     * @return array|\Illuminate\Database\Eloquent\Collection
     */
    public static function getContratos()
    {
        $nivel   = $_SESSION['login']['nivel'];
        $userId  = $_SESSION['login']['dados']['id'];

        // Níveis 2.5 e 2 veem apenas contratos que criaram
        if (in_array($nivel, ['2.5', '2'])) {
            return Contrato::with('plano')
                ->where('IDCriador', $userId)
                ->get();
        }

        // Admin vê todos
        return Contrato::with('plano')->get();
    }

    /**
     * Salva ou atualiza um colaborador.
     *
     * @param  array  $dados  Dados do colaborador
     * @return \App\Models\Colaborador
     */
    public static function setColaborador($dados)
    {
        if (!empty($dados['IDColaborador'])) {
            $colaborador = Colaborador::find($dados['IDColaborador']);
            if ($colaborador) {
                $colaborador->update([
                    'NMColaborador'      => $dados['nome'],
                    'NMCargoColaborador' => $dados['cargo'],
                    'NMEmailColaborador' => $dados['email'],
                    'VLSalario'          => $dados['salario'],
                ]);
            }
        } else {
            $colaborador = Colaborador::create([
                'NMColaborador'       => $dados['nome'],
                'NMCargoColaborador'  => $dados['cargo'],
                'NUCpfColaborador'    => $dados['cpf'],
                'VLSalario'           => $dados['salario'],
                'DTAdmissao'          => $dados['admissao'],
                'IDFilial'            => $dados['filial'],
                'NMEmailColaborador'  => $dados['email'],
            ]);
        }

        return $colaborador;
    }

    /**
     * Retorna filiais de uma empresa com a soma da folha salarial.
     * Query complexa com SUM e JOIN - usa DB::select.
     *
     * @param  int   $IDEmpresa
     * @return array
     */
    public static function getFiliais($IDEmpresa)
    {
        return DB::select(
            "SELECT f.*, SUM(c.VLSalario) as folhaSalarial 
             FROM filiais as f 
             LEFT JOIN colaboradores c USING(IDFilial) 
             WHERE f.IDEmpresa = ? 
             GROUP BY f.IDFilial 
             ORDER BY f.IDFilial",
            [$IDEmpresa]
        );
    }

    /**
     * Retorna uma filial específica com a soma da folha salarial.
     *
     * @param  int        $IDFilial
     * @return object|null
     */
    public static function getFilial($IDFilial)
    {
        $result = DB::select(
            "SELECT f.*, SUM(c.VLSalario) as folhaSalarial 
             FROM filiais as f 
             LEFT JOIN colaboradores c USING(IDFilial) 
             WHERE IDFilial = ?",
            [$IDFilial]
        );

        return !empty($result) ? $result[0] : null;
    }

    /**
     * Retorna lista de colaboradores de uma empresa com dados da filial e último acesso.
     * Query complexa com múltiplos JOINs - usa DB::select.
     *
     * @param  int   $IDEmpresa
     * @return array
     */
    public static function getColaboradores($IDEmpresa)
    {
        return DB::select(
            "SELECT
                c.*,
                f.NMFilial,
                f.IDFilial,
                u.DTUltimoAcesso
             FROM colaboradores c
             LEFT JOIN filiais f USING(IDFilial)
             LEFT JOIN usuarios u USING(IDColaborador)
             WHERE f.IDEmpresa = ?
             ORDER BY c.IDColaborador",
            [$IDEmpresa]
        );
    }

    /**
     * Retorna um colaborador específico pelo ID.
     *
     * @param  int  $IDColaborador
     * @return \App\Models\Colaborador|null
     */
    public static function getColaborador($IDColaborador)
    {
        return Colaborador::find($IDColaborador);
    }

    /**
     * Retorna todos os contratos.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getAllContratos()
    {
        return Contrato::all();
    }

    /**
     * Retorna um contrato específico pelo ID.
     *
     * @param  int  $ID
     * @return \App\Models\Contrato|null
     */
    public static function getContrato($ID)
    {
        return Contrato::find($ID);
    }

    /**
     * Exclui um colaborador e seu usuário vinculado.
     *
     * @param  array  $dados  (IDColaborador)
     * @return bool
     */
    public function delColaborador($dados)
    {
        $id = $dados['IDColaborador'];

        // Remove o usuário vinculado (tabela legada 'usuarios')
        DB::delete("DELETE FROM usuarios WHERE IDColaborador = ?", [$id]);

        // Remove o colaborador
        return Colaborador::destroy($id);
    }

    /**
     * Exclui um contrato e TODOS os dados relacionados em cascata.
     * Operação crítica multi-tabela - usa transação com queries raw.
     *
     * @param  array  $dados  (IDContrato)
     * @return bool
     */
    public function delContrato($dados)
    {
        $IDContrato = $dados['IDContrato'];

        // Busca os IDs das filiais vinculadas ao contrato
        $filiais = DB::select(
            "SELECT IDFilial FROM filiais 
             INNER JOIN empresas USING(IDEmpresa) 
             INNER JOIN contratos USING(IDContrato) 
             WHERE IDContrato = ?",
            [$IDContrato]
        );

        $filiaisIds = array_column($filiais, 'IDFilial');

        if (empty($filiaisIds)) {
            // Sem filiais, apenas remove o contrato
            return Contrato::destroy($IDContrato);
        }

        $placeholders = implode(',', array_fill(0, count($filiaisIds), '?'));

        DB::beginTransaction();
        try {
            // Remove registros dependentes na ordem correta
            DB::delete("DELETE FROM promocionais WHERE IDPromocao IN (SELECT IDPromocao FROM promocoes WHERE IDFilial IN ($placeholders))", $filiaisIds);
            DB::delete("DELETE FROM promocoes WHERE IDFilial IN ($placeholders)", $filiaisIds);
            DB::delete("DELETE FROM pagamentos WHERE IDFilial IN ($placeholders)", $filiaisIds);
            DB::delete("DELETE FROM ordemservico WHERE IDServico IN (SELECT IDServico FROM servicos WHERE IDFilial IN ($placeholders))", $filiaisIds);
            DB::delete("DELETE FROM servicos WHERE IDFilial IN ($placeholders)", $filiaisIds);
            DB::delete("DELETE FROM produtos WHERE IDFornecedor IN (SELECT IDFornecedor FROM fornecedores WHERE IDFilial IN ($placeholders))", $filiaisIds);
            DB::delete("DELETE FROM fornecedores WHERE IDFilial IN ($placeholders)", $filiaisIds);
            DB::delete("DELETE FROM cupons WHERE IDFilial IN ($placeholders)", $filiaisIds);
            DB::delete("DELETE FROM contas WHERE IDFilial IN ($placeholders)", $filiaisIds);
            DB::delete("DELETE FROM crediarios WHERE IDCliente IN (SELECT IDCliente FROM clientes WHERE IDFilial IN ($placeholders))", $filiaisIds);
            DB::delete("DELETE FROM devedores WHERE IDCliente IN (SELECT IDCliente FROM clientes WHERE IDFilial IN ($placeholders))", $filiaisIds);
            DB::delete("DELETE FROM clientes WHERE IDFilial IN ($placeholders)", $filiaisIds);
            DB::delete("DELETE FROM caixa WHERE IDFilial IN ($placeholders)", $filiaisIds);
            DB::delete("DELETE FROM colaboradores WHERE IDFilial IN ($placeholders)", $filiaisIds);
            DB::delete("DELETE FROM usuarios WHERE IDColaborador NOT IN (SELECT IDColaborador FROM colaboradores)", []);
            DB::delete("DELETE FROM filiais WHERE IDFilial IN ($placeholders)", $filiaisIds);
            DB::delete("DELETE FROM empresas WHERE IDContrato = ?", [$IDContrato]);
            DB::delete("DELETE FROM contratos WHERE IDContrato = ?", [$IDContrato]);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    /**
     * Altera o status de um contrato (ativa/desativa).
     *
     * @param  int  $ID
     * @param  int  $ST  Status atual
     * @return int  Número de registros afetados
     */
    public function changeStatus($ID, $ST)
    {
        $novoStatus = ($ST == 1) ? 0 : 1;

        return Contrato::where('IDContrato', $ID)
            ->update(['STContrato' => $novoStatus]);
    }

    /**
     * Altera o status de acesso de colaboradores (individual ou em massa).
     *
     * @param  array  $dados  (ID, atualStatus, mtd)
     * @return int  Número de registros afetados
     */
    public function changeColaborador($dados)
    {
        $novoStatus = ($dados['atualStatus'] == 1) ? 0 : 1;

        if (is_array($dados['ID']) && isset($dados['mtd'])) {
            // Operação em massa
            $ids = $dados['ID'];

            return Colaborador::whereIn('IDColaborador', $ids)
                ->update(['STAcesso' => $novoStatus]);
        }

        // Operação individual
        return Colaborador::where('IDColaborador', $dados['ID'])
            ->update(['STAcesso' => $novoStatus]);
    }

    /**
     * Salva ou atualiza uma filial.
     *
     * @param  array  $dados  Dados da filial
     * @return \App\Models\Filial
     */
    public static function setFilial($dados)
    {
        $IDContrato = $_SESSION['login']['contrato'] ?? 0;
        $IDEmpresa  = self::getEmpresaId($IDContrato);

        $endjson = json_encode([
            "cep"    => $dados['cep'],
            "uf"     => $dados['uf'],
            "rua"    => $dados['rua'],
            "cidade" => $dados['cidade'],
            "bairro" => $dados['bairro'],
            "numero" => $dados['numero'],
        ], JSON_UNESCAPED_UNICODE);

        if (!empty($dados['IDFilial'])) {
            $filial = Filial::find($dados['IDFilial']);
            if ($filial) {
                $filial->update([
                    'DSEnderecoJSON'    => $endjson,
                    'NMFilial'          => $dados['nome'],
                    'NUTelefoneFilial'  => $dados['telefone'],
                ]);
            }
        } else {
            $filial = Filial::create([
                'IDEmpresa'         => $IDEmpresa,
                'DSEnderecoJSON'    => $endjson,
                'NMFilial'          => $dados['nome'],
                'NUTelefoneFilial'  => $dados['telefone'],
            ]);
        }

        return $filial;
    }

    /**
     * Salva uma empresa vinculada a um contrato.
     * Verifica se o CNPJ já está cadastrado.
     *
     * @param  array   $dados  Dados da empresa
     * @return string  JSON encoded
     */
    public function setEmpresa($dados)
    {
        $cnpjExiste = Empresa::where('NUCnpjEmpresa', $dados['cnpj'])->exists();

        if ($cnpjExiste) {
            $retorno['status'] = false;
        } else {
            Empresa::create([
                'IDContrato'        => $dados['contrato'],
                'NMFantasiaEmpresa' => $dados['fantasia'],
                'NMRazaoEmpresa'    => $dados['razao'],
                'NUCnpjEmpresa'     => $dados['cnpj'],
            ]);
            $retorno['status'] = true;
        }

        return json_encode($retorno);
    }

    /**
     * Retorna o ID da empresa vinculada a um contrato.
     *
     * @param  int      $contrato
     * @return int|string
     */
    public static function getEmpresaId($contrato)
    {
        $empresa = Empresa::where('IDContrato', $contrato)->first();

        return $empresa ? $empresa->IDEmpresa : '0';
    }

    /**
     * Retorna colaboradores que ainda NÃO possuem usuário no sistema.
     * Query com NOT IN - usa DB::select.
     *
     * @return array
     */
    public static function getSelectColUser()
    {
        $empresaId = $_SESSION['login']['empresa'];

        return DB::select(
            "SELECT IDColaborador, NMColaborador, NMEmailColaborador 
             FROM colaboradores 
             INNER JOIN filiais USING(IDFilial) 
             WHERE IDColaborador NOT IN (
                 SELECT IDColaborador FROM usuarios
             ) 
             AND IDEmpresa = ?",
            [$empresaId]
        );
    }

    /**
     * Retorna dados da empresa e do usuário.
     *
     * @param  int   $IDEmpresa
     * @param  int   $IDUsuario
     * @return array
     */
    public static function getDadosEmpresa($IDEmpresa, $IDUsuario)
    {
        $dados = [];

        if ($IDEmpresa != 0) {
            $dados['empresa'] = Empresa::find($IDEmpresa);
        }

        // Busca usuário na tabela legada 'usuarios'
        $usuario = DB::select("SELECT * FROM usuarios WHERE IDUsuario = ?", [$IDUsuario]);
        $dados['usuario'] = !empty($usuario) ? $usuario[0] : null;

        return $dados;
    }

    /**
     * Retorna dados da filial e do usuário.
     *
     * @param  int   $IDFilial
     * @param  int   $IDUsuario
     * @return array
     */
    public static function getDadosFilial($IDFilial, $IDUsuario)
    {
        $dados                = [];
        $dados['filial']      = Filial::find($IDFilial);

        $usuario = DB::select("SELECT * FROM usuarios WHERE IDUsuario = ?", [$IDUsuario]);
        $dados['usuario'] = !empty($usuario) ? $usuario[0] : null;

        return $dados;
    }

    /**
     * Salva ou atualiza um contrato.
     *
     * @param  array  $dados  Dados do contrato
     * @return \App\Models\Contrato
     */
    public function setContrato($dados)
    {
        $endereco = json_encode([
            "cep"    => $dados['cep'],
            "uf"     => $dados['uf'],
            "cidade" => $dados['cidade'],
            "rua"    => $dados['rua'],
            "bairro" => $dados['bairro'],
            "numero" => $dados['numero'],
        ], JSON_UNESCAPED_UNICODE);

        if (!empty($dados['IDContrato'])) {
            $contrato = Contrato::find($dados['IDContrato']);
            if ($contrato) {
                $contrato->update([
                    'NMContratante'      => $dados['contratante'],
                    'NMEmailContratante' => $dados['email'],
                    'NUTelefoneContato'  => $dados['telefone'],
                    'IDPlano'            => $dados['plano'],
                    'DSEndContrato'      => $endereco,
                ]);
            }
        } else {
            $contrato = Contrato::create([
                'IDPlano'            => $dados['plano'],
                'DSEndContrato'      => $endereco,
                'NMContratante'      => $dados['contratante'],
                'NMEmailContratante' => $dados['email'],
                'NUCpfContratante'   => $dados['cpf'],
                'NUTelefoneContato'  => $dados['telefone'],
                'IDCriador'          => $_SESSION['login']['dados']['id'],
            ]);
        }

        return $contrato;
    }
}