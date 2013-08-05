/**
 * Fileman Javascript Library
 * -----
 * jQuery dependent.
 * Yes, I know of $.
 * Yes, I know of noConflict().
 * -----
 * @author Frenck Lutke <http://frencklutke.nl/>
 */

jQuery(document).ready(function() {
	
	//***********************
	// Hidden Field Switcher
	//***********************
	
	//click function toggles unhide class on all relevant elements
	jQuery('.tx-fileman .rel-switch').click(function() {
		jQuery(this).next('.tx-fileman .rel-links').slideToggle();
		return false;
	});
	
	//by default all is unhidden (in case of no js-support), force once to set initial state hidden
	jQuery('.tx-fileman .rel-switch').show();
	jQuery('.tx-fileman .rel-links').hide();	
	
	
	
	//*********************
	// Upload progress bar 
	//*********************
	//TODO: doc
	//TODO: SHOULD BE ABLE TO SET THE UPLOAD PROGRESS BAR TYPE FROM TS
	//note that it is a string, so that the numbers get concatenated
	var upload_id_gen = '' + new Date().getTime() + Math.random();
	upload_id_gen = upload_id_gen.replace('.','');
	var updateProgressInt = {};
	var apcFieldName = "###APC_FIELD_NAME###";
	var sendingFileText = "###SENDING_FILE###";
	
	jQuery('.tx-fileman .init-progressbar').each(function(i, form) {
		var upload_id = i + upload_id_gen;
		jQuery(form).prepend('<input type="hidden" name="'+apcFieldName+'" value="' + upload_id + '" />');
		jQuery(form).after('<div id="fileman-uploadProgress'+i+'" class="uploadprogress"><div class="progressbar"></div><div class="progressvalue"></div></div>');
		
		jQuery(form).on('submit', function() {
			//only if fileupload is not empty
			var fileuploadValue = jQuery(this).find('.fileupload').val();
			if (fileuploadValue != '') {
				jQuery(this).hide();
				jQuery('.tx-fileman #fileman-uploadProgress'+i).show();
				
				updateProgressInt[i] = setInterval(function() {
					updateProgress(upload_id,i);
				}, 500); //TODO: should be configurable
				updateProgress(upload_id,i);
			}
		});
	});
	
	//TODO: url should be set from TS, no cache should not be necessary once headers in script are set
	function updateProgress(id,i) {
		jQuery.get('/typo3conf/ext/fileman/Resources/Public/Scripts/UploadProgress.php', {upload_id: id, no_cache: Math.random()}, function(data) {
			//TODO: catch script errors
			var uploaded = parseInt(data);
			jQuery('.tx-fileman #fileman-uploadProgress'+i+' .progressbar').css({
				'width': uploaded + '%'
			});
			if (uploaded == 100) {
				clearInterval(updateProgressInt[i]);
				jQuery('.tx-fileman #fileman-uploadProgress'+i+' .progressvalue').text(sendingFileText+' 99%');
			} else {
				jQuery('.tx-fileman #fileman-uploadProgress'+i+' .progressvalue').text(sendingFileText+' '+uploaded+'%');
			}
		});
	};
	
	
	
	//**********************
	// Multi-file Upload UI
	//**********************
	
	var fileCountMax = "###MAX_FILE_UPLOADS###";
	var addFileText = "###ADD_FILE###";
	var delFileText = "###DEL_FILE###";
	var showOptionalText = "###SHOW_OPTIONAL###";
	
	if (fileCountMax > 1) {
		jQuery('.tx-fileman .multi-file').each(function(i, form) {
			var fileCount = jQuery(form).find('.single-file').size();
			jQuery(form).find('.submit').before('<a href="#" class="add-single-file" title="'+addFileText+'">'+addFileText+'</a><a href="#" class="del-single-file" title="'+delFileText+'">'+delFileText+'</a>');
			var addFileLink = jQuery(form).find('.add-single-file');
			var delFileLink = addFileLink.next('.del-single-file');
			
			if (fileCount == fileCountMax) addFileLink.addClass('disabled');
			if (fileCount == 1) delFileLink.addClass('disabled');
			
			jQuery(form).find('.optional').addClass('indent');
			jQuery(form).find('.optional').each(function(i, optional) {
				var singleFile = jQuery(optional).parent('.single-file'); 
				singleFile.find('.fileupload').after('<a href="#" class="show-optional" title="'+showOptionalText+'">'+showOptionalText+'</a>');
				singleFile.find('.show-optional').click(function() {
					jQuery(optional).slideToggle();
					jQuery(this).toggleClass('expanded');
					return false;
				});
				jQuery(optional).hide();
			});
			
			jQuery(form).find('a.add-single-file').click(function() {
				if (fileCount < fileCountMax && !jQuery(this).hasClass('disabled')) {
					if (fileCount == 1) delFileLink.removeClass('disabled');
					
					var singleFile = jQuery(this).prevAll('.single-file:first');
					var clone = singleFile.clone();
					var findName = '[file][i' + fileCount + ']';
					var replaceName = '[file][i' + (++fileCount) + ']';
					jQuery(clone).find('input[type=file],input[type=text],textarea').each(function(i, elem) {
						jQuery(elem).attr('name', jQuery(elem).attr('name').replace(findName,replaceName));
						jQuery(elem).val(null); //input values are copied with the clone..
					});
					jQuery(clone).find('.show-optional').click(function() {
						jQuery(clone).find('.optional').slideToggle();
						jQuery(this).toggleClass('expanded');
						return false;
					});
					jQuery(this).before(clone);
					
					if (fileCount == fileCountMax) addFileLink.addClass('disabled');
				}
				return false;
			});
			
			jQuery(form).find('a.del-single-file').click(function() {
				if (fileCount > 1  && !jQuery(this).hasClass('disabled')) {
					if (fileCount == fileCountMax) addFileLink.removeClass('disabled');
					
					jQuery(this).prevAll('.single-file:first').remove();
					fileCount--;
					
					if (fileCount == 1) delFileLink.addClass('disabled');
				}
				return false;
			});
		
		});
	}
	
});