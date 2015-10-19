$(document).ready(function(){
    // Вешаем событие на кнопку загрузки списка категорий
    $('#button-load-categories').click(function() {
        ajaxLoadCategories();
    });

    // Активация модального окна удаления
    $('#removeModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget); // Button that triggered the modal
        var id = button.data('id'); // Extract info from data-* attributes
        var modal = $(this);
        modal.find('.modal-body').text( $('#d-'+id+' span').first().text() );
        modal.find('.modal-footer .btn-danger').attr('id-category',id);
    });

    // Нажата кнопка удаления категории
    $('#removeModal .btn-danger').click(function(event) {
        var id = event.target.getAttribute('id-category');
        // удаляем ветку на клиенте
        var elem = '#d-'+id;
        $('#removeModal').modal('hide');
        $(elem).slideUp('normal', function() {
            $(elem).remove();
        });
        // удаляем ветку в базе
        $.post('/euroauto/ajax.php', {
                data: 'remove-category',
                id: id
            },
            function (data) {
                if (data.deleted) alert('deleted = '+ data.deleted);
                else if(data.error) alert('error = '+ data.error);
            }
        );
    });

    ajaxLoadCategories();
});

function ajaxLoadCategories() {
    $.post('/euroauto/ajax.php', {
            data: 'get-all-categories'
        },
        function (data) {
            console.time('Tree render time: ');
            var count = data.length;
            var b_edit, b_remove;
            /*
             data[i][0] => id каталога
             data[i][1] => id родительского каталога
             data[i][2] => Имя каталога
             data[i][3] => deep (глубина)
             */
            $('#dir-place').html('<ul id="dir-list"></ul>');

            for (var i=0; i<count; i++){
                var id   = data[i][0];
                var pid  = data[i][1];
                var name = data[i][2];
                var deep = data[i][3];

                b_edit   = '<a data-toggle="modal" data-target="#removeModal" data-id="'+id+'" href="#" title="edit" class="glyphicon glyphicon-pencil" aria-hidden="true"></a> ';
                b_remove = '<a data-toggle="modal" data-target="#removeModal" data-id="'+id+'" href="#" title="remove" class="glyphicon glyphicon-remove" aria-hidden="true"></a>';


                var li = '<li id="d-'+id+'"><span>'+name+'</span> ('+deep+') ';
                li += b_edit + b_remove + '</li>';

                // Выводим 1й уровень дерева
                if (deep == 1) {
                    $('#dir-list').append(li);
                }
                // Выводим все остальные уровни
                else {
                    if( $('#d-'+pid+' ul').length == 0 ) {
                        $('#d-'+pid).append('<ul></ul>');
                        $('#d-'+pid+' span').addClass('has-child');
                    }
                    $('#d-'+pid+' ul').append(li);
                }
            }
            $('#dir-list').treed(); // Init tree view
            $('#total').text(data.length); // Вывод кол-ва загруженных категорий
            console.timeEnd('Tree render time: ');
        }
    );
}



$.fn.extend({
    treed: function () {
        var openedClass = 'glyphicon-chevron-right';
        var closedClass = 'glyphicon-chevron-down';

        //initialize each of the top levels
        var tree = $(this);
        tree.addClass("tree");
        tree.find('li').has("ul").each(function () {
            var branch = $(this); //li with children ul
            branch.prepend("<i class='indicator glyphicon " + closedClass + "'></i>");
            branch.addClass('branch');
            branch.on('click', function (e) {
                if (this == e.target) {
                    var icon = $(this).children('i:first');
                    icon.toggleClass(openedClass + " " + closedClass);
                    $(this).children().children().toggle();
                }
            });
            branch.children().children().toggle();
        });
        //fire event from the dynamically added icon
        tree.find('.branch .indicator').each(function(){
            $(this).on('click', function () {
                $(this).closest('li').click();
            });
        });
        //fire event to open branch if the li span
        tree.find('.branch>span').each(function () {
            $(this).on('click', function (e) {
                $(this).closest('li').click();
                e.preventDefault();
            });
        });
    }
});