<?php 
/*
	Plugin Name: SMSdeck Cliente
	Plugin URI: http://smsdeck.com.br/documentacao
	Description: Permite enviar SMS através do 'SMSdeck.com.br'.
	Version: 1.1
	Author: John-Henrique 
	Author URI: http://johnhenrique.com
*/

/* 
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
Online: http://www.gnu.org/licenses/gpl.txt
*/

/**
 * Classe responsável por enviar os disparos.
 * 
 * Ela deve ser capaz de:
 *  - Armazenar os dados da conta (token)
 *  - Disparar
 *  - Consultar saldo
 * 
 */
class Smsdeck_Cliente {

	
	/**
	 * Chave token para acesso remoto
	 *
	 * @var String
	 */
	protected $token = 0;
	
	
	/**
	 * Numero do celular que funcionará 
	 * como remetente
	 *
	 * @var String
	 */
	protected $celular = '';
	
	
	
	function __construct(){
		
		$smsdeck_dados 	= get_option( 'smsdeck_dados' );
		
		//$this->token 	= '3c8ebf87636ed4b28feb155eca441013';
		$this->token 	= $smsdeck_dados['smsdeck_token'];
		$this->celular 	= $smsdeck_dados['smsdeck_celular'];
	}
	
	
	/**
	 * Realiza as consultas no servidor
	 *
	 * @param String $server_api slug da ação na API (sms|saldo|compra)
	 * @param Array $fields campos necessários na consulta
	 * @return String
	 */
	public function curl_query( $server_api = '', $fields = array() ){
		
		// tentando evitar o token vazio
		if( !isset( $fields['token'] ) ){
			$g = new Smsdeck_Cliente();
			$fields['token'] = $g->token;
		}

		
		// cria uma conexão CURL
		$curl = curl_init( "http://smsdeck.com.br/api/". $server_api ); 
		//$curl = curl_init( "http://woocommerce.net/api/". $server_api ); 
		
		// define que o envio deve ser via POST
		curl_setopt($curl, CURLOPT_POST, 1);
		
		// define o conteúdo da mensagem
		curl_setopt($curl, CURLOPT_POSTFIELDS, $fields ); 
		
		// ignora cabeçalhos
		curl_setopt($curl, CURLOPT_HEADER, 0);
		
		// retorna o resultado como texto
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); 
		
		// saida do conteudo na variavel $resultado
		$resposta = curl_exec($curl); 
		
