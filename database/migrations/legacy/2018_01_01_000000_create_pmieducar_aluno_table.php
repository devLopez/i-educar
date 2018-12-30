<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class CreatePmieducarAlunoTable extends Migration
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
                
                CREATE TABLE pmieducar.aluno (
                    cod_aluno integer DEFAULT nextval(\'pmieducar.aluno_cod_aluno_seq\'::regclass) NOT NULL,
                    ref_cod_religiao integer,
                    ref_usuario_exc integer,
                    ref_usuario_cad integer,
                    ref_idpes integer,
                    data_cadastro timestamp without time zone NOT NULL,
                    data_exclusao timestamp without time zone,
                    ativo smallint DEFAULT (1)::smallint NOT NULL,
                    caminho_foto character varying(255),
                    analfabeto smallint DEFAULT (0)::smallint,
                    nm_pai character varying(255),
                    nm_mae character varying(255),
                    tipo_responsavel character(1),
                    aluno_estado_id character varying(25),
                    justificativa_falta_documentacao smallint,
                    url_laudo_medico json,
                    codigo_sistema character varying(30),
                    veiculo_transporte_escolar smallint,
                    autorizado_um character varying(150),
                    parentesco_um character varying(150),
                    autorizado_dois character varying(150),
                    parentesco_dois character varying(150),
                    autorizado_tres character varying(150),
                    parentesco_tres character varying(150),
                    autorizado_quatro character varying(150),
                    parentesco_quatro character varying(150),
                    autorizado_cinco character varying(150),
                    parentesco_cinco character varying(150),
                    url_documento json,
                    recebe_escolarizacao_em_outro_espaco smallint DEFAULT 3 NOT NULL,
                    recursos_prova_inep integer[]
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
        Schema::dropIfExists('pmieducar.aluno');
    }
}