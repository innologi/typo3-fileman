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

	var $fileman = jQuery('.tx-fileman');


	//*****************
	// Search features
	//*****************

	var $searchBox = $fileman.find('.search-form .searchbox');

	if ($searchBox[0]) {
		var searchWidth = $searchBox.width(),
			searchTerms = $searchBox[0].value.trim();

		if ($searchBox.val().length > 0) {
			$searchBox.css({
				width: '85%'
			});
		}
		$searchBox.on('focus', function() {
			if (this.value.length < 1) {
				jQuery(this).animate({
					width: '85%'
				});
			}
		});
		$searchBox.on('blur', function() {
			if (this.value.length < 1) {
				jQuery(this).animate({
					width: searchWidth
				});
			}
		});

		if (searchTerms.length > 0) {
			// split search terms
			searchTerms = searchTerms.split(' ');

			// remove empty elements (i.e. due to double spaces)
			for (var i in searchTerms) {
				if (searchTerms.hasOwnProperty(i)) {
					if (searchTerms[i].length < 1) {
						searchTerms.splice(i, 1);
					}
				}
			}

			// create Regexp pattern from searchterms, incl. negative lookbehind to NOT match anything part of an HTML tag
			var pattern = new RegExp('(' + searchTerms.join('|') + ')(?![^<]*>|[^<>]<\/)', 'ig');

			// replace each match with itself wrapped in a span for styling
			$fileman.find('table.tx_fileman').each(function(i, table) {
				var $obj = jQuery(table),
					replacement = '<span class="search-match">$1</span>';
				// note that this replacement would destroy event handlers, hence this feature must be applied before all others
				$obj.html($obj.html().replace(pattern, replacement));
			});
		}
	}


	//***********************
	// Hidden Field Switcher
	//***********************

	//click function toggles unhide class on all relevant elements
	var $switches = $fileman.find('.rel-switch');
	$switches.click(function() {
		jQuery(this).next('.tx-fileman .rel-links').slideToggle();
		return false;
	});

	//by default all is unhidden (in case of no js-support), force once to set initial state hidden
	$switches.show();
	$fileman.find('.rel-links').hide();



	//*********************
	// Delete Confirmation
	//*********************

	//click function performs a confirm, if TRUE/OK continues button functionality
	$fileman.find('.button-delete').click(function(e) {
		if(confirm('###DELETE_CONFIRM###')) {
			return true;
		} else {
			e.stopImmediatePropagation();
			return false;
		};
	});



	//*****************
	// Auto fill title
	//*****************

	$fileman.find('.file-entry').each(function(i, entry) {
		initAutoFill(entry);
	});

	//a PHP basename() equivalent
	function basename(string) {
		string = string.replace(/\\/g,'/'); //note the regex global option in order to replace more than once
		return string.substring(string.lastIndexOf('/')+1);
	}

	//will auto-fill title only if title wasn't fiddled with manually
	function initAutoFill(entry) {
		var $entry = jQuery(entry),
			$title = $entry.find('.optional .textinput:first');
		//setting the var as data, so we can change it when cloned later on
		$entry.data('titleUnchanged',true);
		//if anything was put in title manually, change the boolean
		$title.keyup(function() {
			$entry.data('titleUnchanged',false);
		});
		//copy the fileupload val to title IF title remains untouched
		$entry.find('.fileupload').change(function() {
			if ($entry.data('titleUnchanged')) {
				//depending on the browser, you might get more than the filename, so we do basename()
				$title.val(basename(jQuery(this).val()));
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
	var updateProgressInt = {},
		apcFieldName = '###APC_FIELD_NAME###',
		sesFieldName = '###SES_FIELD_NAME###',
		sendingFileText = '###SENDING_FILE###',
		debug = '###DEBUG###',
		progressType = '###UPLOADPROGRESS###',
		uploadType = '###UPLOADTYPE###',
		uploadQueue = [],
		xhrUploadEnabled = false,
		xhrUploadDone = false,
		allowMimeType = '###ALLOW_MIMETYPE###',
		maxFileSize = parseInt('###MAX_FILESIZE###'),
		maxTotalFileSize = parseInt('###MAX_TOTAL_FILESIZE###'),
		fileSizes = {},
		totalSize = 0,
		totalSizeHR = '',
		uploadedSize = 0,
		chunkSize = parseInt('###CHUNKSIZE###'),
		$progress = null;

	if (chunkSize < 1) {
		// 1 MB default
		chunkSize = 1024 * 1024;
	}


	if (window.File) {

		$fileman.find('form').on('change', 'input[type=file].fileupload', function(e) {
			var $upload = jQuery(this);
			if (!validateFiles(this.files, $upload, $upload.attr('name'))) {
				return false;
			}
		});

		if (window.FileReader) {
			initXhrUpload();
		}
	}

	if (uploadType === 'js' || progressType != 'none') {

		$fileman.find('.init-progressbar').each(function(i, form) {
			i++;
			var $form = jQuery(form);
			$form.after('<div id="fileman-uploadProgress'+i+'" class="uploadprogress"><div class="progressbar"></div><div class="progressvalue"></div></div>');

			if (uploadType !== 'js') {
				var upload_id = i + upload_id_gen;
				if (progressType == 'session') {
					$form.prepend('<input type="hidden" name="'+sesFieldName+'" value="' + upload_id + '" />');
				} else if (progressType == 'apc') {
					$form.prepend('<input type="hidden" name="'+apcFieldName+'" value="' + upload_id + '" />');
				} else if (progressType == 'uploadprogress') {
					$form.prepend('<input type="hidden" name="UPLOAD_IDENTIFIER" value="' + upload_id + '" />');
				}

				$form.on('submit', function(e) {
					// fileuploadValue will only be empty if none of the fileupload fields have a value
					var fileuploadValue = $form.find('input[type=file].fileupload').val();
					// only show the progressbar if fileupload is not empty
					if (fileuploadValue !== undefined && fileuploadValue !== '') {
						$form.hide();
						$fileman.find('#fileman-uploadProgress'+i).show();

						updateProgressInt[i] = setInterval(function() {
							updateProgress(upload_id,i);
						}, 100);
						updateProgress(upload_id,i); //the interval runs only AFTER its interval, so we run it at the start here
					}
				});
			}
		});
	}

	/**
	 * Validates a number of upload files
	 *
	 * @param files FileList or array containing files
	 * @param $upload jQuery object of upload field
	 * @param basename Base of field name
	 * @return boolean
	 */
	function validateFiles(files, $upload, basename) {
		// start with no errors
		if ($upload.hasClass('file-checker-error')) {
			$upload.removeClass('f3-form-error file-checker-error');
			$upload.parent('label').prev('.typo3-messages').remove();
		}

		// by default, this method is only called with 1 file, so set a default name based on that
		var name = basename + 0,
			error = false;
		if (files.length > 0) {
			for (var i=0; i < files.length; i++) {
				var file = files[i];
				name = basename + i;

				// filter on mime types
				if (allowMimeType.length > 0) {
					// sometimes, a MIME type is enclosed with double quotes
					var testFileType = trimChar(file.type, '"'),
						filter = new RegExp('^(' + allowMimeType + ')', 'i');

					if (!validateField(
						$upload,
						!filter.test(testFileType),
						'File type denied for \'' + file.name + '\': ' + file.type,
						'###VALID_FAIL_MIMETYPE###'.replace('{fileName}', file.name)
					)) {
						error = true;
					}
				}

				// file size limit per file
				if (maxFileSize > 0) {
					if (!validateField(
						$upload,
						file.size > maxFileSize,
						'File size ' + file.size + ' of \'' + file.name + '\' exceeds set limit: ' + maxFileSize,
						'###VALID_FAIL_MAXFILESIZE###'.replace('{maxFileSize}', bytesToSize(maxFileSize)).replace('{fileName}', file.name)
					)) {
						error = true;
					}
				}

				fileSizes[name] = file.size;
			}
		} else if (fileSizes.hasOwnProperty(name)) {
			delete fileSizes[name];
		}

		// recalculate total size on any fileupload change
		totalSize = 0;
		for (var i in fileSizes) {
			if (fileSizes.hasOwnProperty(i)) {
				totalSize += fileSizes[i];
			}
		}
		totalSizeHR = bytesToSize(totalSize);

		// total file size limit
		if (maxTotalFileSize > 0) {
			if (!validateField(
				$upload,
				totalSize > maxTotalFileSize,
				'Total file size ' + totalSize + ' exceeds set limit: ' + maxTotalFileSize,
				'###VALID_FAIL_TOTFILESIZE###'.replace('{maxTotalFileSize}', bytesToSize(maxTotalFileSize))
			)) {
				error = true;
			}
		}

		return !error;
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
			errorMessage(errorMsg, $upload.parent('label'));
			return false;
		}
		return true;
	}

	// produce visual TYPO3-like errormessage
	function errorMessage(errorMsg, $elemAfter) {
		errorMsg = '<li class="alert alert-danger"><p class="alert-message">' + errorMsg + '</p></li>';

		var $errors = $elemAfter.prev('.typo3-messages');
		if ($errors[0]) {
			$errors.append(errorMsg);
		} else {
			$elemAfter.before('<ul class="typo3-messages">' + errorMsg + '</ul>');
		}
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


	//@TODO url should be set from TS, no cache should not be necessary once headers in script are set
	//updates progress in progress bar
	function updateProgress(id,i) {
		jQuery.get('/typo3conf/ext/fileman/Resources/Public/Scripts/UploadProgress.php', {upload_id: id, no_cache: Math.random(), type: progressType}, function(data) {
			//@TODO: catch script errors
			var uploaded = parseInt(data),
				$progress = $fileman.find('#fileman-uploadProgress'+i),
				$progressValue = $progress.find('.progressvalue');
			if (debug == '1' && isNaN(uploaded)) {
				$progressValue.html('Not receiving upload-progress status: '+data);
			} else {
				$progress.find('.progressbar').css({
					'width': uploaded + '%'
				});
				if (uploaded == 100) {
					clearInterval(updateProgressInt[i]);
					//because the request won't be really "done" at the same time the file is received, we display 99% to indicate a still unfinished state
					//this way, the user never sees 100% and is (hopefully) not tempted to do something that breaks the process too early
					$progressValue.text(sendingFileText+' 99%');
				} else {
					$progressValue.text(sendingFileText+' '+uploaded+'%');
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
			$fileman.find('.init-progressbar').each(function(i, form) {
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
			xhrUploadEnabled = true;
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
		var previouslyUploaded = 0,
			$form = jQuery(form);

		$form.find('.fileupload').each(function(i, upload) {
			var $upload = jQuery(upload),
				// the reason we can't just use i+1, is because of the delFileEntry button capable of
					// producing a non-succeeding index count
				uIndex = getIndex($upload);
			if (
				// these are needed to cover every circumstance for every single browser (read: IE)
				upload.files !== undefined && upload.files !== null && upload.files.length > 0
				// and these are the actual conditions of importance
				&& upload.files[0] instanceof File && upload.files[0].name.length > 0
			) {
				uploadQueue.push({
					file: upload.files[0],
					uploadIndex: uIndex,
					form: form
				});

				$upload.after(
					'<input type="text" name="tx_fileman_filelist[files][file][i' + uIndex + '][fileUri]" readonly="readonly" class="fileupload fill-' + uIndex + '" value="" />' +
					'<input type="hidden" name="tx_fileman_filelist[tmpFiles][i' + uIndex + ']" class="tmpfile fill-' + uIndex + '" value="" />'
				);
				// disable the original file upload
				$upload.remove();
			} else {
				var value = $upload.val();
				if (value !== undefined && value.length > 0) {
					// this field represents a previously uploaded file
					previouslyUploaded++;
				}
			}
		});


		if (uploadQueue.length > 0) {
			$form.hide();
			$progress = $fileman.find('#fileman-uploadProgress'+index);
			$progress.show();

			var first = uploadQueue.shift();
			xhrUploadFileInChunks(first.file, first.uploadIndex, first.form);
		} else if (previouslyUploaded > 0) {
			// if there are no upload-files, but there are previously uploaded files, submit the form as normal
			xhrUploadDone = true;
			$form.submit();
		}
	}

	/**
	 * Upload a file in chunks via XHR mechanism
	 *
	 * @param file
	 * @param j Uploadfield index
	 * @param form
	 * @return void
	 */
	function xhrUploadFileInChunks(file, j, form) {
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

				// @TODO log succesful / failed transfers?
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
						var $form = jQuery(form);
						$form.find('.tmpfile.fill-' + j).val(filename);
						$form.find('.fileupload.fill-' + j).val(file.name);

						var next = uploadQueue.shift();
						if (next === undefined) {
							xhrUploadDone = true;
							$form.submit();
						} else {
							xhrUploadFileInChunks(next.file, next.uploadIndex, next.form);
						}
					}
					return;
				} else {
					console.log('ERROR: File transfer failure');
					errorMessage('###ERROR_FILE_TRANSFER###', $progress);
					return;
				}
			} else {
				console.log('ERROR: No valid XHR response');
				errorMessage('###ERROR_XHR_RESPONSE###', $progress);
				if (debug == '1') {
					console.log(e);
				}
				return;
			}
		}, false);
		xhr.addEventListener('error', function(e) {
			console.log('ERROR: No connection, retrying in 30 seconds');
			$progress.find('.progressvalue').text('###XHR_RETRY###');
			setTimeout(function() {
				xhrUploadFileInChunks(file, j, form);
			}, 30000);
		}, false);
		xhr.upload.addEventListener('progress', function(e) {
			var $progressVal = $progress.find('.progressvalue'),
				$progressBar = $progress.find('.progressbar');
			if ($progressVal[0] && $progressBar[0]) {
				if (e.lengthComputable) {
					var uploadedBytes = uploadedSize + e.loaded,
						uploaded = (uploadedBytes / totalSizeExp) * 100;
					if (uploaded > 100) {
						uploaded = 100;
					}
					if (debug == '1') {
						console.log(uploaded);
					}
					$progressBar.css({
						'width': uploaded + '%'
					});
					uploaded = parseInt(uploaded);
					if (uploaded === 100) {
						//because the request won't be really "done" at the same time the file is received, we display 99% to indicate a still unfinished state
						//this way, the user never sees 100% and is (hopefully) not tempted to do something that breaks the process too early
						$progressVal.text(sendingFileText+' 99%');
					} else {
						$progressVal.text(sendingFileText+' '+uploaded+'% ('+ bytesToSize(uploadedBytes/1.33) +' / '+ totalSizeHR +')');
					}
					if (e.loaded === e.total) {
						uploadedSize += e.total;
					}
				} else if (debug == '1') {
					$progressVal.html('###XHR_NO_PROGRESS###');
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

	var fileCountMax = parseInt('###MAX_FILE_UPLOADS###'),
		addFileText = '###ADD_FILE###',
		delFileText = '###DEL_FILE###',
		showOptionalText = '###SHOW_OPTIONAL###';
	//don't enable it unless there are more files allowed than 1
	if (fileCountMax > 1) {
		$fileman.find('.multi-file').each(function(i, form) {
			var $form = jQuery(form),
				$fileEntries = $form.find('.file-entry'),
				formVar = { //can be passed as reference to functions
					fileCount: $fileEntries.length, //there could be more files initially already, due to validation errors
					lastIndex: getLastIndex(form)
				};

			//create buttons
			$form.find('.submit').before('<a href="#" class="add-file-entry" title="'+addFileText+'">'+addFileText+'</a><a href="#" class="del-file-entry" title="'+delFileText+'">'+delFileText+'</a>');
			var $addFileLink = $form.find('a.add-file-entry');
			var $delFileLink = $addFileLink.next('a.del-file-entry');
			$delFileLink.remove(); //remove it here, clone it later

			//set initial state to disabled where it applies
			if (formVar.fileCount == fileCountMax) $addFileLink.addClass('disabled'); //no adds possible if @ max
			if (formVar.fileCount == 1) $delFileLink.addClass('disabled'); //no dels possible if @ min
			if (!$form.hasClass('multi-file-add')) $addFileLink.hide(); //HIDE button if form doesn't meet requirement

			//for each initial file-entry, do the following
			$fileEntries.each(function(i,entry) {
				var $entry = jQuery(entry),
					$fileUpload = $entry.find('.fileupload');

				//place delFileLink clone
				var $clone = jQuery($delFileLink.clone());
				$clone.insertAfter($fileUpload);

				//when a delete link is clicked:
				$clone.click(function() { //@TODO: undo button?
					deleteFileEntry(formVar,$addFileLink,this,form);
					return false;
				});

				//the multi-file UI can get crowded, so we hide the optional fields under a button
				var $optional = $entry.find('.optional');
				$optional.hide().addClass('indent'); //indent gives some special styling that should only be visible if multi-file UI is in effect
					//create button
				$fileUpload.after('<a href="#" class="show-optional" title="'+showOptionalText+'">'+showOptionalText+'</a>');
				$entry.find('.show-optional').click(function() {
					toggleOptional($optional,this);
					return false;
				});
			});


			//only add this event if form meets requirements
			if ($form.hasClass('multi-file-add')) {
				//when an add link is clicked:
				$addFileLink.click(function() {
					var $addEntry = jQuery(this);
					if (formVar.fileCount < fileCountMax && !$addEntry.hasClass('disabled')) { //only works if not disabled and form allows adds
						// note that del-file-entries were cloned and $delFileLink was the original, so don't try to refactor this one!
						if (formVar.fileCount == 1) $form.find('a.del-file-entry').removeClass('disabled'); //if we were @ min, we can enable the del link again
						formVar.fileCount++;
						//clone the last file-entry
						var clone = $addEntry.prevAll('.file-entry:first').clone(),
							$clone = jQuery(clone),
							//replace its index in the clone
							findName = '[file][i' + formVar.lastIndex + ']';
							replaceName = '[file][i' + (++formVar.lastIndex) + ']';
						//empty field values!
						$clone.find('input[type=file],input[type=text],textarea').each(function(i, elem) {
							var $elem = jQuery(elem);
							$elem.attr('name', $elem.attr('name').replace(findName,replaceName));
							//$elem.attr('value',''); //if input is type=text..
							$elem.val(null); //input values are copied with the clone..
						});

						//because clone() doesn't copy events, and clone(true) makes events retain their original targets, we have to assign certain events explicitly
							//--> show optional
						$clone.find('.show-optional').click(function() {
							toggleOptional($clone.find('.optional'), this);
							return false;
						});
							//--> auto fill title
						initAutoFill(clone);
							//--> del file link
						$clone.find('a.del-file-entry').click(function() {
							deleteFileEntry(formVar,$addFileLink,this,form);
							return false;
						});

						//place it before the button
						$addEntry.before(clone);

						if (formVar.fileCount == fileCountMax) $addFileLink.addClass('disabled'); //if we are @ max now, we need to disable add link
					}
					return false;
				});
			} else {
				// makes other JS funcs not look for add-file-entry button, since multi-file handling is currently depending on it
				fileCountMax = 1;
			}

		});
	}

	//deletes a file entry
	function deleteFileEntry(countVars, $addButton, deleteButton, form) {
		var $deleteButton = jQuery(deleteButton);
		if (countVars.fileCount > 1  && !$deleteButton.hasClass('disabled')) { //only works if not disabled
			if (countVars.fileCount == fileCountMax) $addButton.removeClass('disabled'); //if we were @ max, we can enable add link again
			countVars.fileCount--;

			var $parent = $deleteButton.parents('.file-entry'),
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
		return getIndex(jQuery(form).find('.fileupload:last'));
	}
	function getIndex($fileupload) {
		//we want 999 from: <input class="fileupload" name="*[file][i999][fileUri]" />
		return $fileupload.attr('name').match(/\[file\]\[i([0-9]+)\]/i)[1];
	}

	//toggle optional fields
	function toggleOptional($optional, button) {
		$optional.slideToggle();
		jQuery(button).toggleClass('expanded'); //this class helps for indicating an expanded view through styles on the button
	}



	//**********************
	// Drag 'n Drop Support
	//**********************

	var dropzoneActive = false;
	// relies on xhr uploading & draggable feature-support
	if (xhrUploadEnabled && 'draggable' in document.createElement('span')) {
		var $dropzones = $fileman.find('.drop-zone');

		if ($dropzones.length > 0) {
			$dropzones.prepend('<div class="drop-overlay"></div><div class="drop-here" title="###DROP_ZONE_TOOLTIP###">###DROP_ZONE###</div>');
			$dropzones.on('drop', function(e) {
				e.originalEvent.preventDefault();
				var $overlay = jQuery('.drop-overlay', this);
				$overlay.toggleClass('loading');
				if (!drop_handler(e.originalEvent, this)) {
					drop_exit($overlay);
					$overlay.toggleClass('loading');
				}
			});
			$dropzones.on('dragover', function(e) {
				e.originalEvent.preventDefault();
			});
			$dropzones.on('dragenter', function(e) {
				e.originalEvent.preventDefault();
				e.originalEvent.stopPropagation();
				if (!dropzoneActive) {
					jQuery('.drop-overlay', this).show();
					dropzoneActive = true;
				}
			});
			// setting dragleave on $dropzones will result in in a leave after 2 enters, due to the overlay popping up,
			// so instead, we set the leave on the overlay itself. dragexit did not have this issue, but that one doesn't
			// fire in chromium and IE.
			$dropzones.find('.drop-overlay').on('dragleave', function(e) {
				e.originalEvent.preventDefault();
				e.originalEvent.stopPropagation();
				drop_exit(jQuery(this));
			});
		}
	}

	// exits/hides dropzone overlay
	function drop_exit($overlay) {
		if (dropzoneActive) {
			$overlay.hide();
			dropzoneActive = false;
		}
	}

	// handles actual drop event
	function drop_handler(e, form) {
		var dt = e.dataTransfer,
			// actual files container
			files = [];

		if (dt.items) {
			for (var i=0; i < dt.items.length; i++) {
				// if dropped items aren't files, reject them
				if (dt.items[i].kind == "file") {
					files.push(dt.items[i].getAsFile());
				}
			}
		} else {
			files = dt.files;
		}

		// continue only if there are actual files
		if (files.length > 0) {
			var $form = jQuery(form),
				$entries = $form.find('.file-entry'),
				$upload = $entries.first().find('.fileupload');

			// clean up any previous errors
			if ($upload.hasClass('file-checker-error')) {
				$upload.removeClass('f3-form-error file-checker-error');
				$upload.parent('label').prev('.typo3-messages').remove();
			}

			// check fileCountMax
			if (!validateField(
				$upload,
				files.length > fileCountMax,
				'Max file count is ' + fileCountMax + ', tried uploading ' + files.length,
				'###VALID_FAIL_FILECOUNT###'.replace('{maxFileCount}', fileCountMax)
			)) {
				return false;
			}

			// for dragNdrop, clear the filesizes object to ensure proper validation
			fileSizes = {};
			if (!validateFiles(files, $upload, 'dragNdrop')) {
				fileSizes = {};
				return false;
			}

			// adjust the number of file-entries in the form, to match expectancy of PHP processing
			if (files.length !== $entries.length) {
				if (files.length > $entries.length) {
					var addCount = files.length - $entries.length,
						$button = $form.find('a.add-file-entry');
					for (var i=0; i < addCount; i++) $button.click();
				} else {
					var remCount = ($entries.length - files.length) * -1;
					$entries.slice(remCount).remove();
				}
			}

			// $entries can be out of date, so do another find
			$form.find('.file-entry').each(function(i, entry) {
				var $fileupload = jQuery('.fileupload', entry),
					$replacement = jQuery('<span class="fileupload" name="' + $fileupload.attr('name') + '">' + files[i].name + '</span>');
				$replacement[0].files = [ files[i] ];
				$fileupload.replaceWith($replacement);
			});

			$form.submit();
		}
	}

});