function getScript(url, success) {
    var script = document.createElement('script');
    script.src = url;
    var head = document.getElementsByTagName('head')[0],
            done = false;
    // Attach handlers for all browsers
    script.onload = script.onreadystatechange = function() {
        if (!done && (!this.readyState
                || this.readyState == 'loaded'
                || this.readyState == 'complete')) {
            done = true;
            success();
            script.onload = script.onreadystatechange = null;
            head.removeChild(script);
        }
    };
    head.appendChild(script);
}
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

