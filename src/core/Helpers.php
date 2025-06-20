<?php

function dd($data)
{
    echo '<pre>';
    var_dump($data);
    echo '</pre>';
    die();
}

function config($chave = null)
{
    $config = require __DIR__ . '/../../config/config.php';

    if (strlen($chave) > 0) {
        return $config[$chave];
    }

    return $config;
}

function flash()
{
    return new Flash;
}

function auth()
{
    if (!isset($_SESSION['auth'])) {
        return false;
    }

    return $_SESSION['auth'];
}

function redirectNotPost($url)
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        redirect($url);
        return;
    }
}

function flashRedirect($status, $mensagem, $url)
{
    flash()->setMensagem($status, $mensagem);
    header("Location: $url");
    exit;
}

function redirect($url)
{
    header("Location: $url");
    exit;
}

function usuarioAutenticadoOuRedireciona($url)
{
    $usuario_id = auth()->id;

    if (!$usuario_id) {
        flashRedirect('error', 'Usuário não autenticado!', $url, 'filme');
    }

    return $usuario_id;
}

function validarIdOuRedirecionar($paramName, $redirectUrl, $mensagemErro = 'ID inválido!', $item = 'usuario')
{
    if (!isset($_GET[$paramName]) || !is_numeric($_GET[$paramName])) {
        flashRedirect('error', $mensagemErro, $redirectUrl, $item);
    }

    return (int) $_GET[$paramName];
}