define(['jquery', 'core/config', 'core/notification'], function($, Config, Notification) {
    return {
        init: function() {
            if ($('body').hasClass('format-cre') && $('body').hasClass('cre-mod-form')) {
                $('body').removeClass('jsenabled');
            }
            if ($('body').hasClass('cre-index')) {
                var nodes = $('#page-content ol li');
                var span;
                var transport = $('.course-content-footer .cre-transport-index');
                var path = $('.course-content-footer .cre-path-index');
                $.each(nodes, function(key, node) {
                    span = $("<span>",
                        {
                            "class": "cre-index-text",
                            "html": $(node).text()
                        });
                    $(node).html('').append(span);
                });
                if (transport.length > 0) {
                    $('body').addClass('transport-animated');
                    var node = $('#page-content ol li:last-child');
                    if (node.length) {
                        node.append(transport);
                    }
                }
                if (path.length > 0) {
                    var node = $('#page-content ol li:nth-last-child(2)');
                    if (node.length) {
                        node.append(path);
                    } else {
                        path.hide();
                    }
                }
            }
            if ($('body').hasClass('format-cre') && $('body').hasClass('path-mod') && $('.pauta-revisio').length > 0) {
                var self = this;
                var nodes = $('.format-cre .pauta-revisio li');
                var html = '';
                var input = $("<input/>",
                        {
                            "type": "checkbox",
                            "name": "cre-pauta-revisio",
                            "aria-hidden": "true",
                            "value": ""
                        });
                var checkbox = $("<span/>",
                        {
                            "class": "cre-checkbox"
                        });
                var label = $("<label/>");
                var newlabel;
                $.each(nodes, function ( index, node ) {
                    html = $(node).text();
                    newlabel = label.clone();
                    $(node).empty().append(newlabel.html(html));
                    $(newlabel).prepend(checkbox.clone()).prepend(input.clone().val(index));
                });

                // Return saved tasks from DB
                var params = {
                    action: 'gettasks',
                    courseid: $('#theme-cre-params input[name="courseid"]').val(),
                    cmid: $('#theme-cre-params input[name="cmid"]').val(),
                    sesskey: Config.sesskey
                };
                this.callAjax(params);

                // Click on a checkbox
                $(document).on('click', '.format-cre .pauta-revisio input', function() {
                    params = {
                        action: $(this).is(':checked') ? 'taskdone' : 'tasktodo',
                        courseid: $('#theme-cre-params input[name="courseid"]').val(),
                        cmid: $('#theme-cre-params input[name="cmid"]').val(),
                        task: parseInt($(this).val()),
                        sesskey: Config.sesskey
                    };
                    self.callAjax(params);
                });
            }
            if ($('body').hasClass('cre-module')
                && ($('body').attr('id') == 'page-mod-forum-discuss'
                    || $('body').attr('id') == 'page-mod-forum-post'
                    || $('body').attr('id') == 'page-mod-forum-view')) {
                var $nodes = $('.options .commands a');
                var type;
                if ($nodes.length) {
                    $.each($nodes, function ( index, node ) {
                        type = $(node).attr('href');
                        if (type.search(/discuss\.php/i) > 0) {
                            $(node).addClass('forum-link').attr('title', $(node).text()).attr('aria-label', $(node).text());
                        } else if (type.search(/\?edit/i) > 0) {
                            $(node).addClass('forum-edit').attr('title', $(node).text()).attr('aria-label', $(node).text());
                        } else if (type.search(/\?prune/i) > 0) {
                            $(node).addClass('forum-prune').attr('title', $(node).text()).attr('aria-label', $(node).text());
                        } else if (type.search(/\?delete/i) > 0) {
                            $(node).addClass('forum-delete').attr('title', $(node).text()).attr('aria-label', $(node).text());
                        } else if (type.search(/\?reply/i) > 0) {
                            $(node).addClass('forum-reply').attr('title', $(node).text()).attr('aria-label', $(node).text());
                        } else if (type.search(/portfolio/i) > 0) {
                            $(node).attr('title', $(node).text()).attr('aria-label', $(node).text());
                        }  else if (type.search(/\?fav/i) > 0) {
                            $(node).addClass('forum-fav').attr('title', $(node).text()).attr('aria-label', $(node).text());
                        } else if (type.search(/\?unfav/i) > 0) {
                            $(node).addClass('forum-unfav').attr('title', $(node).text()).attr('aria-label', $(node).text());
                        }
                    });
                }
            }
        },
        callAjax: function(data) {
                var settings = {
                    context: this,
                    type: 'POST',
                    dataType: 'json',
                    data: data
                };

                var script = Config.wwwroot + '/theme/cre/ajax.php';
                $.ajax(script, settings)
                    .then(function(response) {
                        if (response.error) {
                            Notification.addNotification({
                                message: response.error,
                                type: "error"
                            });
                        } else if (response.values) {
                            this.setCheckboxes(response.values);
                        }
                        return;
                    })
                    .fail(Notification.exception);
        },
        setCheckboxes: function(data) {
            if (data) {
                var nodes = $('.format-cre .pauta-revisio li input');
                $.each(data, function (index, value) {
                    nodes.eq(value).attr('checked', 'checked');
                });
            }
        }
    };
});
