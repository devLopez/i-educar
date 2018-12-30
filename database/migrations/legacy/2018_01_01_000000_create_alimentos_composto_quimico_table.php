<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class CreateAlimentosCompostoQuimicoTable extends Migration
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
                
                CREATE SEQUENCE alimentos.composto_quimico_idcom_seq
                    START WITH 1
                    INCREMENT BY 1
                    MINVALUE 0
                    NO MAXVALUE
                    CACHE 1;

                CREATE TABLE alimentos.composto_quimico (
                    idcom integer DEFAULT nextval(\'alimentos.composto_quimico_idcom_seq\'::regclass) NOT NULL,
                    idcli character varying(10) NOT NULL,
                    idgrpq integer NOT NULL,
                    descricao character varying(50) NOT NULL,
                    unidade character varying(5) NOT NULL
                );
                
                ALTER TABLE ONLY alimentos.composto_quimico
                    ADD CONSTRAINT pk_cp_quimico PRIMARY KEY (idcom);

                SELECT pg_catalog.setval(\'alimentos.composto_quimico_idcom_seq\', 1, false);
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
        Schema::dropIfExists('alimentos.composto_quimico');

        DB::unprepared('DROP SEQUENCE alimentos.composto_quimico_idcom_seq;');
    }
}
