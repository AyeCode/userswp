(function($) {

    if(!window.ayecodeds)
        window.ayecodeds = {};

    if(ayecodeds.DeactivateFeedbackForm)
        return;

    ayecodeds.DeactivateFeedbackForm = function(plugin)
    {
        var self = this;
        var strings = ayecodeds_deactivate_feedback_form_strings;
        var support_btn = plugin.support_url ? '<a class="button button-primary" href="'+plugin.support_url+'" target="_blank" >'+strings.get_support+'</a>' : '';
        var documentation = plugin.documentation_url ? ' <a class="button button-primary" href="'+plugin.documentation_url+'" target="_blank" >'+strings.documentation+'</a>' : '';
        this.plugin = plugin;

        // Dialog HTML
        var element = $('\
			<div class="ayecodeds-deactivate-dialog" data-remodal-id="' + plugin.slug + '">\
				<form>\
					<input type="hidden" name="plugin"/>\
					<h2>' + strings.quick_feedback + '<span id="ayecodeds-plugin-name"></span></h2>\
					<p>\
						' + strings.foreword + '\
					</p>\
					<ul class="ayecodeds-deactivate-reasons"></ul>\
					<input id="ayecode-feedback-other" name="comments" placeholder="' + strings.brief_description + '" style="width: 100%;padding: 5px;display: none;"/>\
					<p class="ayecodeds-help-buttons" style="float: left;display: none;">\
					'+support_btn+documentation+'\
					</p> \
					<p class="ayecodeds-deactivate-dialog-buttons" style="float: right;">\
						<input type="submit" class="button confirm" value="' + strings.skip_and_deactivate + '"/>\
						<button data-remodal-action="cancel" class="button button-primary" onclick="tb_remove();jQuery(\'#ayecode-deactivation-form\').html(\'\');return false;">' + strings.cancel + '</button>\
					</p>\
				</form><style>#TB_window {overflow-y:auto;border-radius: 4px;height: fit-content !important;width:auto !important; left: 50%;top: 50% !important;margin-left: unset !important; -webkit-transform: translate(-50%, -50%);transform: translate(-50%, -50%);}#TB_title{display:none;}#TB_ajaxContent {height: auto !important;}</style>\
			</div>\
		')[0];
        this.element = element;

        var pluginNiceName = $("#the-list [data-slug='" + plugin.slug + "'] .plugin-title strong").text();

        $(element).find("input[name='plugin']").val(JSON.stringify(plugin));

        $(element).find("#ayecodeds-plugin-name").text(" - "+pluginNiceName);
        //

        $(element).on("click", "input[name='reason']", function(event) {
            $(element).find("input[type='submit']").val(
                strings.submit_and_deactivate
            );

            //hide then we can show if needed
            jQuery("#ayecode-feedback-other,.ayecodeds-help-buttons").hide();
            if(jQuery(this).val()=="other"){
                jQuery("#ayecode-feedback-other").attr('placeholder',strings.brief_description ).show();
            }else if(jQuery(this).val()=="found-better-plugin"){
                jQuery("#ayecode-feedback-other").attr('placeholder',strings.better_plugins_name ).show();
            }else if(jQuery(this).val()=="suddenly-stopped-working" || jQuery(this).val()=="plugin-broke-site" || jQuery(this).val()=="plugin-setup-difficult" || jQuery(this).val()=="plugin-design-difficult"){
                jQuery(".ayecodeds-help-buttons").show();
            }

        });

        $(element).find("form").on("submit", function(event) {
            self.onSubmit(event);
        });

        // Reasons list
        var ul = $(element).find("ul.ayecodeds-deactivate-reasons");
        for(var key in plugin.reasons)
        {
            var li = $("<li><input type='radio' name='reason'/> <span></span></li>");

            $(li).find("input").val(key);
            $(li).find("span").html(plugin.reasons[key]);
            $(ul).append(li);
        }

        // Listen for deactivate
        $("#the-list [data-slug='" + plugin.slug + "'] .deactivate>a").on("click", function(event) {
            self.onDeactivateClicked(event);
        });
    };

    ayecodeds.DeactivateFeedbackForm.prototype.onDeactivateClicked = function(event)
    {

        var strings = ayecodeds_deactivate_feedback_form_strings;
        this.deactivateURL = event.target.href;

        event.preventDefault();

        if(!$('#ayecode-deactivation-form').length){
            $( "#wpfooter" ).after( "<div id='ayecode-deactivation-form' style='display: none;'></div>" );
        }

        $('#ayecode-deactivation-form').html(this.element);

        tb_show(strings.quick_feedback,'#TB_inline?height=auto&inlineId=ayecode-deactivation-form');

    };

    ayecodeds.DeactivateFeedbackForm.prototype.onSubmit = function(event)
    {
        var element = this.element;
        var strings = ayecodeds_deactivate_feedback_form_strings;
        var self = this;
        var data = $(element).find("form").serialize();

        $(element).find("button, input[type='submit']").prop("disabled", true);

        if($(element).find("input[name='reason']:checked").length)
        {
            $(element).find("input[type='submit']").val(strings.thank_you);

            $.ajax({
                type:		"POST",
                url:		"https://wpgeodirectory.com/tracking/",
                data:		data,
                complete:	function() {
                    window.location.href = self.deactivateURL;
                }
            });
        }
        else
        {
            $(element).find("input[type='submit']").val(strings.please_wait);
            window.location.href = self.deactivateURL;
        }

        event.preventDefault();
        return false;
    };

    $(document).ready(function() {

        for(var i = 0; i < ayecodeds_deactivate_feedback_form_plugins.length; i++)
        {
            var plugin = ayecodeds_deactivate_feedback_form_plugins[i];
            new ayecodeds.DeactivateFeedbackForm(plugin);
            // console.log(plugin);
        }

    });

})(jQuery);