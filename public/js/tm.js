(function(){
    console.log('Task Manager APP');
    app = function() {
        console.log('App run!');

        projectEdit =  function(e){
            e.preventDefault();
            $.get($(this).attr('data-url'), function (response) {
                console.log(response);
                var modal = $('#modalWindow');
                $(modal).find('.modal-content').first().html(response.data);
                $('#btn-project-save').on('click', saveProject);
                modal.modal('show');
            })
        };

        saveProject = function(e) {
            e.preventDefault();
            var me = this, form = $(me).closest('.modal-content').find('form').first();
            var data = $(form).serialize();
            console.log(data);

            $.ajax({
                method: $(form).attr('method'),
                url: $(form).attr('action'),
                data: data
            }).done(function (response) {
                console.log(response);
                if (response.success === true) {
                    var modal = $('#modalWindow');
                    modal.modal('hide');
                }

                if (response.action === 'edit') {
                    $('#title-project-' + response.data.id + ' .project-icon').removeClass().addClass('project-icon icon-title icon-left ' + response.data.icon);
                    $('#title-project-' + response.data.id + ' .project-name').html(response.data.name);
                }

                if (response.action === 'create') {
                    $('#projects-list').append(response.html);
                    $('button[id^="btn-project-edit"]').on('click', projectEdit);
                    $('button[id^="btn-project-confirm-delete"]').on('click', confirmDeleteProject);
                    $('button[id^="btn-task-new"]').on('click', newTask);
                }
            });
        };

        confirmDeleteProject = function (e) {
            e.preventDefault();
            $.get($(this).attr('data-url'), function (response) {
                console.log(response);
                var modal = $('#modalWindow');
                $(modal).find('.modal-content').first().html(response.html);
                $('#btn-project-delete').on('click', deleteProject);
                modal.modal('show');
            });
        };

        deleteProject = function (e) {
            e.preventDefault();
            var me = this, form = $(me).closest('.modal-content').find('form').first();
            var data = $(form).serialize();
            console.log(data);

            $.ajax({
                method: $(form).attr('method'),
                url: $(form).attr('action'),
                data: data
            }).done(function (response) {
                console.log(response);
                if (response.success === true) {
                    var modal = $('#modalWindow');
                    modal.modal('hide');
                }

                if ( 'delete' === response.action) {
                    $('#project-'+response.data.id).remove();
                }
            });
        };

        newTask = function(e){
            e.preventDefault();
            //console.log(this, a,b,c,d);
            var me = this, form = me.closest('form');

            $(form).validate({
                rules: {
                    'task[name]': {
                        required: true,
                        minlength: 2
                    }
                },
                errorElement: 'div',
                // the errorPlacement has to take the table layout into account
                errorPlacement: function(error, element) {
                    console.log('errorPlacement error, element', error, element);

                    if (element.is(":radio"))
                        error.appendTo(element.parent().next().next());
                    else if (element.is(":checkbox"))
                        error.appendTo(element.next());
                    else
                        error.appendTo(element.parent());
                },
                // specifying a submitHandler prevents the default submit, good for the demo
                submitHandler: function() {
                    alert("submitted!");
                },
                // set this class to error-labels to indicate valid fields
                success: function(label) {
                    // set &nbsp; as text for IE
                    console.log('success label', label);
                    label.html("&nbsp;").addClass("checked");
                },
                highlight: function(element, errorClass) {
                    console.log('highlight element, errorClass', element, errorClass);
                    $(element).parent().next().find("." + errorClass).removeClass("checked");
                }
            });
            if ($(form).valid()) {
                var data = $(form).serialize();
                console.log(data);

                $.ajax({
                    method: $(form).attr('method'),
                    url: $(form).attr('action'),
                    data: data
                }).done(function (response) {
                    console.log(response);
                    if (response.success === true) {
                        var taskList = $(me).closest('.project').find('.task-list').first();
                        if ($(taskList).find('.task-list-empty').length) {
                            $(taskList).html(response.html);
                        } else {
                            $(taskList).append(response.html);
                        }

                        $(form).find('input[type=text], textarea').val('');
                        $('button[id^="btn-task-edit"]').on('click', editTask);
                        $('button[id^="btn-task-confirm-delete"]').on('click', confirmDeleteTask);
                        //Need Add handler for button
                    }

                });
            }

        };

        editTask = function(e) {
            e.preventDefault();
            $.get($(this).attr('data-url'), function (response) {
                console.log(response);
                var modal = $('#modalWindow');
                $(modal).find('.modal-content').first().html(response.html);
                $('#btn-task-save').on('click', saveTask);
                modal.modal('show');
            });
        };

        saveTask = function(e) {
            e.preventDefault();
            var me = this, form = $(me).closest('.modal-content').find('form').first();
            var data = $(form).serialize();
            console.log(data);

            $.ajax({
                method: $(form).attr('method'),
                url: $(form).attr('action'),
                data: data
            }).done(function (response) {
                console.log(response);
                if (true === response.success) {
                    var modal = $('#modalWindow');
                    modal.modal('hide');
                }

                if ('update' === response.action) {
                    var projectId = response.data.projectId;
                    var project = $('#project-'+projectId);
                    var task = $(project).find('#task-row-'+response.data.id);
                    if (task.length > 0) {
                        $('#task-row-'+response.data.id).replaceWith(response.html);
                    } else {
                        var taskRow = $('#task-row-'+response.data.id);
                        var oldTaskList = $(taskRow).closest('.task-list');
                        $(taskRow).remove();
                        if (0 === $(oldTaskList).find('tr').length) {
                            $(oldTaskList).append('<tr class="task-list-empty"><td colspan="3">no records found</td></tr>');
                        }

                        var taskList = $(project).find('.task-list');
                        if ($(taskList).find('.task-list-empty').length) {
                            $(taskList).html(response.html);
                        } else {
                            $(taskList).append(response.html);
                        }
                    }

                    $('button[id^="btn-task-edit"]').on('click', editTask);
                    $('button[id^="btn-task-edit"]').on('click', confirmDeleteTask);
                }
            });
        };

        confirmDeleteTask = function(e) {
            e.preventDefault();
            $.get($(this).attr('data-url'), function (response) {
                console.log(response);
                var modal = $('#modalWindow');
                $(modal).find('.modal-content').first().html(response.html);
                $('#btn-task-delete').on('click', deleteTask);
                modal.modal('show');
            });
        };

        deleteTask = function(e) {
            e.preventDefault();
            var me = this, form = $(me).closest('.modal-content').find('form').first();
            var data = $(form).serialize();
            console.log(data);

            $.ajax({
                method: $(form).attr('method'),
                url: $(form).attr('action'),
                data: data
            }).done(function (response) {
                console.log(response);
                if (response.success === true) {
                    var modal = $('#modalWindow');
                    modal.modal('hide');
                }

                if ( 'delete' === response.action) {
                    var taskRow = $('#task-row-'+response.data.id);
                    var taskList = $(taskRow).closest('.task-list');
                    $(taskRow).remove();
                    if (0 === $(taskList).find('tr').length) {
                        $(taskList).append('<tr class="task-list-empty"><td colspan="3">no records found</td></tr>');
                    }
                }
            });
        };

        $('#btn-project-new').on('click', function (e) {
            e.preventDefault();
            $.get($(this).attr('data-url'), function (response) {
                console.log(response);
                var modal = $('#modalWindow');
                $(modal).find('.modal-content').first().html(response.data);
                $('#btn-project-save').on('click', saveProject);
                modal.modal('show');
            })

        });

        $('button[id^="btn-project-edit"]').on('click', projectEdit);
        $('button[id^="btn-project-confirm-delete"]').on('click', confirmDeleteProject);
        $('button[id^="btn-task-new"]').on('click', newTask);

        $('button[id^="btn-task-edit"]').on('click', editTask);
        $('button[id^="btn-task-confirm-delete"]').on('click', confirmDeleteTask);
    };

    $(document).ready(function() {
        app();
    });




})();


