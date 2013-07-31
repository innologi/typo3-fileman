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
	
	//TODO: SHOULD BE ABLE TO SET THE UPLOAD PROGRESS BAR TYPE FROM TS
	//note that it is a string, so that the numbers get concatenated
	var upload_id_gen = '' + new Date().getTime() + Math.random();
	upload_id_gen = upload_id_gen.replace('.','');
	var updateProgressInt = {};
	
	jQuery('.tx-fileman .init-progressbar').each(function(i, form) {
		var upload_id = i + upload_id_gen;
		//TODO: APC UPLOAD PROGRESS should be set from TS
		jQuery(form).prepend('<input type="hidden" name="APC_UPLOAD_PROGRESS" value="' + upload_id + '" />');
		jQuery(form).after('<div id="fileman-uploadProgress'+i+'" class="uploadprogress"><div class="progressbar"></div><div class="progressvalue"></div></div>');
		
		jQuery(form).on('submit', function() {
			jQuery(this).hide();
			jQuery('.tx-fileman #fileman-uploadProgress'+i+' .progressbar').progressbar({
				value: 0
			});
			jQuery('.tx-fileman #fileman-uploadProgress'+i).show();
			
			updateProgressInt[i] = setInterval(function() {
				updateProgress(upload_id,i);
			}, 200);
			updateProgress(upload_id,i);
		});
	});
	
	//TODO: url should be set from TS, no cache should not be necessary once headers in script are set
	function updateProgress(id,i) {
		jQuery.get('/typo3conf/ext/fileman/Resources/Public/Scripts/UploadProgress.php', {upload_id: id, no_cache: Math.random()}, function(data) {
			//TODO: catch script errors
			var uploaded = parseInt(data);
			jQuery('.tx-fileman #fileman-uploadProgress'+i+' .progressvalue').text(uploaded + '%');
			jQuery('.tx-fileman #fileman-uploadProgress'+i+' .progressbar').progressbar('option','value',uploaded);
			
			if (uploaded == 100) {
				clearInterval(updateProgressInt[i]);
			}
		});
	};
	
});