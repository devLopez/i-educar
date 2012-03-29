<?php

#error_reporting(E_ALL);
#ini_set("display_errors", 1);

/**
 * i-Educar - Sistema de gestão escolar
 *
 * Copyright (C) 2006  Prefeitura Municipal de Itajaí
 *     <ctima@itajai.sc.gov.br>
 *
 * Este programa é software livre; você pode redistribuí-lo e/ou modificá-lo
 * sob os termos da Licença Pública Geral GNU conforme publicada pela Free
 * Software Foundation; tanto a versão 2 da Licença, como (a seu critério)
 * qualquer versão posterior.
 *
 * Este programa é distribuí­do na expectativa de que seja útil, porém, SEM
 * NENHUMA GARANTIA; nem mesmo a garantia implí­cita de COMERCIABILIDADE OU
 * ADEQUAÇÃO A UMA FINALIDADE ESPECÍFICA. Consulte a Licença Pública Geral
 * do GNU para mais detalhes.
 *
 * Você deve ter recebido uma cópia da Licença Pública Geral do GNU junto
 * com este programa; se não, escreva para a Free Software Foundation, Inc., no
 * endereço 59 Temple Street, Suite 330, Boston, MA 02111-1307 USA.
 *
 * @author    Lucas D'Avila <lucasdavila@portabilis.com.br>
 * @category  i-Educar
 * @license   @@license@@
 * @package   Biblioteca
 * @subpackage  Modules
 * @since   Arquivo disponível desde a versão ?
 * @version   $Id$
 */

require_once 'lib/Portabilis/Controller/ApiCoreController.php';
require_once 'include/pmieducar/clsPmieducarExemplar.inc.php';

#require_once 'Core/Controller/Page/EditController.php';
#require_once 'Avaliacao/Model/NotaComponenteDataMapper.php';
#require_once 'Avaliacao/Service/Boletim.php';
#require_once 'App/Model/MatriculaSituacao.php';
#require_once 'RegraAvaliacao/Model/TipoPresenca.php';
#require_once 'RegraAvaliacao/Model/TipoParecerDescritivo.php';
#require_once 'include/pmieducar/clsPmieducarMatricula.inc.php';
#require_once 'include/portabilis/dal.php';
#require_once 'include/pmieducar/clsPmieducarHistoricoEscolar.inc.php';


class ReservaApiController extends ApiCoreController
{
  protected $_dataMapper  = '';#Avaliacao_Model_NotaComponenteDataMapper';
  protected $_nivelAcessoOption = App_Model_NivelAcesso::SOMENTE_BIBLIOTECA;
  protected $_saveOption  = FALSE;
  protected $_deleteOption  = FALSE;
  protected $_titulo   = '';

  #TODO setar código processoAP, copiar da funcionalidade de reserva existente?
  protected $_processoAp  = 0;

  // validadores especificos reserva

  protected function validatesPresenceOfRefCodInstituicao(){
    return $this->validator->validatesPresenceOf($this->getRequest()->ref_cod_instituicao, 'ref_cod_instituicao');
  }


  protected function validatesPresenceOfRefCodEscola(){
    return $this->validator->validatesPresenceOf($this->getRequest()->ref_cod_escola, 'ref_cod_escola');
  }


  protected function validatesPresenceOfRefCodBiblioteca(){
    return $this->validator->validatesPresenceOf($this->getRequest()->ref_cod_biblioteca, 'ref_cod_biblioteca');
  }


  protected function validatesPresenceOfRefCodCliente(){
    return $this->validator->validatesPresenceOf($this->getRequest()->ref_cod_cliente, 'ref_cod_cliente');
  }


  protected function validatesPresenceOfRefCodAcervo(){
    return $this->validator->validatesPresenceOf($this->getRequest()->ref_cod_acervo, 'ref_cod_acervo');
  }


  protected function validatesPresenceOfExemplarId(){
    return $this->validator->validatesPresenceOf($this->getRequest()->exemplar_id, 'exemplar_id');
  }


  // validações negócio

  protected function canAcceptRequest() {
    return parent::canAcceptRequest() &&
           $this->validatesPresenceOfRefCodInstituicao() &&
           $this->validatesPresenceOfRefCodEscola() &&
           $this->validatesPresenceOfRefCodBiblioteca() &&
           $this->validatesPresenceOfRefCodCliente() &&
           $this->validatesPresenceOfRefCodAcervo();
          // TODO validar se cliente da biblioteca
  }


  protected function canPostReserva() {
    return $this->validatesClienteIsNotSuspenso() &&
           $this->validatesPresenceOfExemplarId() &&
           $this->validatesSituacaoExemplarIsIn(array('emprestado', 'reservado', 'emprestado_e_reservado'));
           // TODO qtd reservas em aberto do cliente <= limite biblioteca
           // TODO valor R$ multas em aberto do cliente <= limite biblioteca
  }


