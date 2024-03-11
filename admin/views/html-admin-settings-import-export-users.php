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

    .uwp-ie-container #import-submit{
        display: none;
    }
    .uwp-ie-container .uwp-msg-wrap{
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
    .uwp-ie-report {
        max-height: 120px;
        overflow-y: scroll;
        width: auto;
        background-color: #fffbcc;
        padding: 0px 10px;
        margin: 30px 0 0 0;
    }

    .uwp-ie-report p {
        font-size: 12px;
        margin: 7px 0px;
        padding: 0;
    }
</style>
<?php
$uwp_chunk_sizes = array(
    50 => 50,
    100 => 100,
    200 => 200,
    500 => 500,
    1000 => 1000,
    2000 => 2000,
    5000 => 5000,
    10000 => 10000,
    20000 => 20000,
    50000 => 50000,
    100000 => 100000,
);

$uwp_chunk_sizes = apply_filters('uwp_ie_csv_chunks_options', $uwp_chunk_sizes);
$uwp_chunk_sizes_opts = '';
foreach ($uwp_chunk_sizes as $value => $title) {
    $uwp_chunk_sizes_opts .= '<option value="' . $value . '" ' . selected($value, 5000, false) . '>' . $title . '</option>';
}

$users_count = count_users();
$total_users = $users_count['total_users'];
?>
<div class="metabox-holder uwp-ie-container">
    <div class="postbox uwp-export-users">
        <h3><span><?php esc_html_e( 'Export Users Data', 'userswp' ); ?></span></h3>
        <div class="inside uwp-export-users-form">
            <p><?php esc_html_e( 'Download a CSV of all users data for usersWP.', 'invoicing' ); ?></p>
            <table class="form-table">
                <tbody>
                <tr>
                    <th class=""><label for="uwp_ie_chunk_size"><?php esc_html_e( 'Max entries per csv file:', 'userswp' );?></label></th>
                    <td><select name="uwp_ie_chunk_size" class="aui-select2" id="uwp_ie_chunk_size" data-ucount = "<?php echo esc_attr( $total_users );?>" style="min-width:140px"><?php echo esc_attr( $uwp_chunk_sizes_opts );?></select><p class="description"><?php esc_html_e( 'The maximum number of entries per csv file (default to 5000, you might want to lower this to prevent memory issues.)', 'userswp' );?></p></td>
                </tr>
                </tbody>
            </table>
            <div class="uwp-export-submit-wrap">
                <input type="hidden" name="uwp_ie_action" value="export_users" />
                <?php wp_nonce_field( 'uwp_export_users_nonce', 'uwp_export_users_nonce' ); ?>
                <?php submit_button( __( 'Export CSV', 'userswp' ), 'primary', 'export-submit', false ); ?>

            </div>
        </div>
    </div>

    <div class="postbox">
        <h3><span><?php esc_html_e( 'Import Users' ); ?></span></h3>
        <div class="inside" id="uwp-imp-container">
            <p><?php esc_html_e( 'Import the users data from a .csv file. This file can be obtained by exporting the data on another site using the form above.' ); ?></p>
                <p class="uwp-imp-uploaded-file">
                    <a id="uwp-imp-browse" class="button file-selector button-primary" href="#"><?php esc_html_e( 'Select File', 'userswp' ); ?></a>
                </p>
                <p>
                    <input type="hidden" value="" name="uwp_import_users_file" class="uwp_import_users_file">
                    <?php wp_nonce_field( 'uwp_import_users_nonce', 'uwp_import_users_nonce' ); ?>
                    <?php submit_button( __( 'Import', 'userswp' ), 'primary', 'import-submit', false ); ?>
                </p>
        </div>
    </div>
</div>

<script type="text/javascript">
    jQuery(function($) {
        var UWP_IE = {
            init: function() {
                var $self = this;
                this.submit();
                this.clearMessage();
                this.form = jQuery('form#mainform');
                this.form.attr('action', 'javascript:void(0);');
                jQuery("#save_style", this.el).on("click", function(e) {
                    $self.saveStyle(e);
                });
            },
            submit: function() {
                var $this = this;
                $('#export-submit').click(function(e) {
                    e.preventDefault();
                    var $btn = jQuery(this);
                    var $form = jQuery('form#mainform');
                    if (!$btn.attr('disabled')) {
                        var data = $form.serialize();
                        $btn.attr('disabled', true);
                        $btn.find('.uwp-msg-wrap').remove();
                        $btn.after('<div class="uwp-msg-wrap"><div class="uwp-progress"><div></div><span>0%</span></div><span class="uwp-export-loader"><i class="fas fa-spin fa-spinner"></i></span></div>');
                        // start the process
                        $this.step(1, data, $form, $this);
                    }
                });
                $('#import-submit').click(function(e) {
                    e.preventDefault();
                    var $btn = $(this);
                    var $form = jQuery('form#mainform');
                    $btn.hide();
                    if (!$btn.attr('disabled')) {
                        var data = $form.serialize();
                        $btn.attr('disabled', true);
                        $form.find('.uwp-msg-wrap').remove();
                        $btn.after('<div class="uwp-msg-wrap"><div class="uwp-progress"><div></div><span>0%</span></div><span class="uwp-import-loader"><i class="fas fa-spin fa-spinner"></i></span></div>');
                        $form.find('.uwp-msg-wrap').append('<div class="uwp-ie-report"></div>');
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
                                        message.append('<span class="uwp-export-file"><a href="' + res.data.file.u + '" target="_blank" download><i class="fas fa-download"></i> ' + res.data.file.u + '</a><span> - ' + res.data.file.s + '<span><span>');
                                    }
                                    message.find('.uwp-export-loader').html('<i class="fas fa-check-circle"></i>');
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
                                if (res.total.msg) {
                                    message.find('.uwp-ie-report').append('<p>' + res.total.msg + '</p>');
                                }
                                if ('done' == res.data.step || res.data.done >= 100) {
                                    $form.find('input[type="submit"]').removeAttr('disabled');
                                    $('.uwp-progress > span').text(parseInt(res.data.done) + '%');
                                    $('.uwp-progress div').animate({
                                        width: res.data.done + '%'
                                    }, 100, function() {});
                                    if (res.msg) {
                                        message.append('<div id="uwp-export-success" class="updated notice is-dismissible"><p>' + res.msg + '<span class="notice-dismiss"></span></p></div>');
                                    }
                                    message.find('.uwp-import-loader').html('<i class="fas fa-check-circle"></i>');
                                } else {
                                    var next = parseInt(res.data.step) > 0 ? parseInt(res.data.step) : 1;
                                    $('.uwp-progress > span').text(parseInt(res.data.done) + '%');
                                    $('.uwp-progress div').animate({
                                        width: res.data.done + '%'
                                    }, 100, function() {});
                                    if (res.data.msg) {
                                        message.find('.uwp-ie-report').append('<p>' + res.data.msg + '</p>');
                                    }
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
            this.removed_files = [];
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
                url: '<?php echo esc_url( admin_url( 'admin-ajax.php' ) . '?nonce=' . wp_create_nonce( 'uwp-ie-file-upload-nonce' ) ); ?>' + '&type=' + type,
                filters: [{
                    title: '<?php echo esc_js( __('Allowed Files', 'userswp') ); ?>',
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
                this.showHide();
                $('#' + this.container).prepend('<div class="uwp-file-warning"></div>');
            },

            showHide: function () {

                if ( this.count >= this.max) {

                    if ( this.count > this.max ) {
                        $('#' + this.container + ' .uwp-file-warning').addClass('error').html( '<?php echo esc_js( __( 'Maximum number of files reached!', 'userswp' ) ); ?>' );
                        $('#' + this.container).find('.file-selector').hide();

                        return;
                    }
                };
                $('#' + this.container + ' .uwp-file-warning').removeClass('error').html( '' );
                $('#' + this.container).find('.file-selector').show();
            },

            added: function (up, files) {
                var $container = $('#' + this.container).find('.uwp-imp-uploaded-file');

                this.showHide();
                $('form#mainform').find('.uwp-msg-wrap').remove();

                $.each(files, function(i, file) {
                    $container.append(
                        '<div class="upload-item uwp-msg-wrap" id="' + file.id + '"><div class="uwp-progress"><div class="bar"></div><span class="percent">0%</span></div><span class="uwp-import-loader"><i class="fas fa-spin fa-spinner"></i></span><span class="uwp-import-file filename original">' +
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
                        msg = '<?php esc_attr_e( 'The file you have uploaded exceeds the file size limit. Please try again.', 'userswp' ); ?>'
                        break;

                    case -601:
                        msg = '<?php esc_attr_e( 'Invalid file uploaded. Please upload .csv file. Please try again.', 'userswp' ); ?>'
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
                $('#' + file.id).find('.uwp-import-loader').html('<i class="fas fa-check-circle"></i>');

                if(response.response !== 'error') {
                    $('#' + this.container).find('.file-selector').hide();
                    $('form#mainform').find('#import-submit').show();
                    $('form#mainform').find('.uwp_import_users_file').val(response.response);

                } else {
                    console.log(response);
                    alert(response.response);

                    this.count -= 1;
                    this.showHide();
                }
            },

        };

        $(document).ready( function(){
            var uploader = new UWP_IE_Uploader('uwp-imp-browse', 'uwp-imp-container', 1, 'uwp_ie_import_file', 'csv', 2048);
        });
    });
</script>