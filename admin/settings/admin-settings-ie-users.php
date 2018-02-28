<?php
wp_enqueue_script( 'plupload' );
?>
<style type="text/css">
    .uwp-export-submit-wrap [type="submit"] {
        vertical-align: top;
        line-height: normal;
    }
    .uwp-ie-container .uwp-progress {
        width: calc(100% - 50px);
        height: 16px;
        background-color: #ddd;
        float: left;
        position: relative;
    }
    .uwp-ie-container .uwp-progress div {
        background-color: orange;
        height: 100%;
        width: 0;
        display: block
    }
    .uwp-ie-container .uwp-progress > span {
        position: absolute;
        height: 100%;
        width: 100%;
        display: block;
        font-size: 13px;
        line-height: 14px;
        text-align: center;
        vertical-align: top;
        top: 0;
    }
    .uwp-ie-container .uwp-msg-wrap {
        padding: 12px;
        background-color: #f4f4f4;
        border-style: solid;
        border-width: 1px 0;
        border-color: #eae9e9;
        overflow: auto;
        margin: 20px -12px -23px;
        position: relative;
    }

    .uwp-ie-container .uwp-import-users-form .uwp-msg-wrap{
        margin: 20px 0;
    }
    .uwp-ie-container .uwp-msg-wrap .uwp-export-loader, .uwp-ie-container .uwp-msg-wrap .uwp-import-loader {
        display: inline-block;
        float: right;
        margin: 0;
        vertical-align: middle;
        width: 40px;
        height: 16px;
        text-align: center;
    }
    .uwp-ie-container .uwp-export-loader .fa, .uwp-ie-container .uwp-import-loader .fa {
        font-size: 17px;
        color: orange;
    }
    .uwp-ie-container .uwp-export-loader .fa-spin, .uwp-ie-container .uwp-import-loader .fa-spin {
        color: #000;
    }
    .uwp-msg-wrap .updated {
        margin: 0 0 2px 0 !important;
    }
    .uwp-export-file, .uwp-import-file {
        display: block;
        padding-top: 7px;
        clear: both;
        display: block;
    }
    .uwp-export-file a, .uwp-import-file a {
        text-decoration: none;
    }
</style>
<div class="metabox-holder uwp-ie-container">
    <div class="postbox uwp-export-users">
        <h3><span><?php _e( 'Export Users Data', 'userswp' ); ?></span></h3>
        <div class="inside">
            <p><?php _e( 'Download a CSV of all users data for usersWP.', 'invoicing' ); ?></p>
            <form id="uwp-export-users" class="uwp-export-users-form" method="post">
                <div class="uwp-export-submit-wrap">
                    <input type="hidden" name="uwp_ie_action" value="export_users" />
                    <?php wp_nonce_field( 'uwp_export_users_nonce', 'uwp_export_users_nonce' ); ?>
                    <?php submit_button( __( 'Export CSV' ), 'primary', 'submit', false ); ?>
                </div>
            </form>
        </div>
    </div>

    <div class="postbox">
        <h3><span><?php _e( 'Import Users' ); ?></span></h3>
        <div class="inside" id="uwp-imp-container">
            <p><?php _e( 'Import the users data from a .csv file. This file can be obtained by exporting the data on another site using the form above.' ); ?></p>
            <form method="post" enctype="multipart/form-data" class="uwp-import-users-form">
                <p class="uwp-imp-uploaded-file">
                    <a id="uwp-imp-browse" class="button file-selector button-primary" href="#"><?php _e( 'Select File', 'userswp' ); ?></a>
                </p>
                <p>
                    <input type="hidden" value="" name="uwp_import_users_file" class="uwp_import_users_file">
                    <?php wp_nonce_field( 'uwp_import_users_nonce', 'uwp_import_users_nonce' ); ?>
                    <?php submit_button( __( 'Import', 'userswp' ), 'primary', 'submit', false ); ?>
                </p>
            </form>
        </div>
    </div>
</div>


