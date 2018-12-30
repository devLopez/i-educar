<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class CreatePmieducarCalendarioDiaMotivoTable extends Migration
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
                
                CREATE TABLE pmieducar.calendario_dia_motivo (
                    cod_calendario_dia_motivo integer DEFAULT nextval(\'pmieducar.calendario_dia_motivo_cod_calendario_dia_motivo_seq\'::regclass) NOT NULL,
                    ref_cod_escola integer NOT NULL,
                    ref_usuario_exc integer,
                    ref_usuario_cad integer NOT NULL,
                    sigla character varying(15) NOT NULL,
                    descricao text,
                    tipo character(1) NOT NULL,
                    data_cadastro timestamp without time zone NOT NULL,
                    data_exclusao timestamp without time zone,
                    ativo smallint DEFAULT (1)::smallint NOT NULL,
                    nm_motivo character varying(255) NOT NULL
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
        Schema::dropIfExists('pmieducar.calendario_dia_motivo');
    }
}