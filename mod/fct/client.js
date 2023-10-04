// Quadern virtual d'FCT
//
// Copyright © 2008,2009,2010  Institut Obert de Catalunya
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.

$('.quadern_quinzena_edit_form').ready(function() {

    var nom_mes = ['gener','febrer', 'març', 'abril',
                   'maig', 'juny', 'juliol', 'agost',
                   'setembre', 'octubre', 'novembre', 'desembre'];

    var nom_dia = ['dl', 'dt', 'dc', 'dj', 'dv', 'ds', 'dg'];

    var actualitzar_dies = function() {
        var dies = [];
        $('#calendari_dies :checkbox').each(function () {
            if (this.checked) {
                dies.push($(this).val());
            }
        });
        dies.sort();
        $dies.val(dies.join(', '));
    };

    var dies_periode = function(any, periode) {
        if (periode % 2 == 0) {
            return [1, 15];
        } else {
            var mes = mes_periode(periode);
        return [16, 32 - new Date(any, mes, 32).getDate()];
        }
    };

    var mes_periode = function(periode) {
        return Math.floor(periode / 2);
    };

    var calendari = function(any, periode, dies, congelat) {
        var mes = mes_periode(periode);
        var rang_dies = dies_periode(any, periode);
        var dia_setmana = new Date(any, mes, rang_dies[0]).getDay();
        var espais = (dia_setmana + 6) % 7;
        var cells = [];
        var dia, i;

        $('#calendari_dies').remove();

        dies = dies.split(',');
        for (i = 0; i < dies.length; i++) {
            dies[i] = parseInt(dies[i]);
        }
        for (i = 0; i < nom_dia.length; i++) {
            cells.push('<th>' + nom_dia[i] + '</th>');
        }
        for (i = 0; i < espais; i++) {
            cells.push('<td></td>');
        }
        for (dia = rang_dies[0]; dia <= rang_dies[1]; dia++) {
            cells.push('<td>' + dia + '<input type="checkbox" value="' + dia + '"'
                       + ($.inArray(dia, dies) >= 0 ? ' checked="checked"' : '')
                       + (congelat ? ' disabled="disabled"' : '') + '/></td>');
        }
        while (cells.length % 7 != 0) {
            cells.push('<td></td>');
        }

        var $table = $('<table id="calendari_dies"></table>');
        for (i = 0; i < cells.length; i += 7) {
            $table.append('<tr>' + cells.slice(i, i + 7).join('') + '</tr>');
        }

        return $table;
    };

    var nom_periode = function(any, periode) {
        var dies = dies_periode(any, periode);
        var mes = mes_periode(periode);
        return dies[0] + "-" + dies[1] + " " + nom_mes[mes];
    };

    var any_inici = parseInt($('input[name=any_inici]').val());
    var any_final = parseInt($('input[name=any_final]').val());
    var periode_inici = parseInt($('input[name=periode_inici]').val());
    var periode_final = parseInt($('input[name=periode_final]').val());

    var $any = $('#id_any');
    var $periode = $('#id_periode');
    var $dies = $('#id_dies');

    $any.change(function () {
        var periode_min = ($any.val() == any_inici ? periode_inici : 0);
        var periode_max = ($any.val() == any_final ? periode_final : 23);
        var seleccionat = $periode.val();
        $periode.empty();
        for (var periode = periode_min; periode <= periode_max; periode++) {
            $periode.append('<option value="' + periode + '"'
                            + (periode == seleccionat ?
                               ' selected="selected"' : '') + '>'
                            + nom_periode($any.val(), periode) + '</option>');
        }
        $periode.change();
    });

    $periode.change(function() {
        var $calendari = calendari($any.val(), $periode.val(),
                                   $dies.val(), false);
        $dies.hide().after($calendari);
        $('#calendari_dies :checkbox').change(actualitzar_dies);
        actualitzar_dies();
    });

    $any.change();

    $('input[name=dies_quinzena]').each(function() {
        var any = $('input[name=any_quinzena]').val();
        var periode = $('input[name=periode_quinzena]').val();
        var $calendari = calendari(any, periode, $(this).val(), true);
        $('#quinzena .felement').eq(2).empty().append($calendari);
    });

    $('#activitats_realitzades input:disabled').not(':checked')
        .closest('.fitem').hide();

    $('.frases_areatext').each(function (index, div) {
        $(div).find('li')
            .click(function () {
                var id = $(div).attr('id').replace('_frases', '');
                var $textarea = $('#' + id);
                $textarea
                    .val($textarea.val() + $(this).text())
                    .animate({ scrollTop: $textarea[0].scrollHeight })
                    .focus();
                $(div).find('h4').click();
            });
        $(div).find('h4')
            .click(function () {
                $(this).find('img').toggle();
                $(div).find('ul').slideToggle();
            });
        $(div).show();
    });

});

$('.quadern_search_form').ready(function() {
    $('#id_searchcurs, #id_searchcicle, #id_searchestat, #id_cerca').on('change',function() {
        $(this).closest("form").submit();
    });
});
