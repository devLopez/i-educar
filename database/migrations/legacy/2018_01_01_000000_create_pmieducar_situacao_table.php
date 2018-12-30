<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class CreatePmieducarSituacaoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared(
            '
                SET default_with_oids = true;

                CREATE TABLE pmieducar.situacao (
                    cod_situacao integer DEFAULT nextval(\'pmieducar.situacao_cod_situacao_seq\'::regclass) NOT NULL,
                    ref_usuario_exc integer,
                    ref_usuario_cad integer NOT NULL,
                    nm_situacao character varying(255) NOT NULL,
                    permite_emprestimo smallint DEFAULT (1)::smallint NOT NULL,
                    descricao text,
                    situacao_padrao smallint DEFAULT (0)::smallint NOT NULL,
                    situacao_emprestada smallint DEFAULT (0)::smallint NOT NULL,
                    data_cadastro timestamp without time zone NOT NULL,
                    data_exclusao timestamp without time zone,
                    ativo smallint DEFAULT (1)::smallint NOT NULL,
                    ref_cod_biblioteca integer NOT NULL
                );
            '
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pmieducar.situacao');
    }
}