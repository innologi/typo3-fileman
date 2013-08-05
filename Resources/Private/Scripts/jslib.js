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
	
	//TODO: autofill the template title field through javascript?
	
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

	//TODO: SHOULD BE ABLE TO SET THE UPLOAD PROGRESS BAR TYPE FROM TS
	//note that it is a string, so that the numbers get concatenated
	var upload_id_gen = '' + new Date().getTime() + Math.random();
	upload_id_gen = upload_id_gen.replace('.','');
	//contains interval return values for use by clearInterval
	var updateProgressInt = {};
	var apcFieldName = "###APC_FIELD_NAME###";
	var sendingFileText = "###SENDING_FILE###";
	
	jQuery('.tx-fileman .init-progressbar').each(function(i, form) {
		var upload_id = i + upload_id_gen;
		jQuery(form).prepend('<input type="hidden" name="'+apcFieldName+'" value="' + upload_id + '" />');
		jQuery(form).after('<div id="fileman-uploadProgress'+i+'" class="uploadprogress"><div class="progressbar"></div><div class="progressvalue"></div></div>');
		
		jQuery(form).on('submit', function() {
			//only show the progressbar if fileupload is not empty
			var fileuploadValue = jQuery(this).find('.fileupload').val(); //FIXME: input[type=file]
			if (fileuploadValue != '') {
				jQuery(this).hide();
				jQuery('.tx-fileman #fileman-uploadProgress'+i).show();
				
				updateProgressInt[i] = setInterval(function() {
					updateProgress(upload_id,i);
				}, 500); //TODO: should be configurable
				updateProgress(upload_id,i); //the interval runs only AFTER its interval, so we run it at the start here 
			}
		});
	});
	
	//TODO: url should be set from TS, no cache should not be necessary once headers in script are set
	//updates progress in progress bar
	function updateProgress(id,i) {
		jQuery.get('/typo3conf/ext/fileman/Resources/Public/Scripts/UploadProgress.php', {upload_id: id, no_cache: Math.random()}, function(data) {
			//TODO: catch script errors
			var uploaded = parseInt(data);
			jQuery('.tx-fileman #fileman-uploadProgress'+i+' .progressbar').css({
				'width': uploaded + '%'
			});
			if (uploaded == 100) {
				clearInterval(updateProgressInt[i]);
				//because the request won't be really "done" at the same time the file is received, we display 99% to indicate a still unfinished state
				//this way, the user never sees 100% and is (hopefully) not tempted to do something that breaks the process too early
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
	var delFileText = "###DEL_FILE###"; //TODO: can't we make a delete button for every file entry?
	var showOptionalText = "###SHOW_OPTIONAL###";
	
	//don't enable it unless there are more files allowed than 1
	if (fileCountMax > 1) {
		jQuery('.tx-fileman .multi-file').each(function(i, form) {
			var fileCount = jQuery(form).find('.file-entry').size(); //there could be more files initially already, due to validation errors
			//add buttons
			jQuery(form).find('.submit').before('<a href="#" class="add-file-entry" title="'+addFileText+'">'+addFileText+'</a><a href="#" class="del-file-entry" title="'+delFileText+'">'+delFileText+'</a>');
			var addFileLink = jQuery(form).find('.add-file-entry');
			var delFileLink = addFileLink.next('.del-file-entry');
			
			//set initial state to disabled where it applies
			if (fileCount == fileCountMax) addFileLink.addClass('disabled'); //no adds possible if @ max
			if (fileCount == 1) delFileLink.addClass('disabled'); //no dels possible if @ min
			
			//the multi-file UI can get crowded, so we hide the optional fields under a button
			jQuery(form).find('.optional').addClass('indent'); //indent gives some special styling that should only be visible if multi-file UI is in effect
			//each, because there could already be multiple file-uploads
			jQuery(form).find('.optional').each(function(i, optional) {
				var fileEntry = jQuery(optional).parent('.file-entry');
				//add show-optional button
				fileEntry.find('.fileupload').after('<a href="#" class="show-optional" title="'+showOptionalText+'">'+showOptionalText+'</a>');
				fileEntry.find('.show-optional').click(function() {
					jQuery(optional).slideToggle();
					jQuery(this).toggleClass('expanded'); //this class helps to style any indication of an expanded view on the button
					return false;
				});
				jQuery(optional).hide();
			});
			
			//when an add link is clicked:
			jQuery(form).find('a.add-file-entry').click(function() {
				if (fileCount < fileCountMax && !jQuery(this).hasClass('disabled')) { //only works if not disabled
					if (fileCount == 1) delFileLink.removeClass('disabled'); //if we were @ min, we can enable the del link again
					//clone the last file-entry
					var fileEntry = jQuery(this).prevAll('.file-entry:first'); 
					var clone = fileEntry.clone();
					//replace its index in the clone
					var findName = '[file][i' + fileCount + ']';
					var replaceName = '[file][i' + (++fileCount) + ']';
					//empty field values!
					jQuery(clone).find('input[type=file],input[type=text],textarea').each(function(i, elem) {
						jQuery(elem).attr('name', jQuery(elem).attr('name').replace(findName,replaceName));
						jQuery(elem).val(null); //input values are copied with the clone..
					});
					//because clone() doesn't copy events, and clone(true) makes events retain their original targets, we have to assign functionality to certain buttons explicitly
					jQuery(clone).find('.show-optional').click(function() {
						jQuery(clone).find('.optional').slideToggle();
						jQuery(this).toggleClass('expanded');
						return false;
					});
					jQuery(this).before(clone); //place it before the button
					
					if (fileCount == fileCountMax) addFileLink.addClass('disabled'); //if we are @ max now, we need to disable add link
				}
				return false;
			});
			
			//when a delete link is clicked:
			jQuery(form).find('a.del-file-entry').click(function() {
				if (fileCount > 1  && !jQuery(this).hasClass('disabled')) { //only works if not disabled
					if (fileCount == fileCountMax) addFileLink.removeClass('disabled'); //if we were @ max, we can enable add link again
					
					jQuery(this).prevAll('.file-entry:first').remove(); //remove the last file-entry
					fileCount--;
					
					if (fileCount == 1) delFileLink.addClass('disabled'); //if we are @ min now, we need to disable del link
				}
				return false;
			});
		
		});
	}
	
});