<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Faqcosis
 * @author     Clayton Alves Rodrigues <clayton.rodrigues@tesouro.gov.br>
 * @copyright  © 2016 STN/COSIS. Todos os direitos reservados.
 * @license    GNU General Public License versão 2 ou posterior; consulte o arquivo License. txt
 */
// No direct access
defined('_JEXEC') or die;
JHtml::_('jquery.framework');
$doc = JFactory::getDocument();
//$doc->addScript(JUri::base() . '/media/com_faqcosis/js/form.js');
//$doc->addStyleSheet(JUri::base() . '/media/com_faqcosis/css/list.css')

?>


<form action="<?php echo JRoute::_('index.php?option=com_perguntasfrequente&view=perguntasfrequentes'); ?>" method="post"
      name="adminForm" id="adminForm">

    <?php echo JLayoutHelper::render('default_filter', array('view' => $this), dirname(__FILE__)); ?>
    <br />
    <?php
    $arDados = array();
    if( count($this->items) ){
        foreach ($this->items as $chave => $valor){
            $arDados[$valor->categoria][] = $valor;
        }
    }
    ?>
    <?php

        $html = '';
        if ( count( $arDados ) ){
            $html .= '<div id="conteudo_faq">';
            foreach ($arDados as $categoria => $arPerguntas){
                $html .= '<br /><div class="categoria" style="margin-left: 15px; margin-bottom: 10px; color: #0F4098; font-weight: bold; font-size: 16px;">';
                    $html .= '<span>'.$categoria.'</span>';
                $html .= '</div>';
                foreach ($arPerguntas as $i => $item){
                    
                    $html .= '<div class="titulo" style="background: #84B0ED; color:#000; padding: 5px 5px 5px 15px; margin: 2px; cursor:pointer; font-weight: bold;" item="'.$item->id.'">';
                        $html .= $item->title;
                    $html .= '</div>';
                    $html .= '<div fechado="1" style="display: none; margin-left: 2px; margin-right: 2px; padding-right: 10px; padding-left: 10px; border-left: 1px #84B0ED solid; border-right: 1px #84B0ED solid; border-bottom: 1px #84B0ED solid;" class="conteudo" id="conteudo_'.$item->id.'">';
                        $html .= $item->text;
                    $html .= '</div>';    
                    
                }
            }
        } 

        // Caso tenha pesquisa os itens devem ficar aberto
        if( isset($_POST['filter']['search']) && !empty($_POST['filter']['search']) ){
            $termoPesquisa = $_POST['filter']['search'];
            
            $html = preg_replace("~{$termoPesquisa}~", "<span class='highlight'>{$termoPesquisa}</span>", $html);
            
            // Termo caixa alta
            $termoUpper = strtoupper($termoPesquisa);
            $html = preg_replace("~{$termoUpper}~", "<span class='highlight'>{$termoUpper}</span>", $html);
            
            // Termo caixa baixa
            $termoLower = strtolower($termoPesquisa);
            $html = preg_replace("~{$termoLower}~", "<span class='highlight'>{$termoLower}</span>", $html);
            
            // Termo com a primeira letra maiuscula
            $termoUcfirst = ucfirst($termoPesquisa);
            $html = preg_replace("~{$termoUcfirst}~", "<span class='highlight'>{$termoUcfirst}</span>", $html);
            
        }
        
        echo $html;
        
        ?>
                    

    <input type="hidden" name="task" value=""/>
    <input type="hidden" name="boxchecked" value="0"/>
    <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
    <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
    <?php echo JHtml::_('form.token'); ?>
</form>

<script type="text/javascript">

    jQuery(document).ready(function () {
        jQuery('.titulo').click(function () {
            
            jQuery('.conteudo').hide();
            var item = jQuery(this).attr('item');
            var fechado = jQuery('#conteudo_'+item).attr('fechado');
            
            if(fechado == 1){
                jQuery('#conteudo_'+item).toggle('slow',function () {
                    jQuery(this).attr({fechado:"0"});
                });                
            }
            else{
                jQuery('#conteudo_'+item).hide('slow',function () {
                    jQuery(this).attr({fechado:"1"});
                });                                
            }
        });
    });
</script>
<?php
// Caso tenha pesquisa os itens devem ficar aberto
if( isset($_POST['filter']['search']) && !empty($_POST['filter']['search']) ):
    $termoPesquisa = $_POST['filter']['search'];
?>
<script type="text/javascript">

    jQuery(document).ready(function () {
        jQuery('.conteudo').show();
    });
</script>
<?php else: ?>
<script type="text/javascript">

    jQuery(document).ready(function () {
        jQuery('.conteudo').hide();
    });
</script>

<?php endif; ?>
