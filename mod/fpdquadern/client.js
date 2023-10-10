function mod_fpdquadern_init(Y, sesskey, id, alumne_id) {
    $('.mod-fpdquadern-activitat-descripcio').hide();
    $('.mod-fpdquadern-activitat-mostrar')
        .show()
        .on('click',  function() {
            var id = $(this).attr('data-mod-fpdquadern-activitat');
            $(this).removeAttr('href').fadeTo(null, 0);
            $('#mod-fpdquadern-activitat-descripcio-' + id).fadeIn();
            return false;
        });
    $('.mod-fpdquadern-dia-validar').on('click', function() {
        var dia_id = $(this).attr('data-mod-fpdquadern-dia');
        var url = M.cfg.wwwroot + '/mod/fpdquadern/ajax.php';
        var data = {
            accio: "validar_seguiment",
            id: id,
            sesskey: sesskey,
            alumne_id: alumne_id,
            dia_id: dia_id
        };

        $(this).hide();

        $.post(url, data, function(data) {
            if (data.error) {
                alert(data.error);
            } else {
                $('#mod-fpdquadern-dia-validat-' + dia_id).fadeIn();
            }
        });

        return false;
    });
}
