$(document).ready(function(){
    // Вешаем событие на кнопку загрузки списка категорий
    $('#button-load-categories').click(function() {
        var filter = $('#filterName').val();
        var level = $('#nestingLevel').val();
        ajaxLoadCategories(filter, level);
    });

    // Событие при активации модального окна удаления
    $('#removeModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget); // Button that triggered the modal
        var id = button.data('id'); // Extract info from data-* attributes
        var modal = $(this);
        modal.find('.modal-body').text( $('#d-'+id+' span').first().text() );
        modal.find('.modal-footer .btn-danger').attr('id-category',id);
    });

    // Событие при активации модального окна редактирования
    $('#editModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget); // Button that triggered the modal
        var id = button.data('id'); // Extract info from data-* attributes
        var modal = $(this);
        modal.find('#editCatName').val( $('#d-'+id+' span').first().text() );
        modal.find('.modal-footer .btn-primary').attr('id-category',id);
    });

    // Нажата кнопка редактирования категории
    $('#editModal .btn-primary').click(function(event) {
        var start_server = Date.now();
        var id = event.target.getAttribute('id-category');

        $('#editModal').modal('hide');

        // редактируем категорию в базе
        $.post('php/ajax.php', {
                action: 'edit-category',
                id: id,
                name: $('#editCatName').val()
            },
            function (data) {
                if(data.errmsg !== undefined) {
                    alert('Error: ' + data.errmsg);
                    return;
                }
                var start_client = Date.now();
                $('#speed-server-sql').text(data.time + ' ms');
                $('#speed-server-resp').text((Date.now()-start_server) + ' ms');
                if (data.affected !== undefined) {
                    console.log(data.affected + ' rows updated.');
                    if(data.affected > 0) {
                        // переименовываем каталог на клиенте
                        $('#d-' + id + ' span').first().html(data.name);
                        $('#speed-client').text((Date.now()-start_client) + ' ms');
                    }
                }
            }
        );
    });

    // Нажата кнопка удаления категории
    $('#removeModal .btn-danger').click(function(event) {
        var start_server = Date.now();
        var id = event.target.getAttribute('id-category');

        $('#removeModal').modal('hide');

        // удаляем ветку в базе
        $.post('php/ajax.php', {
                action: 'remove-category',
                id: id
            },
            function (data) {
                if(data.errmsg !== undefined) {
                    alert('Error: ' + data.errmsg);
                    return;
                }
                var start_client = Date.now();
                $('#speed-server-sql').text(data.time + ' ms');
                $('#speed-server-resp').text((Date.now()-start_server) + ' ms');
                if (data.affected !== undefined) {
                    console.log(data.affected + ' rows was removed.');
                    // Если что-то удалили то удаляем элемент из DOM
                    if(data.affected > 0) {
                        var loaded = parseInt($('#total').text(), 10) - data.affected;
                        $('#total').text(loaded);
                        // удаляем ветку на клиенте
                        $('#d-' + id).slideUp('normal', function () {
                            $('#d-' + id).remove();
                            $('#speed-client').text((Date.now()-start_client) + ' ms (с учетом анимации)');
                        });
                    }
                }
            }
        );
    });

    ajaxLoadCategories('', 0);
});

function ajaxLoadCategories(filter, level) {
    $('#dir-place').html('Loading... <br>Подождите пожалуйста !<br>Загружается дерево категорий');

    var action_name = 'get-all-categories';
    if(filter.length) action_name = 'get-filtered-categories';

    var start_server = Date.now();
    $.post('php/ajax.php', {
            action: action_name,
            filt: filter,
            deep: level
        },
        function (data) {
            if(data.errmsg !== undefined) {
                alert('Error: ' + data.errmsg);
                return;
            }
            $('#speed-server-sql').text(data.time + ' ms');
            $('#speed-server-resp').text((Date.now()-start_server) + ' ms');
            var start_client = Date.now();
            var count = data.dirlist.length;
            var b_edit, b_remove;
            /*
             data[i][0] => id каталога
             data[i][1] => id родительского каталога
             data[i][2] => Имя каталога
             data[i][3] => deep (глубина)
             */
            $('#dir-place').html('<ul id="dir-list"></ul>');
            var dirList = $('#dir-list'); // directory list item
           //  var rootFlag = 0;
            console.time('Init tree');
            for (var i=0; i<count; i++){
                var id   = data['dirlist'][i][0];
                var pid  = data['dirlist'][i][1];
                var name = data['dirlist'][i][2];
                var deep = data['dirlist'][i][3];

                b_edit   = '<a data-toggle="modal" data-target="#editModal" data-id="'+id+'" href="#" title="edit" class="glyphicon glyphicon-pencil" aria-hidden="true"></a> ';
                b_remove = '<a data-toggle="modal" data-target="#removeModal" data-id="'+id+'" href="#" title="remove" class="glyphicon glyphicon-remove" aria-hidden="true"></a>';

                var li = '<li id="d-'+id+'"><span>'+name+'</span> ('+deep+') ';
                li += b_edit + b_remove + '</li>';

                // Выводим 1й уровень дерева
                if (deep == 1) {
                    dirList.append(li);
                }
                // Выводим все остальные уровни
                else {
                    var dpi = $('#d-'+pid+' ul'); // directory parent item
                    if( $('#d-'+pid+' ul').length == 0 ) {
                        $('#d-'+pid).append('<ul></ul>');
                        $('#d-'+pid+' span').addClass('has-child');
                    }
                    $('#d-'+pid+' ul').append(li);
                }
            }

            dirList.treed(); // Init tree view
            console.timeEnd('Init tree');
            $('#total').text(data.dirlist.length); // Вывод кол-ва загруженных категорий
            $('#speed-client').text((Date.now()-start_client) + ' ms');
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