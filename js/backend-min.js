jQuery(document).ready(function(f){f(".fvp_input").blur(function(){f(this).val(f.trim(f(this).val()));e=f(this).val();if((e.length===0)||(e==fvp_backend_data.default_value)||(e==fvp_backend_data.default_value_sec)){f(this).addClass("defaultTextActive");if(f(this).attr("id")=="fvp_video"){f(this).val(fvp_backend_data.default_value)}else{f(this).val(fvp_backend_data.default_value_sec)}}f(this).trigger("autosize")});f(".fvp_input").focus(function(){e=f(this).val();if((e==fvp_backend_data.default_value)||(e==fvp_backend_data.default_value_sec)){f(this).removeClass("defaultTextActive");f(this).val("")}});f(".fvp_input").autosize().trigger("blur").keypress(function(i){if(i.keyCode==13){i.preventDefault()}});f(".fvp_input").click(function(){f(this).select()});var e=f("#fvp_video").val();if(e.length===0||e==fvp_backend_data.default_value||!e.match(fvp_backend_data.wp_upload_dir.replace(/\//g,"\\/"))){f("#fvp_sec").val(fvp_backend_data.default_value_sec);f("#fvp_sec_wrapper").hide()}f("#fvp_video").bind("change paste keyup",function(){setTimeout(h(f(this)),200)});function h(l){var k=f.trim(l.val());var j=f.trim(f("#fvp_sec").val());f("#fvp_help_notice").show("fast");if(k.length===0||k==fvp_backend_data.default_value){f("#fvp_video").css("backgroundColor","white");f("#fvp_sec").val(fvp_backend_data.default_value_sec).blur();f("#fvp_sec_wrapper").hide("fast","linear");f("#fvp_localvideo_format_warning").hide("fast","linear")}if(k.match(fvp_backend_data.wp_upload_dir.replace(/\//g,"\\/"))){var m=/^.*\/(.*)\.(.*)$/g;var i=m.exec(k);if(i[2]=="webm"||i[2]=="mp4"||i[2]=="ogg"||i[2]=="ogv"){f("#fvp_sec_wrapper").show("fast","linear");f("#fvp_video").css("backgroundColor","white");f("#fvp_localvideo_format_warning").hide("fast","linear")}else{f("#fvp_sec").val(fvp_backend_data.default_value_sec).blur();f("#fvp_sec_wrapper").hide("fast","linear");f("#fvp_video").css("backgroundColor","lightYellow");f("#fvp_localvideo_format_warning").show("fast","linear")}d()}else{f("#fvp_sec_wrapper").hide("fast","linear");f("#fvp_video").css("backgroundColor","white");f("#fvp_localvideo_format_warning").hide("fast","linear")}}f("#fvp_sec").bind("change paste keyup",function(){setTimeout(a(f(this)),200)});function a(l){var k=f.trim(l.val());var i=f.trim(f("#fvp_video").val());if(k.length===0||k==fvp_backend_data.default_value){f("#fvp_localvideo_format_warning").hide("fast");f("#fvp_sec").css("backgroundColor","white")}if(k.match(fvp_backend_data.wp_upload_dir.replace(/\//g,"\\/"))){var m=/^.*\/(.*)\.(.*)$/g;var j=m.exec(k);if(j[2]=="webm"||j[2]=="mp4"||j[2]=="ogg"||j[2]=="ogv"){d();f("#fvp_sec").css("backgroundColor","white");f("#fvp_localvideo_format_warning").hide("fast")}else{d();f("#fvp_sec").css("backgroundColor","lightYellow");f("#fvp_localvideo_format_warning").show("fast")}}else{if(k.length!==0){f("#fvp_sec").css("backgroundColor","lightYellow");f("#fvp_localvideo_notdistinct_warning").show("fast")}}}function d(){if(f("#fvp_video").val()==f("#fvp_sec").val()){f("#fvp_sec").css("backgroundColor","lightYellow");f("#fvp_localvideo_notdistinct_warning").show("fast")}else{f("#fvp_localvideo_notdistinct_warning").hide("fast");f("#fvp_sec").css("backgroundColor","white")}}f("#fvp_set_featimg_link").show();f("#fvp_set_featimg_input").hide();f("#fvp_set_featimg_link, #fvp_warning_set_featimg").click(function(){f("#fvp_set_featimg").attr("checked",true);f("#fvp_set_featimg").closest("form").submit();return false});f("#remove-post-thumbnail").click(function(){f("#fvp_featimg_box_warning").removeClass("fvp_hidden")});f("#set-post-thumbnail").click(function(){f("#fvp_featimg_box_warning").addClass("fvp_hidden")});f("#fvp_help_toggle").bind("click",function(){f("#contextual-help-link").trigger("click")});var c,g,b;b={frame:function(){if(this._frame){return this._frame}this._frame=wp.media({title:c.data("title"),library:{type:"video"},button:{text:c.data("button")},multiple:false});this._frame.on("open",this.updateFrame).state("library").on("select",this.select);return this._frame},select:function(){var j=this.get("selection"),i="url";f(c.data("target")).val(j.pluck(i)).trigger("autosize").change().removeClass("defaultTextActive")},updateFrame:function(){},init:function(){f("#wpbody").on("click",".fvp_video_choose",function(i){i.preventDefault();c=f(this).closest(".fvp_input_wrapper");b.frame().open()})}};if(fvp_backend_data.wp_35==1){b.init()}});