		// fecha a conexão CURL 
		curl_close($curl);
		
		
		// as vezes o retorno traz espaços
		return trim( $resposta );
	}
	
	
	
	/**
	 * Consulta o saldo de créditos no SMSdeck
	 * 
	 * Só é necessário enviar o token
	 *
	 */
	public function smsdeck_consulta_creditos(){
		return Smsdeck_Cliente::curl_query( 'saldo', array() );
	}
	
	
	/**
	 * Adiciona o botão para comprar créditos
	 * 
	 * Como trata-se apenas do pedido não faz 
	 * diferença onde ele está sendo feito, a 
	 * segurança e confirmação do pagamento 
	 * ocorrem na loja
	 *
	 */
	public function smsdeck_comprar_creditos(){
		echo "<h3>Comprar créditos</h3>";
	}
	
	
	/**
	 * Informa se a API está respondendo 
	 * corretamente, caso esteja, o retorno 
	 * será sempre "ok" (texto puro) do 
	 * contrario algum erro está ocorrendo
	 *
	 */
	public function smsdeck_ping(){
		$smsdeck = new Smsdeck_Cliente();
		
		$status = $smsdeck->curl_query( 'ping', array() );
		
		if( $status == "ok" ){
			echo "<p>Server online</p>";
		}else{
			echo "<p>Problemas na conexão com o servidor SMSdeck</p>";
		}
		
	}
	
	
	/**
	 * Exibe o formulário de configurações 
	 * deste plugin
	 *
	 */
	function smsdeck_configuracoes(){
		$smsdeck = new Smsdeck_Cliente();
		
		$teste = get_option( 'smsdeck_dados');
		
		?>		
		<div class="wrap">
		<?php screen_icon('smsdeck'); ?>
		<h2>Configurações</h2>
		
		<?php $smsdeck->smsdeck_salvar_dados(); ?>
		
		<p>
			Nesta tela você pode informar o número do celular que aparecerá como remetente nas mensagens SMS enviadas pelo sistema. 
			Você também pode adicionar sua <a href="http://smsdeck.com.br/documentacao/gerar-token-de-acesso.html" target="_blank">chave token</a> 
			para integração do SMSdeck com o seu site.
		</p>
		
		
			<form name="smsdeck_form" id="smsdeck_form" action="edit.php?post_type=torpedo_sms&page=smsdeck-configuracoes&action=<?php echo rand(0,5); ?>" method="post">
				<div class="notice" style="display:none;"></div>
				  <table width="100%" border="0" cellspacing="5" cellpadding="5">
				    <tr>
				      <th scope="row"><label for="smsdeck_celular">Celular</label></th>
				      <td><input name="smsdeck_celular" type="text" id="smsdeck_celular" value="<?php echo $smsdeck->celular; ?>" size="40"></td>
				      <td rowspan="4" class="smsdeck_coluna">
				      	<?php $smsdeck->smsdeck_comprar_creditos(); ?>

				        <?php 
				        if( Smsdeck_Cliente::curl_query( 'ping' ) == "ok" ): ?>
				        	<span class="button button-primary-disabled button-large">SMSdeck está <b>online</b></span>
				        <?php else: ?>
				        	<span class="button button-primary-disabled button-large status-online">SMSdeck está <b>offline</b> neste momento</span>
				        <?php endif; ?>
				      </td>
				    </tr>
				    <tr>
				      <th scope="row"><label for="smsdeck_token2">Token</label></th>
				      <td><input name="smsdeck_token" type="text" id="smsdeck_token2" value="<?php echo $smsdeck->token; ?>" size="40"></td>
				    </tr>
				    <tr>
				      <th scope="row">Saldo</th>
				      <td>
						<?php 
						$saldo = $smsdeck->smsdeck_consulta_creditos();

						if( $saldo > 0 ):
						?>
							<p><?php echo $saldo; ?> torpedos SMS</p>
						<?php else: ?>
							<p>Você não possui saldo de torpedos SMS</p>
							<p><a href="http://smsdeck.com.br/loja" target="_blank">Compre agora 100 torpedos por R$ 19,50</a></p>
						<?php endif; ?>
				      </td>
				    </tr>
				    <tr>
				      <th scope="row">&nbsp;</th>
				      <td><input name="smsdeck_botao_salvar" type="submit" id="smsdeck_botao_salvar" value="Salvar altera&ccedil;&otilde;es" class="button button-primary button-large" ></td>
				    </tr>
				  </table>
				  
			</form>
		</div>
		<?php
	}
	
	
	
	/**
	 * Salva os dados de configurações 
	 * e informa se os dados foram salvos 
	 * ou nao
	 *
	 */
	protected function smsdeck_salvar_dados(){
		if( isset( $_POST['smsdeck_token'] ) ){
			if( update_option( 'smsdeck_dados', $_POST ) ){
			?>
				<div id="message" class="updated"><p>Dados salvos</p></div>
			<?php }else{ ?>
				<div id="message" class="updated"><p>Houve um erro ao tentar salvar os dados, tente novamente.</p></div>
			<?php 
			}
		}
	}
	
	/**
	 * Responsável pela comunicação via back end 
	 * ocorrendo via REST API
	 *
	 */
	public function smsdeck_dispara(){
		
		return $this->curl_query( 'sms', $_POST );
	}
	
	
	/**
	 * Shortcode para exibir formulário de envio
	 * dentro do WordPress
	 *
	 * @return string
	 */
	public function smsdeck_sms_formulario(){

		?>
			<p>Preencha os campos com o telefone de destino e mensagem, em seguida clique em "Enviar SMS"</p>
			<div class="notice"></div>
			<form id="smsdeck_formulario" name="smsdeck_formulario" method="post" action="" style="display:none;" >
			    <table width="100%" border="0" cellspacing="5" cellpadding="5">
			      <tr>
			        <th scope="row"><label for="celular_destino">Celular origem</label></th>
			        <td><input type="text" name="numero_origem" id="numero_origem" placeholder="DDD+telefone" /></td>
			      <tr>
			        <th scope="row"><label for="celular_destino">Celular destino</label></th>
			        <td><input type="text" name="numero_destino" id="numero_destino" placeholder="DDD+telefone" /></td>
			      </tr>
			      <tr>
			        <th scope="row"><label for="mensagem">Mensagem</label></th>
			        <td>
			        	<textarea name="mensagem" id="mensagem" cols="45" rows="5" placeholder="Uma mensagem com até 160 digitos"></textarea>
			        	<div class="contador"><span></span></div>
			        </td>
			      </tr>
			      <tr>
			        <th scope="row"><input name="action" type="hidden" value="smsdeck_ajax" /></th>
			        <td>
			        <?php 
			        if( Smsdeck_Cliente::curl_query( 'ping' ) == "ok" ): ?>
			        	<input type="submit" name="button" value="Enviar SMS" class="button button-primary button-large"  />
			        <?php else: ?>
			        	<span class="button button-primary-disabled button-large">SMSdeck está offline neste momento</span>
			        <?php endif; ?>
			        </td>
			      </tr>
			    </table>
			</form>
			
		<?php
		//return $html;
	}
	
	
	/**
	 * Exibe o formulário de envio 
	 * na administração
	 *
	 */
	public function smsdeck_enviar_sms(){
		?>
		<div class="wrap">
		<?php screen_icon('smsdeck'); ?>
		<h2>Enviar SMS</h2>
			<?php Smsdeck_Cliente::smsdeck_sms_formulario(); ?>
		</div>
		<?php 
	}
	
	
	/**
	 * Responsável pela comunicação AJAX
	 *
	 */
	public function smsdeck_ajax(){
		$smsdeck = new Smsdeck_Cliente();
		$resposta = $smsdeck->smsdeck_dispara();
		
		
		echo print_r( $resposta, true );
		
		// mata o processo para utilizar AJAX
		die();
	}
	
	
	/**
	 * O JavaScript necessário para o AJAX
	 *
	 */
	public function smsdeck_javascript(){
	?>
	<script type="text/javascript">
	jQuery(function(){
		
		$formulario = jQuery( "#smsdeck_formulario" );
		
		// exibe o formulário apos 1 segundo de espera
		$formulario.delay(1000).slideDown();
		
		
		
		$mensagem = jQuery( "#mensagem" );
		$numero_origem = jQuery( "#numero_origem" );
		$numero_destino = jQuery( "#numero_destino" );
		
		
		// Ações para quando o formulário for enviado
		$formulario.submit( function(){
			
			/**
			 * Validando dados
			 * Telefones 11222233334
			 */
			
			if( $numero_origem.val() == '' ){
				jQuery( ".notice" ).html( "ERRO: Você precisa informar o número do telefone de origem" ).show().delay(3000).fadeOut(1000);
				$numero_origem.focus();
				return false;
			}
			
			// verificando se o numero do telefone tem entre 10 e 11 digitos
			if( ( $numero_origem.val().length >= 10 ) && ( $numero_origem.val().length <= 11 ) ){
			}else{
				jQuery( ".notice" ).html( "ERRO: O telefone de origem informado está incompleto" ).show().delay(3000).fadeOut(1000);
				$numero_origem.focus();
				return false;
			}
			
			
			
			
			// numero de destino
			
			if( $numero_destino.val() == '' ){
				jQuery( ".notice" ).html( "ERRO: Você precisa informar o número do telefone de destino" ).show().delay(3000).fadeOut(1000);
				$numero_destino.focus();
				return false;
			}
			
			// verificando se o numero do telefone tem entre 10 e 11 digitos
			if( ( $numero_destino.val().length >= 10 ) && ( $numero_destino.val().length <= 11 ) ){
			}else{
				jQuery( ".notice" ).html( "ERRO: O telefone de destino informado está incompleto" ).show().delay(3000).fadeOut(1000);
				$numero_destino.focus();
				return false;
			}
			
			
			// verificando se a mensagem foi digitada
			if( $mensagem.val() == '' ){
				jQuery( ".notice" ).html( "ERRO: Você precisa informar a mensagem" ).show().delay(3000).fadeOut(1000);
				$mensagem.focus();
				return false;
			}
			
			
			/**
			 * Requisição AJAX
			 */
			jQuery.ajax({
				type: 'POST',
				url: '/wp-admin/admin-ajax.php',
				data: $formulario.serialize(), 
				//dataType: 'xml', 
				timeout: 10000, 
				beforeSend: function(){
					// antes de enviar
					jQuery( ".notice" ).text( "Processando envio" ).show();
				},
				success: function( response ){
					
					$resposta 			= jQuery( response );
					mensagem_status 	= $resposta.find( 'status' ).text();
					mensagem_erro 		= $resposta.find( 'erro' ).text();
					mensagem_statusMsg 	= $resposta.find( 'statusMsg' ).text();
					
					mensagem_resposta = '';
					
					
					if( mensagem_erro != '' ){
						mensagem_resposta +="<BR>Erro: "+ mensagem_erro;
					}
					
					if( mensagem_status != '' ){
						mensagem_resposta +="<BR>Status: "+ mensagem_status;
					}
					
					if( mensagem_statusMsg != '' ){
						mensagem_resposta +="<BR>MSG: "+ mensagem_statusMsg;
					}
					
					
					jQuery( ".notice" ).html( "RESPOSTA: "+ mensagem_resposta ).show().delay(6000).fadeOut(1000);
					
					// limpa os campos caso não ocorra erros
					if( mensagem_erro == '' ){
						//Laço para selecionar todos os formulários
						$formulario.each(function(){
						   this.reset(); //Cada volta no laço o form atual será resetado
						});
					}
				},
				error: function( response ){
					
					$resposta 			= jQuery( response );
					mensagem_status 	= $resposta.find( 'status' ).text();
					mensagem_erro 		= $resposta.find( 'erro' ).text();
					mensagem_statusMsg 	= $resposta.find( 'statusMsg' ).text();
					
					mensagem_resposta = '';
					
					
					if( mensagem_erro != '' ){
						mensagem_resposta +="<BR>Erro: "+ mensagem_erro;
					}
					
					if( mensagem_status != '' ){
						mensagem_resposta +="<BR>Status: "+ mensagem_status;
					}
					
					if( mensagem_statusMsg != '' ){
						mensagem_resposta +="<BR>MSG: "+ mensagem_statusMsg;
					}
					
					jQuery( ".notice" ).text( "Occorreu um erro: "+ mensagem_resposta ).show();
				}
			});
			
			// para a execução da página
			return false;
		});
		
		
		
		// conta os caracteres da mensagem
		function conta_mensagem( campo_onde, qtd_maximo_caracteres ){
			tamanho_imagem = 300;
			
			$campo = jQuery( campo_onde );
			$caracteres = $campo.val().length;
			
			// Quantidade de caracteres restantes
			qtd_digitos = ( parseInt( qtd_maximo_caracteres ) - parseInt( $caracteres ) );
			
			// verificando se a mensagem tem no máximo 160 digitos
			if( $caracteres <= qtd_maximo_caracteres ){
				
				// porcentagem completada
				posicao = tamanho_imagem - parseInt( ( tamanho_imagem * parseInt( $caracteres ) ) / qtd_maximo_caracteres );
				
				jQuery( ".contador span" ).html( qtd_digitos +" " ).css({
					 'position':'relative', 
					 'left': posicao +'px', 
					 'top': 0
				});
				jQuery( ".contador" ).css( 'background', 'url("<?php echo plugin_dir_url( __FILE__ ); ?>img/fundo.jpg") -'+ posicao +'px 0px' );
			}else{
				$campo.val( $campo.val().substr( 0, qtd_maximo_caracteres ) );
				
				jQuery( ".contador" ).css( 'background', 'url("<?php echo plugin_dir_url( __FILE__ ); ?>img/fundo.jpg") 0px 0px' );
			}
		}
		
	
		
		
		/*
			Conta os caracteres pré validação
		*/
		$mensagem.keyup( function(){
			conta_mensagem( "#mensagem", 160);
		});
		
		// apenas numeros
		$numero_destino.keyup( function(e){
	        e.preventDefault();
	        var expre = /[^0-9]/g;
	        // REMOVE OS CARACTERES DA EXPRESSAO ACIMA
	        if (jQuery(this).val().match(expre))
	            jQuery(this).val(jQuery(this).val().replace(expre,''));
	            
			conta_mensagem( '#numero_destino', 11);
		});
		
		$numero_origem.keyup( function(e){
	        e.preventDefault();
	        var expre = /[^0-9]/g;
	        // REMOVE OS CARACTERES DA EXPRESSAO ACIMA
	        if (jQuery(this).val().match(expre))
	            jQuery(this).val(jQuery(this).val().replace(expre,''));
	            
			conta_mensagem( "#numero_origem", 11);
		});
		
		
		
		
		
		jQuery("body").ajaxStart( function(){ 
			// When ajaxStart is fired, add 'loading' to body class
		    jQuery( this ).addClass("loading"); 
		}).ajaxStop( function(){ 
			// When ajaxStop is fired, rmeove 'loading' from body class
		    jQuery( this ).removeClass("loading"); 
		});
		
		
		

		// configurações
		$smsdeck_celular 	= jQuery( "#smsdeck_celular" );
		$smsdeck_token 		= jQuery( "#smsdeck_token2" );
		
		jQuery( "#smsdeck_form" ).submit(function(){
				
			
			/**
			 * Validando dados
			 * Telefones 11222233334
			 */
			if( $smsdeck_celular.val() == '' ){
				jQuery( ".notice" ).html( "ERRO: Você precisa informar o número do telefone" ).show().delay(3000).fadeOut(1000);
				$smsdeck_celular.focus();
				return false;
			}
			
			// verificando se o numero do telefone tem entre 10 e 11 digitos 
			if( ( $smsdeck_celular.val().length >= 10 ) && ( $smsdeck_celular.val().length <= 11 ) ){
				// sei lá porque isso nao estava funcionando
			}else{
				jQuery( ".notice" ).html( "ERRO: O telefone de origem informado está incompleto "+ $smsdeck_celular.val().length ).show().delay(3000).fadeOut(1000);
				$smsdeck_celular.focus();
				return false;
			}
		});
		
		/*
			Conta os caracteres pré validação
		*/
		$smsdeck_token.keyup( function(){
			conta_mensagem( "#smsdeck_token2", 160);
		});
		
		// apenas numeros
		$smsdeck_celular.keyup( function(e){
	        e.preventDefault();
	        var expre = /[^0-9]/g;
	        // REMOVE OS CARACTERES DA EXPRESSAO ACIMA
	        if (jQuery(this).val().match(expre))
	            jQuery(this).val(jQuery(this).val().replace(expre,''));
	            
			conta_mensagem( '#smsdeck_celular', 11);
		});
	});
	</script>
	<style>
	.contador{
		width: 300px;
		height: 5px;
		background-position: center center;
	}
	#mensagem {
		font-weight: bold;
	}
	
	/* Start by setting display:none to make this hidden.
	   Then we position it in relation to the viewport window
	   with position:fixed. Width, height, top and left speak
	   speak for themselves. Background we set to 80% white with
	   our animation centered, and no-repeating */
	.modal {
	    display:    none;
	    position:   fixed;
	    z-index:    1000;
	    top:        0;
	    left:       0;
	    height:     100%;
	    width:      100%;
	    background: rgba( 255, 255, 255, .8 ) 
	                url('<?php echo plugin_dir_url( __FILE__ ); ?>img/ajax-loader.gif') 
	                50% 50% 
	                no-repeat;
	}
	
	/* When the body has the loading class, we turn
	   the scrollbar off with overflow:hidden */
	body.loading {
	    overflow: hidden;   
	}
	
	/* Anytime the body has the loading class, our
	   modal element will be visible */
	body.loading .modal {
	    display: block;
	}
	
	#icon-smsdeck {
    	background: transparent url( <?php echo plugins_url( 'smsdeck-cliente/img/smsdeck-32x32.png' ); ?> ) no-repeat;
	}
	
	#wpcontent .status-online {
		background: #006600;
		/*color: #C4FFC4;*/
	}
	#wpcontent .status-offline {
		background: #CC0000;
		color: #FF7979;
	}
	</style>
	<?php
	}
	
	
	
	/**
	 * Cria um post_type 'virtual' 
	 * ele só ficará disponível caso 
	 * o SMSdeck Server não esteja 
	 * ativo
	 *
	 */
	public function post_type_sms(){
		
		if( is_admin() && !class_exists( "Smsdeck_server" ) ){
		
			register_post_type( 'torpedo_sms',
				array(
					'labels' => array(
						'name' => __( 'SMSs' ),
						'singular_name' => __( 'SMS' ), 
					),
					'public' => false,
					'rewrite' => true, 
					'exclude_from_search' => true, 
					'has_archive'	=> true, // define se o post_type tem uma página archive.php
					'hierarchical' => true, 
					'query_var' => true, 
					'supports' => array(
						'author', 
						'excerpt', 
						'custom-fields', 
					),
				)
			);
		}
	}
	
	

	// adiciona os menus
	public function smsdeck_menu(){
		
		// Apenas se estiver fora do servidor
		if( !class_exists( "Smsdeck_server" ) ){
			add_menu_page( 'SMSdeck', 'SMSdeck cliente', 'publish_posts', 'edit.php?post_type=torpedo_sms','', plugins_url( 'smsdeck-cliente/img/smsdeck.png' ), '2.4' );
		}
		
		add_submenu_page( 'edit.php?post_type=torpedo_sms', 'Enviar SMS', 'Enviar SMS', 'manage_options', 'smsdeck-enviar-sms', array( 'Smsdeck_Cliente', 'smsdeck_enviar_sms' ) );

		add_submenu_page( 
			'edit.php?post_type=torpedo_sms', 
			'Configurações', 
			'Configurações', 
			'publish_posts', 
			'smsdeck-configuracoes', 
			array( 'Smsdeck_Cliente', 'smsdeck_configuracoes' ) 
		);
		
		
	}
	
}



// cria uma requisição AJAX
add_action( 'admin_footer', array( 'Smsdeck_Cliente', 'smsdeck_javascript' ) );


//Adiciona a funcao smsdeck_ajax aos hooks ajax do WordPress.
add_action('wp_ajax_smsdeck_ajax', array( 'Smsdeck_Cliente', 'smsdeck_ajax' ) );

// cria um shortcode
//add_shortcode( 'smsdeck_formulario_sms', array( 'Smsdeck_Cliente', 'smsdeck_sms_formulario' ) );

// adiciona os links do menu
add_action('admin_menu', array( 'Smsdeck_Cliente', 'smsdeck_menu' ) );

add_action( 'init', array( 'Smsdeck_Cliente', 'post_type_sms' ) );