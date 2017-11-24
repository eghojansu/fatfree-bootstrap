(function($) {
    $('[data-upload]').on('change', function() {
        var $this = $(this);
        var $progress = $('#upload-progress');
        var $progressBar = $progress.find('.progress-bar');
        var $form = $this.parents('form');
        var $group = $this.parents('.form-group');
        var $status = $group.find('.help-block');

        $form.ajaxSubmit({
            url: $this.data('upload'),
            type: 'POST',
            beforeSend: function() {
                var percentVal = '0%';
                $progress.fadeIn();
                $progressBar.width(percentVal);
                $group.removeClass('has-error has-success');
                $status.text('').fadeOut();
            },
            uploadProgress: function(event, position, total, percentComplete) {
                var percentVal = percentComplete + '%';
                $progress.width(percentVal);
            },
            success: function(data) {
                $progress.fadeOut();
                $progressBar.width('100%');
                $status.text(data.message).fadeIn();
                if (data.success) {
                    $group.addClass('has-success');
                    $form.find('button').prop('disabled', false);
                } else {
                    $group.addClass('has-error');
                }
            }
        });
    });
    $('[data-moment-now]').each(function() {
        var $this = $(this);
        var ts = $this.data('momentNow');
        if (ts) {
            setInterval(function() {
                $this.text(moment.unix(ts).fromNow());
            }, 1000);
        }
    });
    $('#user-online').each(function() {
        var updateStatus = function() {
            $.getJSON(app.path.online_user, function(data) {
                $('#user-online-user').text(data.user);
                $('#user-online-visitor').text(data.visitor);
            });
        };
        updateStatus();
        setInterval(updateStatus, 60000);
    });
    $('[data-toggle="tooltip"]').tooltip()
    $('#main-nav .dropdown .active').each(function() {
        $(this).parents('.dropdown').addClass('active');
    });
    $('[data-provide=dtp]').each(function() {
        var option = getPropertyWithPrefix($(this).data(), 'dtp');

        $(this).prop('autocomplete', 'off');
        $(this).datetimepicker(option);
    });
    $('[data-provide=wysiwyg]').each(function() {
        var $this = $(this);
        $this.summernote({
            height: 200,
            minHeight: 100,
            callbacks: {
                onImageUpload: function(files) {
                    data = new FormData();
                    data.append("file", files[0]);
                    $.ajax({
                        data: data,
                        type: "POST",
                        url: app.path.upload_asset,
                        cache: false,
                        contentType: false,
                        processData: false,
                        success: function(data) {
                            if (data.success) {
                                $this.summernote('insertImage', data.url);
                            } else {
                                alert(data.message);
                            }
                        }
                    });
                }
            }
        });
    });
    $('.form-filter').each(function() {
        var $that = $(this);
        var $closer = $that.find('[href="#filter-close"]');
        var $opener = $that.find('[href="#filter-open"]');
        var data = $that.serialize().split('&');
        var open = false;

        for (var i = data.length - 1; i >= 0; i--) {
            var datum = data[i].split('=');
            if (datum[1]) {
                open = true;
                break;
            }
        }

        $opener.on('click', function(event) {
            event.preventDefault();

            if ($opener.is(':visible')) {
                $opener.slideUp('fast', function() {
                    $that.find('.form-group').each(function() {
                        var dependencies = $(this).data('filterDependency');
                        var doit = true;

                        if (typeof dependencies !== 'undefined') {
                            for (var dependency in dependencies) {
                                if(!dependencies.hasOwnProperty(dependency)) {
                                    continue;
                                }

                                var test = new RegExp(dependencies[dependency]);
                                var $dependency = $(dependency);

                                if (!test.test($dependency.val())) {
                                    doit = false;
                                    break;
                                }
                            }
                        }
                        if (doit) {
                            $(this).slideDown();
                        }
                    });
                });
            }
        });
        $closer.on('click', function(event) {
            event.preventDefault();

            if ($closer.is(':visible')) {
                $that.find('.form-group').slideUp('slow', function() {
                    $opener.slideDown('fast');
                });
            }
        });
        $that.find(':input').each(function() {
            var $this = $(this);
            var thisValue = $this.val();
            var triggerRules = $(this).data('filterTrigger');
            var dtpTargets = $(this).data('filterDtp');

            var triggerAction = function(val) {
                for (var target in triggerRules) {
                    if(!triggerRules.hasOwnProperty(target)) {
                        continue;
                    }

                    var test = new RegExp(triggerRules[target]);
                    var $target = $that.find(target);
                    var $parent = $target.parents('.form-group');

                    if (test.test(val)) {
                        if ($parent.is(':hidden')) {
                            $parent.slideDown();
                        }
                    } else {
                        $parent.slideUp();
                    }
                }
            };

            var dtpAction = function(val) {
                var newDtpFormat;
                if (val == 'days' || val == 'periods') {
                    newDtpFormat = 'DD/MM/YYYY';
                } else if (val == 'months') {
                    newDtpFormat = 'MM/YYYY';
                } else if (val == 'years') {
                    newDtpFormat = 'YYYY';
                } else {
                    return;
                }

                $.each(dtpTargets.split('|'), function(k,v) {
                    var $target = $that.find(v);
                    var $dtp = $target.data('DateTimePicker');
                    var prevValue;

                    $target.prop('autocomplete', 'off');
                    if (typeof $dtp === 'undefined') {
                        prevValue = moment($target.val(), newDtpFormat);
                        $target.datetimepicker({
                            format: newDtpFormat
                        });
                        $dtp = $target.data('DateTimePicker');
                    } else {
                        prevValue = $dtp.date();
                        $dtp.format(newDtpFormat);
                    }

                    $dtp.date(prevValue);
                });
            };

            if (typeof triggerRules !== 'undefined') {
                triggerAction(thisValue);
            }
            if (dtpTargets) {
                dtpAction(thisValue);
            }

            $this.on('change', function() {
                var thisValue = $this.val();

                if (typeof triggerRules !== 'undefined') {
                    triggerAction(thisValue);
                }
                if (dtpTargets) {
                    dtpAction(thisValue);
                }
            });
        });

        if (open) {
            $opener.click();
        } else {
            $closer.click();
        }
    });
    $('[data-provide=print]').on('click', function(event) {
        event.preventDefault();

        var target = $(this).prop('href');
        if (target.length < 1) {
            target = $(this).data('target');
        }

        window.open(target, 'print-window', 'fullscreen=yes,menubar=no', false);
    });
    $('#user-stat').each(function() {
        var $this = $(this);
        var userChart;
        $.getJSON(app.path.statistic_user, function(data) {
            var chartData = {
                labels: [],
                datasets: [{
                    label: "User Login By Date",
                    data: [],
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    borderColor: 'rgba(255,99,132,1)'
                }]
            };
            for (var i = 0; i < data.length; i++) {
                chartData.labels.push(data[i].gdate);
                chartData.datasets[0].data.push(data[i].gcount);
            }
            userChart = Chart.Line($this, {
                data: chartData,
                options: {
                    responsive: true,
                    hoverMode: 'index',
                    stacked: false,
                    title:{
                        display: true,
                        text:'User History'
                    }
                }
            });
        });
    });

    function getPropertyWithPrefix(data, prefix) {
        var option = {};
        for (var key in data) {
            if(data.hasOwnProperty(key) && key.indexOf(prefix) > -1) {
                var newKey = key.substr(key.indexOf(prefix) + prefix.length).toLowerCase();
                option[newKey] = data[key];
            }
        }

        return option;
    }

    var tiktokLooper;
    var tiktokMoment;
    function tiktok() {
        var $container = $('.server-time');

        if ($container.length === 0 || $container.is(':hidden')) {
            clearInterval(tiktokLooper);

            return;
        }

        if (typeof tiktokMoment === 'undefined') {
            tiktokMoment = moment($container.text(), 'X').locale('id');
        }

        tiktokMoment.add(1, 's');
        $container.html(tiktokMoment.format('dddd, D MMMM YYYY HH:mm'));
    }

    tiktokLooper = setInterval(tiktok, 1000);
})(jQuery);
