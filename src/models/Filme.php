<?php

class Filme
{
    public $id;
    public $titulo;
    public $diretor;
    public $ano_de_lancamento;
    public $sinopse;
    public $categoria;
    public $total_avaliacoes;
    public $media_avaliacoes;

    public function query($database, $where, $params = [])
    {
        return $database->query(
            query: "
                SELECT 
                    f.*,
                    COUNT(a.id) AS total_avaliacoes,
                    ROUND(AVG(a.nota), 1) AS media_avaliacoes
                FROM 
                    filmes AS f
                LEFT JOIN
                    avaliacoes AS a ON f.id = a.filme_id
                WHERE $where
                GROUP BY f.id;
            ",
            params: $params,
            class: self::class
        );
    }

    public static function getFilmePorId($database, $id)
    {
        return (new self)->query(
            $database,
            where: 'f.id = :id',
            params: ['id' => $id]
        )->fetch();
    }

    public static function getFilmes($database, $pesquisar)
    {
        if (!$pesquisar) {
            return [];
        }

        $where = 'LOWER(f.titulo) LIKE :pesquisar 
                  OR LOWER(f.diretor) LIKE :pesquisar 
                  OR LOWER(f.categoria) LIKE :pesquisar';

        $params = ['pesquisar' => "%$pesquisar%"];

        return (new self)->query($database, $where, $params)->fetchAll();
    }

    public static function getFilmesPorUsuario($database, $usuarioId)
    {
        $where = "f.id IN (
                SELECT uf.filme_id 
                FROM usuarios_filmes uf
                WHERE uf.usuario_id = :usuario_id
        )";

        $params = ['usuario_id' => $usuarioId];

        return (new self)->query($database, $where, $params)->fetchAll();
    }

    public static function incluirNovoFilme($database, $dados, $usuario_id)
    {
        $database->query(
            "INSERT INTO filmes (titulo, diretor, ano_de_lancamento, sinopse, categoria)
             VALUES (:titulo, :diretor, :ano_de_lancamento, :sinopse, :categoria)",
            params: $dados
        );

        $filme_id = $database->lastInsertId();

        $database->query(
            "INSERT INTO usuarios_filmes (usuario_id, filme_id) VALUES (:usuario_id, :filme_id)",
            params: [
                'usuario_id' => $usuario_id,
                'filme_id' => $filme_id
            ]
        );

        return $filme_id;
    }

    public static function verificarFavoritado($database, $dados)
    {
        return $database->query(
            "SELECT * FROM usuarios_filmes WHERE usuario_id = :usuario_id AND filme_id = :filme_id",
            params: $dados
        )->fetch();
    }

    public static function favoritar($database, $dados)
    {
        return $database->query(
            "INSERT INTO usuarios_filmes (usuario_id, filme_id) VALUES (:usuario_id, :filme_id)",
            params: $dados
        );
    }

    public static function desfavoritar($database, $dados)
    {
        return $database->query(
            "DELETE FROM usuarios_filmes WHERE usuario_id = :usuario_id AND filme_id = :filme_id",
            params: $dados
        );
    }
}