<script type="text/javascript">
    jQuery(function($) {
        var UWP_IE = {
            init: function() {
                this.submit();
                this.clearMessage();
            },
            submit: function() {
                var $this = this;
                $('.uwp-export-users-form').submit(function(e) {
                    e.preventDefault();
                    var $form = $(this);
                    var submitBtn = $form.find('input[type="submit"]');
                    if (!submitBtn.attr('disabled')) {
                        var data = $form.serialize();
                        submitBtn.attr('disabled', true);
                        $form.find('.uwp-msg-wrap').remove();
                        $form.append('<div class="uwp-msg-wrap"><div class="uwp-progress"><div></div><span>0%</span></div><span class="uwp-export-loader"><i class="fa fa-spin fa-spinner"></i></span></div>');
                        // start the process
                        $this.step(1, data, $form, $this);
                    }
                });
                $('.uwp-import-users-form').submit(function(e) {
                    e.preventDefault();
                    var $form = $(this);
                    var submitBtn = $form.find('input[type="submit"]');
                    submitBtn.hide();
                    if (!submitBtn.attr('disabled')) {
                        var data = $form.serialize();
                        submitBtn.attr('disabled', true);
                        $form.find('.uwp-msg-wrap').remove();
                        $form.append('<div class="uwp-msg-wrap"><div class="uwp-progress"><div></div><span>0%</span></div><span class="uwp-import-loader"><i class="fa fa-spin fa-spinner"></i></span></div>');
                        // start the process
                        $this.imp_step(1, data, $form, $this);
                    }
                });
            },
            step: function(step, data, $form, $this) {
                var message = $form.find('.uwp-msg-wrap');
                var post_data = {
                    action: 'uwp_ajax_export_users',
                    step: step,
                    data: data,
                };
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    cache: false,
                    dataType: 'json',
                    data: post_data,
                    beforeSend: function(jqXHR, settings) {},
                    success: function(res) {
                        if (res && typeof res == 'object') {
                            if (res.success) {
                                if ('done' == res.data.step || res.data.done >= 100) {
                                    $form.find('input[type="submit"]').removeAttr('disabled');
                                    $('.uwp-progress > span').text(parseInt(res.data.done) + '%');
                                    $('.uwp-progress div').animate({
                                        width: res.data.done + '%'
                                    }, 100, function() {});
                                    if (res.msg) {
                                        message.html('<div id="uwp-export-success" class="updated notice is-dismissible"><p>' + msg + '<span class="notice-dismiss"></span></p></div>');
                                    }
                                    if (res.data.file && res.data.file.u) {
                                        message.append('<span class="uwp-export-file"><a href="' + res.data.file.u + '" target="_blank"><i class="fa fa-download"></i> ' + res.data.file.u + '</a><span> - ' + res.data.file.s + '<span><span>');
                                    }
                                    message.find('.uwp-export-loader').html('<i class="fa fa-check-circle"></i>');
                                } else {
                                    var next = parseInt(res.data.step) > 0 ? parseInt(res.data.step) : 1;
                                    $('.uwp-progress > span').text(parseInt(res.data.done) + '%');
                                    $('.uwp-progress div').animate({
                                        width: res.data.done + '%'
                                    }, 100, function() {});
                                    $this.step(parseInt(next), data, $form, $this);
                                }
                            } else {
                                $form.find('input[type="submit"]').removeAttr('disabled');
                                if (res.msg) {
                                    message.html('<div class="updated error"><p>' + res.msg + '</p></div>');
                                }
                            }
                        } else {
                            $form.find('input[type="submit"]').removeAttr('disabled');
                            message.html('<div class="updated error">' + res + '</div>');
                        }
                    }
                }).fail(function(res) {
                    if (window.console && window.console.log) {
                        console.log(res);
                    }
                });
            },
            imp_step: function(step, data, $form, $this) {
                var message = $form.find('.uwp-msg-wrap');
                var post_data = {
                    action: 'uwp_ajax_import_users',
                    step: step,
                    data: data,
                };
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    cache: false,
                    dataType: 'json',
                    data: post_data,
                    beforeSend: function(jqXHR, settings) {},
                    success: function(res) {
                        if (res && typeof res == 'object') {
                            if (res.success) {
                                if ('done' == res.data.step || res.data.done >= 100) {
                                    $form.find('input[type="submit"]').removeAttr('disabled');
                                    $('.uwp-progress > span').text(parseInt(res.data.done) + '%');
                                    $('.uwp-progress div').animate({
                                        width: res.data.done + '%'
                                    }, 100, function() {});
                                    if (res.msg) {
                                        message.html('<div id="uwp-export-success" class="updated notice is-dismissible"><p>' + msg + '<span class="notice-dismiss"></span></p></div>');
                                    }
                                    message.find('.uwp-import-loader').html('<i class="fa fa-check-circle"></i>');
                                } else {
                                    var next = parseInt(res.data.step) > 0 ? parseInt(res.data.step) : 1;
                                    $('.uwp-progress > span').text(parseInt(res.data.done) + '%');
                                    $('.uwp-progress div').animate({
                                        width: res.data.done + '%'
                                    }, 100, function() {});
                                    $this.imp_step(parseInt(next), data, $form, $this);
                                }
                            } else {
                                $form.find('input[type="submit"]').removeAttr('disabled');
                                if (res.msg) {
                                    message.html('<div class="updated error"><p>' + res.msg + '</p></div>');
                                }
                            }
                        } else {
                            $form.find('input[type="submit"]').removeAttr('disabled');
                            message.html('<div class="updated error">' + res + '</div>');
                        }
                    }
                }).fail(function(res) {
                    if (window.console && window.console.log) {
                        console.log(res);
                    }
                });
            },
            clearMessage: function() {
                $('body').on('click', '#uwp-export-success .notice-dismiss', function() {
                    $(this).closest('#uwp-export-success').parent().slideUp('fast');
                });
                $('body').on('click', '#uwp-import-success .notice-dismiss', function() {
                    $(this).closest('#uwp-import-success').parent().slideUp('fast');
                });
            }
        };
        UWP_IE.init();

        window.UWP_IE_Uploader = function (browse_button, container, max, type, allowed_type, max_file_size) {
            this.removed_files = [],
                this.container = container;
            this.browse_button = browse_button;
            this.max = max || 1;
            this.count = $('#' + container).find('.uwp-attachment-list > li').length; //count how many items are there
            this.perFileCount = 0;
            if( !$('#'+browse_button).length ) {
                return;
            }

            this.uploader = new plupload.Uploader({
                runtimes: 'html5,html4',
                browse_button: browse_button,
                container: container,
                multipart: true,
                multipart_params: {
                    action: 'uwp_ie_upload_file',
                },
                max_file_count : 1,
                urlstream_upload: true,
                file_data_name: 'import_file',
                max_file_size: max_file_size + 'kb',
                url: '<?php echo admin_url( 'admin-ajax.php' ) . '?nonce=' . wp_create_nonce( 'uwp-ie-file-upload-nonce' ); ?>' + '&type=' + type,
                filters: [{
                    title: '<?php _e('Allowed Files', 'userswp'); ?>',
                    extensions: allowed_type
                }]
            });

            //attach event handlers
            this.uploader.bind('Init', $.proxy(this, 'init'));
            this.uploader.bind('FilesAdded', $.proxy(this, 'added'));
            this.uploader.bind('QueueChanged', $.proxy(this, 'upload'));
            this.uploader.bind('UploadProgress', $.proxy(this, 'progress'));
            this.uploader.bind('Error', $.proxy(this, 'error'));
            this.uploader.bind('FileUploaded', $.proxy(this, 'uploaded'));

            this.uploader.init();

            return this.uploader;
        };

        UWP_IE_Uploader.prototype = {

            init: function (up, params) {
                $('.uwp-import-users-form').find('.uwp-msg-wrap').remove();
                this.showHide();
                $('#' + this.container).prepend('<div class="uwp-file-warning"></div>');
            },

            showHide: function () {

                if ( this.count >= this.max) {

                    if ( this.count > this.max ) {
                        $('#' + this.container + ' .uwp-file-warning').html( '<?php _e( 'Maximum number of files reached!', 'userswp' ); ?>' );
                        $('#' + this.container).find('.file-selector').hide();

                        return;
                    }
                };
                $('#' + this.container + ' .uwp-file-warning').html( '' );
                $('#' + this.container).find('.file-selector').show();
            },

            added: function (up, files) {
                var $container = $('#' + this.container).find('.uwp-imp-uploaded-file');

                this.showHide();

                $.each(files, function(i, file) {
                    $container.append(
                        '<div class="upload-item uwp-msg-wrap" id="' + file.id + '"><div class="uwp-progress"><div class="bar"></div><span class="percent">0%</span></div><span class="uwp-import-loader"><i class="fa fa-spin fa-spinner"></i></span><span class="uwp-import-file filename original">' +
                        file.name + '<span> - ' + plupload.formatSize(file.size) + '</span></span>' +
                        '</div></div>');
                });

                up.refresh();
                up.start();
            },

            upload: function (uploader) {

                this.count = uploader.files.length - this.removed_files.length ;
                this.showHide();

            },

            progress: function (up, file) {
                var item = $('#' + file.id);

                $('.bar', item).css({ width: file.percent + '%' });
                $('.percent', item).html( file.percent + '%' );
            },

            error: function (up, error) {
                $('#' + this.container).find('#' + error.file.id).remove();

                var msg = '';
                switch (error.code) {
                    case -600:
                        msg = '<?php _e( 'The file you have uploaded exceeds the file size limit. Please try again.', 'userswp' ); ?>'
                        break;

                    case -601:
                        msg = '<?php _e( 'Invalid file uploaded. Please upload .csv file. Please try again.', 'userswp' ); ?>'
                        break;

                    default:
                        msg = 'Error #' + error.code + ': ' + error.message;
                        break;
                }

                alert(msg);

                this.count -= 1;
                this.showHide();
                this.uploader.refresh();
            },

            uploaded: function (up, file, response) {
                var self = this;

                $('#' + file.id + " .percent").html("100%");
                $('#' + file.id).find('.uwp-import-loader').html('<i class="fa fa-check-circle"></i>');

                if(response.response !== 'error') {

                    $('.uwp-import-users-form').find('input[type="submit"]').show();
                    $('.uwp-import-users-form').find('.uwp_import_users_file').val(response.response);

                } else {
                    alert(response.error);

                    this.count -= 1;
                    this.showHide();
                }
            },

        };

        $(document).ready( function(){
            $('.uwp-import-users-form').find('input[type="submit"]').hide();
            var uploader = new UWP_IE_Uploader('uwp-imp-browse', 'uwp-imp-container', 1, 'uwp_ie_import_file', 'csv', 2048);
        });
    });
</script>