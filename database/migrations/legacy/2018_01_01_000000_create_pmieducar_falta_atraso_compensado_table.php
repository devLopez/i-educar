<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class CreatePmieducarFaltaAtrasoCompensadoTable extends Migration
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
                
                CREATE TABLE pmieducar.falta_atraso_compensado (
                    cod_compensado integer DEFAULT nextval(\'pmieducar.falta_atraso_compensado_cod_compensado_seq\'::regclass) NOT NULL,
                    ref_cod_escola integer NOT NULL,
                    ref_ref_cod_instituicao integer NOT NULL,
                    ref_cod_servidor integer NOT NULL,
                    ref_usuario_exc integer,
                    ref_usuario_cad integer NOT NULL,
                    data_inicio timestamp without time zone NOT NULL,
                    data_fim timestamp without time zone NOT NULL,
                    data_cadastro timestamp without time zone NOT NULL,
                    data_exclusao timestamp without time zone,
                    ativo smallint DEFAULT (1)::smallint NOT NULL
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
        Schema::dropIfExists('pmieducar.falta_atraso_compensado');
    }
}