  protected function validatesSituacaoExemplarIsIn($situacoes) {
    if (! is_array($situacoes))
      $situacoes = array($situacoes);

    $situacaoAtual = $this->getSituacaoForExemplar();
    $situacaoAtual = $situacaoAtual['flag'];
    $msg = "Situação do exemplar deve estar em (" . implode(', ', $situacoes) . ") porem atualmente é $situacaoAtual.";

    return $this->validator->validatesValueInSetOf($situacaoAtual, $situacoes, 'situação', false, $msg);
  }


  protected function validatesClienteIsNotSuspenso() {
    $cliente = $this->getCliente();

    if($cliente['suspenso']) {
      $this->messenger->append("O cliente esta suspenso", 'error');
      return false;
    }

    return true;
  }


  protected function getAvailableOperationsForResources() {
    return array('exemplares' => array('get'),
                 'reserva'    => array('post')
    );
  }


  protected function getExemplar($id = '') {
    if (empty($id))
      $id = $this->getRequest()->exemplar_id;

    $exemplar         = new clsPmieducarExemplar($id);
    $exemplar         = $exemplar->detalhe();

    $situacaoExemplar = $this->_getSituacaoForExemplar($exemplar);

    return array('id'         => $exemplar['cod_exemplar'],
                 'situacao'   => $situacaoExemplar,
                 'pendencias' => $this->_getPendenciasForExemplar($exemplar, $situacaoExemplar)
    );
  }


  protected function getDataPrevistaDisponivelForExemplar($dataInicio, $exemplar = null) {
    if (is_null($exemplar))
      $exemplar = $this->getExemplar();

    // TODO $dataInicio + tempo emprestimo for tipo exemplar E tipo cliente + dias não trabalho

    return $dataInicio;
  }


  protected function _getPendenciasForExemplar($exemplar, $situacaoExemplar = '') {
    if (empty($situacaoExemplar))
      $situacaoExemplar = $this->_getSituacaoForExemplar($exemplar);

    $pendencias = array();

    if (strpos($situacaoExemplar['flag'], 'emprestado'))
      $pendencias[] = $this->getEmprestimoForExemplar($exemplar);
    elseif (strpos($situacaoExemplar['flag'], 'reservado'))
      $pendencias[] = $this->getReservaForExemplar($exemplar);

    return $pendencias;
  }


  protected function _getSituacaoForExemplar($exemplar) {
    $situacao                  = $this->getSituacaoById($exemplar["ref_cod_situacao"]);

    $reservado                 = $this->existsReservaForExemplar($exemplar);
    $emprestado                = $situacao["situacao_emprestada"] == 1;

    $situacaoPermiteEmprestimo = $situacao["permite_emprestimo"]  == 2;
    $exemplarPermiteEmprestimo = $exemplar["permite_emprestimo"]  == 2;

    if ($emprestado && $reservado)
      $flagSituacaoExemplar = 'emprestado_e_reservado';
    elseif ($emprestado)
      $flagSituacaoExemplar = 'emprestado';
    elseif ($reservado)
      $flagSituacaoExemplar =  'reservado';
    elseif ($situacaoPermiteEmprestimo && $exemplarPermiteEmprestimo)
      $flagSituacaoExemplar = 'disponivel';
    elseif (! $situacaoPermiteEmprestimo || ! $exemplarPermiteEmprestimo)
      $flagSituacaoExemplar = 'indisponivel';
    else
      $flagSituacaoExemplar = 'invalida';

    return $this->getSituacaoForFlag($flagSituacaoExemplar);
  }


  protected function getSituacaoForFlag($flag) {
    $situacoes = array(
      'indisponivel'           => array('flag'  => 'indisponivel', 'label' => 'Indisponível'),
      'disponivel'             => array('flag'  => 'disponivel'  , 'label' => 'Disponível'  ),
      'emprestado'             => array('flag'  => 'emprestado'  , 'label' => 'Emprestado'  ),
      'reservado'              => array('flag'  => 'reservado'   , 'label' => 'Reservado'   ),
      'emprestado_e_reservado' => array('flag'  => 'emprestado_e_reservado',
                                        'label' => 'Emprestado e reservado'                ),
      'invalida'               => array('flag'  => 'invalida'    , 'label' => 'Inválida'    )
    );

    return $situacoes[$flag];
  }


  protected function getSituacaoForExemplar($exemplar = null) {
    if (is_null($exemplar))
      $exemplar = $this->getExemplar();

    return $exemplar['situacao'];
  }


  protected function getSituacaoById($id) {
    $situacao = new clsPmieducarSituacao($id);
    return $situacao->detalhe();
  }


