<?php
namespace App\Http\Controllers;

use App\Pessoa;
use App\PessoaPerfil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class AppController extends Controller
{

    // Route::post('/solicitacao-entrega', 'AppController@solicitarEntrega');
    // Route::post('/operacao-usuario', 'AppController@atenderOperacao');
    // Route::get('/info-financeiras', 'AppController@obterInfoFinanceiras');
    // Route::post('/transferencia', 'AppController@realizarTransferencia');   

    public function login(Request $request)
    {
        try {
            $user = $request->input('user');
            $password = $request->input('password');
            if ($user==null) {
                return response()->json(['error' => 'User esta nulo'], 401); 
            }
            $credentials = ['user' => $user, 'password' => $password];            
            if (! $token = auth()->attempt($credentials)) {
                return response()->json(['error' => 'Unauthorized'], 401); 
            }         
            $pessoas = DB::table('users')
                ->leftJoin('tt_empresa', 'tt_empresa.idPessoa', '=', 'users.id')
                ->select('users.id', 'users.Nome',
                    'tt_empresa.idEmpresa', 'tt_empresa.Empresa')
                ->where('user','=',$user)
                ->first();
            $idEmpresa = $pessoas->idEmpresa == null ? 0 : $pessoas->idEmpresa;
            $Empresa = $pessoas->Empresa == null ? "" : $pessoas->Empresa;
            $dados = ['Erro' => 0,
                'DescErro' => '',
                'id' => $pessoas->id,
                'nome' => $pessoas->Nome,
                'idEmpresa' => $idEmpresa,
                'Empresa' => $Empresa,
                'token' => $token,
            ];        
            /* if ($idEmpresa>0) {
                $cForn->EsseFornOnLine($idForn, 3);
            } */           
            return json_encode($dados);
        } catch (\Exception $e) {
            echo "Erro: " . $e->getMessage();
        }
    } 
        
    public function cadboy(Request $request)
    {
        $nome = $request->input('nome_completo');
        $hashedPassword = Hash::make($request->input('senha'));
        $telefone = $request->input('telefone');
        $ddd = substr($telefone, 0, strlen($telefone) == 11 ? 3 : 2);
        $fone = substr($telefone, strlen($telefone) == 11 ? 3 : 2);        
        $user = new Pessoa;
        $user->Nome = $request->input('nome_completo');
        $user->email = $request->input('email');
        $user->user = $user->email;
        $user->idCaptador = 1;
        $user->password = $hashedPassword;
        $user->ddd = $ddd;
        $user->fone = $fone; 
        $user->cnh = $request->input('cnh'); 
        $user->obs = $request->input('documento_veiculo');     
        $user->save();
        $pessoaperfil = new PessoaPerfil;
        $pessoaperfil->idPessoa = $user->id;
        $pessoaperfil->idPerfil  = 8;
        $pessoaperfil->save();
        return response()->json(['id' => $user->id], 201);
    }    

}
