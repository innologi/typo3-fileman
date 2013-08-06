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
	
	
	
	//*****************
	// Auto fill title
	//*****************

	jQuery('.tx-fileman .file-entry').each(function(i, entry) {
		initAutoFill(entry);
	});
	
	//a PHP basename() equivalent
	function basename(string) {
		string = string.replace(/\\/g,'/'); //note the regex global option in order to replace more than once
		return string.substring(string.lastIndexOf('/')+1);
	}

	//will auto-fill title only if title wasn't fiddled with manually
	function initAutoFill(entry) {
		var title = jQuery(entry).find('.optional .textinput:first');
		//setting the var as data, so we can change it when cloned later on
		jQuery(entry).data('titleUnchanged',true);
		//if anything was put in title manually, change the boolean
		title.keyup(function() {
			jQuery(entry).data('titleUnchanged',false);
		});
		//copy the fileupload val to title IF title remains untouched
		jQuery(entry).find('.fileupload').change(function() {
			if (jQuery(entry).data('titleUnchanged')) {
				//depending on the browser, you might get more than the filename, so we do basename()
				title.val(basename(jQuery(this).val()));
			}
		});
	}
	
	
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
			var fileuploadValue = jQuery(this).find('input[type=file].fileupload').val();
			if (fileuploadValue != undefined && fileuploadValue != '') {
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
	var delFileText = "###DEL_FILE###";
	var showOptionalText = "###SHOW_OPTIONAL###";
	
	//don't enable it unless there are more files allowed than 1
	if (fileCountMax > 1) {
		jQuery('.tx-fileman .multi-file').each(function(i, form) {
			var fileEntries = jQuery(form).find('.file-entry');
			var formVar = { //can be passed as reference to functions
					fileCount: fileEntries.size(), //there could be more files initially already, due to validation errors
					lastIndex: getLastIndex(form)
			};
			
			//create buttons
			jQuery(form).find('.submit').before('<a href="#" class="add-file-entry" title="'+addFileText+'">'+addFileText+'</a><a href="#" class="del-file-entry" title="'+delFileText+'">'+delFileText+'</a>');
			var addFileLink = jQuery(form).find('a.add-file-entry');
			var delFileLink = addFileLink.next('a.del-file-entry');
			delFileLink.remove(); //remove it here, clone it later
			
			//set initial state to disabled where it applies
			if (formVar.fileCount == fileCountMax) addFileLink.addClass('disabled'); //no adds possible if @ max
			if (formVar.fileCount == 1) delFileLink.addClass('disabled'); //no dels possible if @ min
			if (!jQuery(form).hasClass('multi-file-add')) addFileLink.hide(); //HIDE button if form doesn't meet requirement
			
			//for each initial file-entry, do the following
			fileEntries.each(function(i,entry) {
				var fileUpload = jQuery(entry).find('.fileupload');
				
				//place delFileLink clone
				var clone = delFileLink.clone();
				jQuery(clone).insertAfter(fileUpload);
				
				//when a delete link is clicked:
				jQuery(clone).click(function() { //TODO: undo button?
					deleteFileEntry(formVar,addFileLink,this,form);
					return false;
				});
				
				//the multi-file UI can get crowded, so we hide the optional fields under a button
				var optional = jQuery(entry).find('.optional');
				optional.hide();
				optional.addClass('indent'); //indent gives some special styling that should only be visible if multi-file UI is in effect
					//create button
				fileUpload.after('<a href="#" class="show-optional" title="'+showOptionalText+'">'+showOptionalText+'</a>');
				jQuery(entry).find('.show-optional').click(function() {
					toggleOptional(optional,this);
					return false;
				});
			});
			
			
			//only add this event if form meets requirements
			if (jQuery(form).hasClass('multi-file-add')) {
				//when an add link is clicked:
				jQuery(form).find('a.add-file-entry').click(function() {
					if (formVar.fileCount < fileCountMax && !jQuery(this).hasClass('disabled')) { //only works if not disabled and form allows adds
						if (formVar.fileCount == 1) jQuery(form).find('a.del-file-entry').removeClass('disabled'); //if we were @ min, we can enable the del link again
						formVar.fileCount++;
						//clone the last file-entry
						var fileEntry = jQuery(this).prevAll('.file-entry:first'); 
						var clone = fileEntry.clone();
						//replace its index in the clone
						var findName = '[file][i' + formVar.lastIndex + ']';
						var replaceName = '[file][i' + (++formVar.lastIndex) + ']';
						//empty field values!
						jQuery(clone).find('input[type=file],input[type=text],textarea').each(function(i, elem) {
							jQuery(elem).attr('name', jQuery(elem).attr('name').replace(findName,replaceName));
							//jQuery(elem).attr('value',''); //if input is type=text..
							jQuery(elem).val(null); //input values are copied with the clone..
						});
						
						//because clone() doesn't copy events, and clone(true) makes events retain their original targets, we have to assign certain events explicitly
							//--> show optional
						jQuery(clone).find('.show-optional').click(function() {
							toggleOptional(jQuery(clone).find('.optional'), this);
							return false;
						});
							//--> auto fill title
						initAutoFill(clone);
							//--> del file link
						jQuery(clone).find('a.del-file-entry').click(function() {
							deleteFileEntry(formVar,addFileLink,this,form);
							return false;
						});
						
						//place it before the button
						jQuery(this).before(clone);
						
						if (formVar.fileCount == fileCountMax) addFileLink.addClass('disabled'); //if we are @ max now, we need to disable add link
					}
					return false;
				});
			}
		
		});
	}
	
	//deletes a file entry
	function deleteFileEntry(countVars, addButton, deleteButton, form) {
		if (countVars.fileCount > 1  && !jQuery(deleteButton).hasClass('disabled')) { //only works if not disabled
			if (countVars.fileCount == fileCountMax) addButton.removeClass('disabled'); //if we were @ max, we can enable add link again
			countVars.fileCount--;
			
			jQuery(deleteButton).parents('.file-entry').remove(); //remove the file-entry the button belongs to
			countVars.lastIndex = getLastIndex(form);
			
			if (countVars.fileCount == 1) jQuery(form).find('a.del-file-entry').addClass('disabled'); //if we are @ min now, we need to disable ALL del links	
		}
	}
	
	//retrieves the last index from the form
	function getLastIndex(form) {
		//we want 999 from: <input class="fileupload" name="*[file][i999][fileUri]" />
		return jQuery(form).find('.fileupload:last').attr('name').match(/\[file\]\[i([0-9]+)\]\[fileUri\]/i)[1];
	}
	
	//toggle optional fields
	function toggleOptional(optional, button) {
		jQuery(optional).slideToggle();
		jQuery(button).toggleClass('expanded'); //this class helps for indicating an expanded view through styles on the button
	}
	
});