  protected function getEmprestimoForExemplar($exemplar = null) {
    if (is_null($exemplar))
      $exemplar = $this->getExemplar();

    $_emprestimo = array('cliente'                => null,
                         'nomeCliente'            => '',
                         'data'                   => '',
                         'dataPrevistaDisponivel' => '',
                         'exists'                 => false,
                         'situacao'               => $this->getSituacaoForFlag('emprestado')
    );

    // TODO get reserva

    return $_emprestimo;
  }


  protected function existsReservaForExemplar($exemplar = null) {
    $reserva = $this->getReservaForExemplar($exemplar);
    return $reserva['exists'];
  }


  protected function getReservaForExemplar($exemplar = null) {
    if (is_null($exemplar))
      $exemplar = $this->getExemplar();

    $_reserva = array('cliente'                => null,
                      'nomeCliente'            => '',
                      'data'                   => '',
                      'dataPrevistaDisponivel' => '',
                      'exists'                 => false,
                      'situacao'               => $this->getSituacaoForFlag('reservado')
    );


		$reserva = new clsPmieducarReservas();
		$reserva = $reserva->lista(null,
                               null,
                               null,
                               null,
                               null,
                               null,
                               null,
                               null,
                               null,
                               null,
                               $exemplar['cod_exemplar'],
                               1,
                               $this->getRequest()->ref_cod_biblioteca,
                               $this->getRequest()->ref_cod_instituicao,
                               $this->getRequest()->ref_cod_escola);

		if(is_array($reserva) && ! empty($reserva)) {
			$reserva                            = array_shift($reserva);
      $cliente                            = $this->getCliente($reserva["ref_cod_cliente"]);
      $dataPrevistaDisponivel             = date('d/m/Y', strtotime($reserva['data_prevista_disponivel']));

      $_reserva['exists']                 = true;
      $_reserva['dataReserva']            = date('d/m/Y', strtotime($reserva['data_reserva']));
      $_reserva['dataPrevistaDisponivel'] = $this->getDataPrevistaDisponivelForExemplar($dataPrevistaDisponivel, $exemplar);
      $_reserva['cliente']                = $cliente;
      $_reserva['nomeCliente']            = $cliente['id'] . ' - ' . $cliente['nome'];
    }

    return $_reserva;
  }


  protected function getCliente($id = '') {

    if (empty($id))
      $id = $this->getRequest()->ref_cod_cliente;

    $_cliente = array('id' => $id);

		$cliente = new clsPmieducarCliente($id);
		$cliente = $cliente->detalhe();

    $_cliente['pessoaId'] = $cliente["ref_idpes"];

		$pessoa = new clsPessoa_($_cliente['pessoaId']);
		$pessoa = $pessoa->detalhe();

    $_cliente['nome']        = $pessoa["nome"];

    $sql = "select 1 from pmieducar.cliente_suspensao where ref_cod_cliente = $1 and data_liberacao is null and data_suspensao + (dias||' day')::interval >= now()";
    $suspenso = $this->fetchPreparedQuery($sql, $params = array($id), true, 'first-field');

    $_cliente['suspenso'] = $suspenso == '1';

    return $_cliente;
  }


  // metódos resposta operação / recurso

  protected function getExemplares() {

		$exemplares = new clsPmieducarExemplar();
    $exemplares = $exemplares->lista(null,
                                     null,
                                     null,
                                     $this->getRequest()->ref_cod_acervo,
                                     null,
                                     null,
                                     null,
                                     null,
                                     null,
                                     null,
                                     null,
                                     null,
                                     null,
                                     1,
                                     null,
                                     null,
                                     null,
                                     null,
                                     $this->getRequest()->ref_cod_biblioteca,
                                     null,
                                     $this->getRequest()->ref_cod_instituicao,
                                     $this->getRequest()->ref_cod_escola);

    $_exemplares = array();

    foreach($exemplares as $exemplar) {
      $_exemplares[] = $this->getExemplar($exemplar['cod_exemplar']);
    }

    return $_exemplares;
  }


  protected function postReserva() {
    if ($this->canPostReserva()) {
      //TODO try pegar excessoes no post, se pegar add msg erro inesperado

        $this->messenger->append("Reserva realizada com sucesso.", 'success');
      //TODO fim try

      $situacaoExemplar = $this->getSituacaoForExemplar();

      $this->appendResponse('situacao_exemplar', $situacaoExemplar);
      $this->appendResponse('pendencias', $this->_getPendenciasForExemplar($exemplar, $situacaoExemplar));
    }
  }


  public function Gerar() {
    if ($this->isRequestFor('get', 'exemplares'))
      $this->appendResponse('exemplares', $this->getExemplares());

    elseif ($this->isRequestFor('post', 'reserva'))
      $this->postReserva();

    else
      $this->notImplementedOperationError();
  }
}
