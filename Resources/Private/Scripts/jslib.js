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
	jQuery('.tx-fileman .rel_switch').click(function() {
		jQuery(this).next('.tx-fileman .rel_links').slideToggle();
		return false;
	});
	
	//by default all is unhidden (in case of no js-support), force once to set initial state hidden
	jQuery('.tx-fileman .rel_switch').toggle();
	jQuery('.tx-fileman .rel_links').toggle();	
});