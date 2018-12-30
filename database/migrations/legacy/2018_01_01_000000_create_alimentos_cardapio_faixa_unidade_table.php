<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class CreateAlimentosCardapioFaixaUnidadeTable extends Migration
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
                
                CREATE TABLE alimentos.cardapio_faixa_unidade (
                    idfeu integer NOT NULL,
                    idcar integer NOT NULL
                );
                
                ALTER TABLE ONLY alimentos.cardapio_faixa_unidade
                    ADD CONSTRAINT pk_cardapio_faixa_unidade PRIMARY KEY (idfeu, idcar);
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
        Schema::dropIfExists('alimentos.cardapio_faixa_unidade');
    }
}
