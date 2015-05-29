/**
 * Fileman Javascript Library
 * -----
 * jQuery dependent.
 * Yes, I know of $.
 * Yes, I know of noConflict().
 * @TODO this entire lib needs to be refactored
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

	//note that it is a string, so that the numbers get concatenated
	var upload_id_gen = '' + new Date().getTime() + Math.random();
	upload_id_gen = upload_id_gen.replace('.','');
	//contains interval return values for use by clearInterval
	var updateProgressInt = {};
	var apcFieldName = "###APC_FIELD_NAME###";
	var sesFieldName = "###SES_FIELD_NAME###";
	var sendingFileText = "###SENDING_FILE###";
	var debug = "###DEBUG###";
	var progressType = "###UPLOADPROGRESS###",
		uploadType = '###UPLOADTYPE###',
		uploadQueue = [],
		xhrUploadDone = false,
		allowMimeType = '###ALLOW_MIMETYPE###',
		maxFileSize = parseInt('###MAX_FILESIZE###'),
		maxTotalFileSize = parseInt('###MAX_TOTAL_FILESIZE###'),
		fileSizes = {},
		totalSize = 0,
		uploadedSize = 0,
		chunkSize = 1024 * 1024;


	if (window.File) {

		jQuery('.tx-fileman form').on('change', 'input[type=file].fileupload', function(e) {
			var $upload = jQuery(this),
				name = $upload.attr('name');
			if (this.files[0]) {
				var file = this.files[0];

				// filter on mime types
				if (allowMimeType.length > 0) {
					// sometimes, a MIME type is enclosed with double quotes
					var testFileType = trimChar(file.type, '"'),
						filter = new RegExp('^(' + allowMimeType + ')', 'i');

					if (!validateField(
						$upload,
						!filter.test(testFileType),
						'File type denied: ' + file.type,
						// @TODO llang
						'Bestandstype niet toegestaan.'
					)) {
						return;
					}
				}

				// file size limit per file
				if (maxFileSize > 0) {
					if (!validateField(
						$upload,
						file.size > maxFileSize,
						'File size ' + file.size + ' exceeds set limit: ' + maxFileSize,
						// @TODO llang
						'Bestand is groter dan het toegestane limiet van {maxFileSize}.'.replace('{maxFileSize}', bytesToSize(maxFileSize))
					)) {
						return;
					}
				}

				fileSizes[name] = file.size;
			} else if (fileSizes.hasOwnProperty(name)) {
				delete fileSizes[name];
			}

			totalSize = 0;
			for (var i in fileSizes) {
				if (fileSizes.hasOwnProperty(i)) {
					totalSize += fileSizes[i];
				}
			}

			// total file size limit
			if (maxTotalFileSize > 0) {
				if (!validateField(
					$upload,
					totalSize > maxTotalFileSize,
					'Total file size ' + totalSize + ' exceeds set limit: ' + maxTotalFileSize,
					// @TODO llang
					'Grootte van het totale aantal gekozen bestanden is groter dan het toegestane limiet van {maxTotalFileSize}.'.replace('{maxTotalFileSize}', bytesToSize(maxTotalFileSize))
				)) {
					return;
				}
			}

		});

		if (window.FileReader) {
			initXhrUpload();
		}
	}

	if (uploadType === 'js' || progressType != 'none') {

		jQuery('.tx-fileman .init-progressbar').each(function(i, form) {
			i++;
			jQuery(form).after('<div id="fileman-uploadProgress'+i+'" class="uploadprogress"><div class="progressbar"></div><div class="progressvalue"></div></div>');

			if (uploadType !== 'js') {
				var upload_id = i + upload_id_gen;
				if (progressType == 'session') {
					jQuery(form).prepend('<input type="hidden" name="'+sesFieldName+'" value="' + upload_id + '" />');
				} else if (progressType == 'apc') {
					jQuery(form).prepend('<input type="hidden" name="'+apcFieldName+'" value="' + upload_id + '" />');
				} else if (progressType == 'uploadprogress') {
					jQuery(form).prepend('<input type="hidden" name="UPLOAD_IDENTIFIER" value="' + upload_id + '" />');
				}

				jQuery(form).on('submit', function(e) {
					// fileuploadValue will only be empty if none of the fileupload fields have a value
					var fileuploadValue = jQuery(this).find('input[type=file].fileupload').val();
					// only show the progressbar if fileupload is not empty
					if (fileuploadValue !== undefined && fileuploadValue !== '') {
						jQuery(this).hide();
						jQuery('.tx-fileman #fileman-uploadProgress'+i).show();

						updateProgressInt[i] = setInterval(function() {
							updateProgress(upload_id,i);
						}, 100); //@TODO: should be configurable
						updateProgress(upload_id,i); //the interval runs only AFTER its interval, so we run it at the start here
					}
				});
			}
		});
	}

	/**
	 * Validates an upload field with a reversed assertion.
	 *
	 * @param $upload jQuery object of upload field
	 * @param assertion If this assertion is true, then the field won't validate
	 * @param consoleMsg
	 * @param errorMsg
	 * @return boolean
	 */
	function validateField($upload, assertion, consoleMsg, errorMsg) {
		if (assertion) {
			console.log(consoleMsg);
			$upload.val(null);
			$upload.addClass('f3-form-error file-checker-error');

			var $label = $upload.parent('label'),
				$errorMsg = $label.prev('.typo3-messages').find('.typo3-message');
			if ($errorMsg[0]) {
				$errorMsg.text(errorMsg);
			} else {
				$label.before('<div class="typo3-messages"><div class="typo3-message message-error">' + errorMsg + '</div></div>');
			}
			return false;
		} else {
			if ($upload.hasClass('file-checker-error')) {
				$upload.removeClass('f3-form-error file-checker-error');
				$upload.parent('label').prev('.typo3-messages').remove();
			}
		}
		return true;
	}

	/**
	 * Trims the chosen char from beginning and end of string
	 *
	 * @param string
	 * @param charToRemove
	 * @return string
	 */
	function trimChar(string, charToRemove) {
		while(string.charAt(0) === charToRemove) {
			string = string.substring(1);
		}
		while(string.charAt(string.length-1) === charToRemove) {
			string = string.substring(0, (string.length-1));
		}
		return string;
	}

	/**
	 * Convert bytes to human readable size,
	 * e.g. KB, MB, GB, TB
	 *
	 * @param bytes
	 * @return string
	 */
	function bytesToSize(bytes) {
		var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
		if (bytes == 0) return 'n/a';
		var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
		return (bytes / Math.pow(1024, i)).toFixed(1) + ' ' + sizes[i];
	}


	//@TODO: url should be set from TS, no cache should not be necessary once headers in script are set
	//updates progress in progress bar
	function updateProgress(id,i) {
		jQuery.get('/typo3conf/ext/fileman/Resources/Public/Scripts/UploadProgress.php', {upload_id: id, no_cache: Math.random(), type: progressType}, function(data) {
			//@TODO: catch script errors
			var uploaded = parseInt(data);
			if (debug == '1' && isNaN(uploaded)) {
				jQuery('.tx-fileman #fileman-uploadProgress'+i+' .progressvalue').html('Not receiving upload-progress status: '+data);
			} else {
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
			}
		});
	};

	/**
	 * Initializes XHR Upload mechanism, if enabled
	 *
	 * @return void
	 */
	function initXhrUpload() {
		if (uploadType === 'js') {
			jQuery('.tx-fileman .init-progressbar').each(function(i, form) {
				i++;
				jQuery(form).on('submit', function(e) {
					if (!xhrUploadDone) {
						// prevent actual submitting
						e.preventDefault();
						// start xhr method
						xhrUploadFiles(this, i);
					}
				});
			});
		}
	}

	/**
	 * Upload every upload-file within the form
	 *
	 * @param form
	 * @param index
	 * @return void
	 */
	function xhrUploadFiles(form, index) {
		var previouslyUploaded = 0;
		jQuery('.fileupload', form).each(function(i, upload) {
			var $upload = jQuery(upload);
			i++;
			if (
				// these are needed to cover every circumstance for every single browser (read: IE)
				upload.files !== undefined && upload.files !== null && upload.files.length > 0
				// and these are the actual conditions of importance
				&& upload.files[0] instanceof File && upload.files[0].name.length > 0
			) {
				uploadQueue.push({
					file: upload.files[0],
					formIndex: index,
					uploadIndex: i,
					form: form
				});
			} else {
				var value = $upload.val();
				if (value !== undefined && value.length > 0) {
					// this field represents a previously uploaded file
					previouslyUploaded++;
				}
			}

			if (uploadQueue.length > 0) {
				$upload.after(
					'<input type="text" name="tx_fileman_filelist[files][file][i' + i + '][fileUri]" readonly="readonly" class="fileupload fill-' + i + '" value="" />' +
					'<input type="hidden" name="tx_fileman_filelist[tmpFiles][i' + i + ']" class="tmpfile fill-' + i + '" value="" />'
				);
				// disable the original file upload
				$upload.remove();
			}
		});

		if (uploadQueue.length > 0) {
			jQuery(form).hide();
			jQuery('.tx-fileman #fileman-uploadProgress'+index).show();

			var first = uploadQueue.shift();
			xhrUploadFileInChunks(first.file, first.formIndex, first.uploadIndex, first.form);
		} else if (previouslyUploaded > 0) {
			// if there are no upload-files, but there are previously uploaded files, submit the form as normal
			xhrUploadDone = true;
			jQuery(form).submit();
		}
	}

	/**
	 * Upload a file in chunks via XHR mechanism
	 *
	 * @param file
	 * @param i Form index
	 * @param j Uploadfield index
	 * @param form
	 * @return void
	 */
	function xhrUploadFileInChunks(file, i, j, form) {
		var reader = new FileReader(),
			xhr = new XMLHttpRequest(),
			startByte = 0,
			endByte = chunkSize,
			// the size is expanded by our transfer method
			totalSizeExp = totalSize * 1.33,
			state = 0,
			// we need file.name to be writable, hence an additional var
			filename = file.name,
			transferUri = '/typo3conf/ext/fileman/Resources/Public/Scripts/FileTransfer.php';

		reader.onload = function(e) {
			xhr.open('PUT', transferUri + '?filename=' + filename + '&state=' + state + '&no_cache=' + Math.random());
			// @TODO what to do with these?
			//xhr.setRequestHeader('x-test-sanitize', '1');
			xhr.setRequestHeader('Content-Type', 'application/octet-stream');
			xhr.responseType = 'json';
			xhr.send(e.target.result);
		}
		reader.onerror = function(e) {
			console.log('ERROR: Could not read file');
		}
		xhr.addEventListener('load', function(e) {
			state = 1;
			if (e.target.response) {
				var response = e.target.response;
				// IE doesn't automaically parse JSON responses
				if (typeof(response) !== 'object') {
					response = JSON.parse(response);
				}
				if (debug == '1') {
					console.log(response);
				}

				if (response.success && response.success === 1) {
					if (response.tmp_name) {
						filename = response.tmp_name;
					}
					if (endByte < file.size) {
						startByte = endByte;
						endByte += chunkSize;
						if (endByte > file.size) {
							endByte = file.size;
						}
						reader.readAsDataURL(
							file.slice(startByte, endByte)
						);
					} else {
						// @TODO log succesful transfer?
						jQuery('.tmpfile.fill-' + j, form).val(filename);
						jQuery('.fileupload.fill-' + j, form).val(file.name);
						var next = uploadQueue.shift();
						if (next === undefined) {
							xhrUploadDone = true;
							jQuery(form).submit();
						} else {
							xhrUploadFileInChunks(next.file, next.formIndex, next.uploadIndex, next.form);
						}
					}
					return;
				}
			}
			// @TODO what if an error occurred?
		}, false);
		xhr.addEventListener('error', function(e) {
			console.log('ERROR: Could not send file');
		}, false);
		xhr.upload.addEventListener('progress', function(e) {
			$progressVal = jQuery('.tx-fileman #fileman-uploadProgress'+i+' .progressvalue');
			$progressBar = jQuery('.tx-fileman #fileman-uploadProgress'+i+' .progressbar');
			if ($progressVal[0] && $progressBar[0]) {
				if (e.lengthComputable) {
					var uploaded = ((uploadedSize + e.loaded) / totalSizeExp) * 100;
					if (uploaded > 100) {
						uploaded = 100;
					}
					//console.log(uploaded);
					$progressBar.css({
						'width': uploaded + '%'
					});
					uploaded = parseInt(uploaded);
					if (uploaded === 100) {
						//because the request won't be really "done" at the same time the file is received, we display 99% to indicate a still unfinished state
						//this way, the user never sees 100% and is (hopefully) not tempted to do something that breaks the process too early
						$progressVal.text(sendingFileText+' 99%');
					} else {
						$progressVal.text(sendingFileText+' '+uploaded+'%');
					}
					if (e.loaded === e.total) {
						uploadedSize += e.total;
					}
				} else if (debug == '1') {
					$progressVal.html('Not receiving upload-progress status.');
				}
			}
		}, false);

		if (endByte > file.size) {
			endByte = file.size;
		}
		reader.readAsDataURL(
			file.slice(startByte, endByte)
		);
	}



	//**********************
	// Multi-file Upload UI
	//**********************

	var fileCountMax = parseInt("###MAX_FILE_UPLOADS###");
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
				jQuery(clone).click(function() { //@TODO: undo button?
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

			var $parent = jQuery(deleteButton).parents('.file-entry'),
				// for the js upload features: removes filesize from fileSizes register
				$upload = $parent.find('input[type=file].fileupload');
			$upload.val(null);
			$upload.change();

			//remove the file-entry the button belongs to
			$parent.remove();
			countVars.lastIndex = getLastIndex(form);

			if (countVars.fileCount == 1) jQuery(form).find('a.del-file-entry').addClass('disabled'); //if we are @ min now, we need to disable ALL del links
		}
	}

	//retrieves the last index from the form
	function getLastIndex(form) {
		//we want 999 from: <input class="fileupload" name="*[file][i999][fileUri]" />
		return jQuery(form).find('.fileupload:last').attr('name').match(/\[file\]\[i([0-9]+)\]/i)[1];
	}

	//toggle optional fields
	function toggleOptional(optional, button) {
		jQuery(optional).slideToggle();
		jQuery(button).toggleClass('expanded'); //this class helps for indicating an expanded view through styles on the button
	}



	//**********************************************
	// XHR CSRF-protection
	//**********************************************

	// @TODO read class through TS?
	var $csrfProtectA = jQuery('.tx-fileman a.csrf-protect'),
		$csrfProtectForm = jQuery('.tx-fileman form.csrf-protect'),
		xhrPageType = '###XHR_PAGETYPE###',
		xhrPageId = '###XHR_PAGEID###';
	if ($csrfProtectA[0] || $csrfProtectForm[0]) {
		var $submitButtons = jQuery(':submit', $csrfProtectForm),
			encodedUrls = [];
		$submitButtons.hide();
		$csrfProtectA.hide();
		$csrfProtectA.each(function (i, a) {
			encodedUrls.push(jQuery(a).attr('data-utoken'));
		});
		$csrfProtectForm.each(function (i, form) {
			encodedUrls.push(jQuery(form).attr('data-utoken'));
		});

		var xhr = new XMLHttpRequest();
		xhr.open('HEAD', 'index.php?id=' + xhrPageId + '&type=' + xhrPageType + '&tx_fileman_filelist[controller]=Category&tx_fileman_filelist[action]=ajaxGenerateTokens', true);
		// @TODO what if the header is too large? (e.g. default apache is 8kb)
		xhr.setRequestHeader('innologi--utoken', encodedUrls);
		xhr.onload = function(e) {
			if (this.status == 200) {
				var tokens = this.getResponseHeader('innologi__stoken'),
					tokenCounter = 0;
				if (tokens !== null) {
					tokens = tokens.split(',');
					$csrfProtectA.each(function (i, a) {
						jQuery(a).attr('data-stoken', tokens[tokenCounter++]);
						jQuery(a).click(function () {
							verifyToken(
								jQuery(this).attr('data-stoken'), jQuery(this).attr('data-utoken')
							);
						});
					});
					$csrfProtectForm.each(function (i, form) {
						jQuery(form).attr('data-stoken', tokens[tokenCounter++]);
						jQuery(form).submit(function () {
							verifyToken(
								jQuery(this).attr('data-stoken'), jQuery(this).attr('data-utoken')
							);
						});
					});
				}
			}
			$submitButtons.show();
			$csrfProtectA.show();
		};
		xhr.send();
	}

	function verifyToken(token, tokenUri) {
		var xhr = new XMLHttpRequest();
		xhr.open('HEAD', 'index.php?id=' + xhrPageId + '&type=' + xhrPageType + '&tx_fileman_filelist[controller]=Category&tx_fileman_filelist[action]=ajaxVerifyToken&tx_fileman_filelist[encodedUrl]=' + tokenUri, false);
		xhr.setRequestHeader('innologi--stoken', token);
		xhr.send();
	}

});