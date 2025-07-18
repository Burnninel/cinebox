<?php

namespace Cinebox\App\Services;

use Cinebox\App\Core\BaseService;
use Cinebox\App\Core\Database;
use Cinebox\App\Models\Usuario;
use Cinebox\App\Utils\Validacao;
use Cinebox\App\Helpers\Insert;
use Cinebox\App\Helpers\Log;

class AuthService extends BaseService
{
    protected Database $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function validarLogin(array $dados): array
    {
        $regras = [
            'email' => ['required', 'email'],
            'senha' => ['required'],
        ];

        $validador = $this->safe(
            fn() => Validacao::validarCampos($regras, $dados, $this->database),
            'Erro ao consultar banco de dados.'
        );

        return $validador->erros();
    }

    public function validarRegistro(array $dados): array
    {
        $regras = [
            'nome' => ['required', 'string', 'min:5'],
            'email' => ['required', 'email', 'unique:usuarios'],
            'senha' => ['required', 'min:8', 'max:24', 'strong', 'confirmed'],
        ];

        $validador = $this->safe(
            fn() => Validacao::validarCampos($regras, $dados, $this->database),
            'Erro ao consultar banco de dados.'
        );

        if (isset($validador->erros()['email']) && $validador->erros()['email'] === 'Já está em uso.') {
            Log::warning('Tentativa de registro com e-mail já cadastrado.', ['email' => $dados['email']]);
        }

        return $validador->erros();
    }

    public function autenticar(string $email, string $senha): ?Usuario
    {
        $usuario = $this->safe(
            fn() => Usuario::buscarUsuarioPorEmail($this->database, $email),
            'Erro ao consultar banco de dados.'
        );

        if ($usuario && $usuario->verificarSenha($senha)) {
            Log::info("Autenticação bem-sucedida.", ['usuario' => $usuario->id]);
            return $usuario;
        } else {
            Log::warning("Falha na autenticação.", ['email' => $email]);
            return null;
        }
    }

    public function registrar(array $dados): Usuario
    {
        $dadosFiltrados = [
            'nome' => trim($dados['nome']),
            'email' => trim($dados['email']),
            'senha' => Usuario::hashSenha($dados['senha'])
        ];

        $usuario = $this->safe(
            fn() => Insert::execute(
                fn() => Usuario::cadastrarUsuario($this->database, $dadosFiltrados),
                fn() => Usuario::buscarUsuarioPorEmail($this->database, $dadosFiltrados['email']),
            ),
            'Erro ao inserir usuario no banco de dados.'
        );

        Log::info('Usuário registrado com sucesso.', ['usuario' => $usuario->id]);

        return $usuario;
    }
}
