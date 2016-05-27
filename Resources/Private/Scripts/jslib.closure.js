jQuery(document).ready(function(){function fa(a){a=a.replace(/\\/g,"/");return a.substring(a.lastIndexOf("/")+1)}function N(a){var b=jQuery(a),c=b.find(".optional .textinput:first");b.data("titleUnchanged",true);c.keyup(function(){b.data("titleUnchanged",false)});b.find(".fileupload").change(function(){b.data("titleUnchanged")&&c.val(fa(jQuery(this).val()))})}function O(a,b,c){if(b.hasClass("file-checker-error")){b.removeClass("f3-form-error file-checker-error");b.parent("label").prev(".typo3-messages").remove()}var d=
c+0,e=false;if(a.length>0)for(var g=0;g<a.length;g++){var f=a[g];d=c+g;if(P.length>0){var j=ga(f.type,'"');y(b,!(new RegExp("^("+P+")","i")).test(j),"File type denied for '"+f.name+"': "+f.type,"###VALID_FAIL_MIMETYPE###".replace("{fileName}",f.name))||(e=true)}if(z>0)y(b,f.size>z,"File size "+f.size+" of '"+f.name+"' exceeds set limit: "+z,"###VALID_FAIL_MAXFILESIZE###".replace("{maxFileSize}",A(z)).replace("{fileName}",f.name))||(e=true);o[d]=f.size}else o.hasOwnProperty(d)&&delete o[d];t=0;for(g in o)if(o.hasOwnProperty(g))t+=
o[g];Q=A(t);if(B>0)y(b,t>B,"Total file size "+t+" exceeds set limit: "+B,"###VALID_FAIL_TOTFILESIZE###".replace("{maxTotalFileSize}",A(B)))||(e=true);return!e}function y(a,b,c,d){if(b){console.log(c);a.val(null);a.addClass("f3-form-error file-checker-error");G(d,a.parent("label"));return false}return true}function G(a,b){a='<div class="typo3-message message-error">'+a+"</div>";var c=b.prev(".typo3-messages");c[0]?c.append(a):b.before('<div class="typo3-messages">'+a+"</div>")}function ga(a,b){for(;a.charAt(0)===
b;)a=a.substring(1);for(;a.charAt(a.length-1)===b;)a=a.substring(0,a.length-1);return a}function A(a){if(a==0)return"n/a";var b=parseInt(Math.floor(Math.log(a)/Math.log(1024)));return(a/Math.pow(1024,b)).toFixed(1)+" "+["Bytes","KB","MB","GB","TB"][b]}function R(a,b){jQuery.get("/typo3conf/ext/fileman/Resources/Public/Scripts/UploadProgress.php",{upload_id:a,no_cache:Math.random(),type:u},function(c){var d=parseInt(c),e=k.find("#fileman-uploadProgress"+b),g=e.find(".progressvalue");if(v=="1"&&isNaN(d))g.html("Not receiving upload-progress status: "+
c);else{e.find(".progressbar").css({width:d+"%"});if(d==100){clearInterval(S[b]);g.text(C+" 99%")}else g.text(C+" "+d+"%")}})}function ha(){if(H==="js"){k.find(".init-progressbar").each(function(a,b){a++;jQuery(b).on("submit",function(c){if(!I){c.preventDefault();ia(this,a)}})});T=true}}function ia(a,b){var c=0,d=jQuery(a);d.find(".fileupload").each(function(e,g){e=jQuery(g);var f=U(e);if(g.files!==undefined&&g.files!==null&&g.files.length>0&&g.files[0]instanceof File&&g.files[0].name.length>0)w.push({file:g.files[0],
uploadIndex:f,form:a});else{g=e.val();g!==undefined&&g.length>0&&c++}if(w.length>0){e.after('<input type="text" name="tx_fileman_filelist[files][file][i'+f+'][fileUri]" readonly="readonly" class="fileupload fill-'+f+'" value="" /><input type="hidden" name="tx_fileman_filelist[tmpFiles][i'+f+']" class="tmpfile fill-'+f+'" value="" />');e.remove()}});if(w.length>0){d.hide();s=k.find("#fileman-uploadProgress"+b);s.show();b=w.shift();J(b.file,b.uploadIndex,b.form)}else if(c>0){I=true;d.submit()}}function J(a,
b,c){var d=new FileReader,e=new XMLHttpRequest,g=0,f=D,j=t*1.33,m=0,p=a.name;d.onload=function(h){e.open("PUT","/typo3conf/ext/fileman/Resources/Public/Scripts/FileTransfer.php?filename="+p+"&state="+m+"&no_cache="+Math.random());e.setRequestHeader("Content-Type","application/octet-stream");e.responseType="json";e.send(h.target.result)};d.onerror=function(){console.log("ERROR: Could not read file")};e.addEventListener("load",function(h){m=1;if(h.target.response){h=h.target.response;if(typeof h!==
"object")h=JSON.parse(h);v=="1"&&console.log(h);if(h.success&&h.success===1){if(h.tmp_name)p=h.tmp_name;if(f<a.size){g=f;f+=D;if(f>a.size)f=a.size;d.readAsDataURL(a.slice(g,f))}else{h=jQuery(c);h.find(".tmpfile.fill-"+b).val(p);h.find(".fileupload.fill-"+b).val(a.name);var l=w.shift();if(l===undefined){I=true;h.submit()}else J(l.file,l.uploadIndex,l.form)}}else{console.log("ERROR: File transfer failure");G("###ERROR_FILE_TRANSFER###",s)}}else{console.log("ERROR: No valid XHR response");G("###ERROR_XHR_RESPONSE###",
s);v=="1"&&console.log(h)}},false);e.addEventListener("error",function(){console.log("ERROR: No connection, retrying in 30 seconds");s.find(".progressvalue").text("###XHR_RETRY###");setTimeout(function(){J(a,b,c)},3E4)},false);e.upload.addEventListener("progress",function(h){var l=s.find(".progressvalue"),V=s.find(".progressbar");if(l[0]&&V[0])if(h.lengthComputable){var X=W+h.loaded,q=X/j*100;if(q>100)q=100;v=="1"&&console.log(q);V.css({width:q+"%"});q=parseInt(q);q===100?l.text(C+" 99%"):l.text(C+
" "+q+"% ("+A(X/1.33)+" / "+Q+")");if(h.loaded===h.total)W+=h.total}else v=="1"&&l.html("###XHR_NO_PROGRESS###")},false);if(f>a.size)f=a.size;d.readAsDataURL(a.slice(g,f))}function Y(a,b,c,d){c=jQuery(c);if(a.fileCount>1&&!c.hasClass("disabled")){a.fileCount==n&&b.removeClass("disabled");a.fileCount--;b=c.parents(".file-entry");c=b.find("input[type=file].fileupload");c.val(null);c.change();b.remove();a.lastIndex=Z(d);a.fileCount==1&&jQuery(d).find("a.del-file-entry").addClass("disabled")}}function Z(a){return U(jQuery(a).find(".fileupload:last"))}
function U(a){return a.attr("name").match(/\[file\]\[i([0-9]+)\]/i)[1]}function $(a,b){a.slideToggle();jQuery(b).toggleClass("expanded")}function aa(a){if(E){a.hide();E=false}}function ja(a,b){var c=a.dataTransfer,d=[];if(c.items)for(a=0;a<c.items.length;a++)c.items[a].kind=="file"&&d.push(c.items[a].getAsFile());else d=c.files;if(d.length>0){b=jQuery(b);a=b.find(".file-entry");c=a.first().find(".fileupload");if(c.hasClass("file-checker-error")){c.removeClass("f3-form-error file-checker-error");c.parent("label").prev(".typo3-messages").remove()}if(!y(c,
d.length>n,"Max file count is "+n+", tried uploading "+d.length,"###VALID_FAIL_FILECOUNT###".replace("{maxFileCount}",n)))return false;o={};if(!O(d,c,"dragNdrop")){o={};return false}if(d.length!==a.length)if(d.length>a.length){c=d.length-a.length;var e=b.find("a.add-file-entry");for(a=0;a<c;a++)e.click()}else a.slice((a.length-d.length)*-1).remove();b.find(".file-entry").each(function(g,f){f=jQuery(".fileupload",f);var j=jQuery('<span class="fileupload" name="'+f.attr("name")+'">'+d[g].name+"</span>");
j[0].files=[d[g]];f.replaceWith(j)});b.submit()}}function ba(a,b){var c=new XMLHttpRequest;c.open("HEAD","index.php?id="+ca+"&type="+da+"&tx_fileman_filelist[controller]=Category&tx_fileman_filelist[action]=ajaxVerifyToken&tx_fileman_filelist[encodedUrl]="+b,false);c.setRequestHeader("innologi--stoken",a);c.send()}var k=jQuery(".tx-fileman"),i=k.find(".search-form .searchbox");if(i[0]){var ka=i.width(),r=i[0].value.trim();i.val().length>0&&i.css({width:"85%"});i.on("focus",function(){this.value.length<
1&&jQuery(this).animate({width:"85%"})});i.on("blur",function(){this.value.length<1&&jQuery(this).animate({width:ka})});if(r.length>0){r=r.split(" ");for(var K in r)r.hasOwnProperty(K)&&r[K].length<1&&r.splice(K,1);var la=new RegExp("("+r.join("|")+")(?![^<]*>|[^<>]</)","ig");k.find("table.tx_fileman").each(function(a,b){a=jQuery(b);a.html(a.html().replace(la,'<span class="search-match">$1</span>'))})}}i=k.find(".rel-switch");i.click(function(){jQuery(this).next(".tx-fileman .rel-links").slideToggle();
return false});i.show();k.find(".rel-links").hide();k.find(".file-entry").each(function(a,b){N(b)});var L=""+(new Date).getTime()+Math.random();L=L.replace(".","");var S={},C="###SENDING_FILE###",v="###DEBUG###",u="###UPLOADPROGRESS###",H="###UPLOADTYPE###",w=[],T=false,I=false,P="###ALLOW_MIMETYPE###",z=parseInt("###MAX_FILESIZE###"),B=parseInt("###MAX_TOTAL_FILESIZE###"),o={},t=0,Q="",W=0,D=parseInt("###CHUNKSIZE###"),s=null;if(D<1)D=1048576;if(window.File){k.find("form").on("change","input[type=file].fileupload",
function(){var a=jQuery(this);if(!O(this.files,a,a.attr("name")))return false});window.FileReader&&ha()}if(H==="js"||u!="none")k.find(".init-progressbar").each(function(a,b){a++;var c=jQuery(b);c.after('<div id="fileman-uploadProgress'+a+'" class="uploadprogress"><div class="progressbar"></div><div class="progressvalue"></div></div>');if(H!=="js"){var d=a+L;if(u=="session")c.prepend('<input type="hidden" name="###SES_FIELD_NAME###" value="'+d+'" />');else if(u=="apc")c.prepend('<input type="hidden" name="###APC_FIELD_NAME###" value="'+
d+'" />');else u=="uploadprogress"&&c.prepend('<input type="hidden" name="UPLOAD_IDENTIFIER" value="'+d+'" />');c.on("submit",function(){var e=c.find("input[type=file].fileupload").val();if(e!==undefined&&e!==""){c.hide();k.find("#fileman-uploadProgress"+a).show();S[a]=setInterval(function(){R(d,a)},100);R(d,a)}})}});var n=parseInt("###MAX_FILE_UPLOADS###");n>1&&k.find(".multi-file").each(function(a,b){var c=jQuery(b);a=c.find(".file-entry");var d={fileCount:a.size(),lastIndex:Z(b)};c.find(".submit").before('<a href="#" class="add-file-entry" title="###ADD_FILE###">###ADD_FILE###</a><a href="#" class="del-file-entry" title="###DEL_FILE###">###DEL_FILE###</a>');
var e=c.find("a.add-file-entry"),g=e.next("a.del-file-entry");g.remove();d.fileCount==n&&e.addClass("disabled");d.fileCount==1&&g.addClass("disabled");c.hasClass("multi-file-add")||e.hide();a.each(function(f,j){f=jQuery(j);j=f.find(".fileupload");var m=jQuery(g.clone());m.insertAfter(j);m.click(function(){Y(d,e,this,b);return false});var p=f.find(".optional");p.hide().addClass("indent");j.after('<a href="#" class="show-optional" title="###SHOW_OPTIONAL###">###SHOW_OPTIONAL###</a>');f.find(".show-optional").click(function(){$(p,
this);return false})});if(c.hasClass("multi-file-add"))e.click(function(){var f=jQuery(this);if(d.fileCount<n&&!f.hasClass("disabled")){d.fileCount==1&&c.find("a.del-file-entry").removeClass("disabled");d.fileCount++;var j=f.prevAll(".file-entry:first").clone(),m=jQuery(j),p="[file][i"+d.lastIndex+"]";replaceName="[file][i"+ ++d.lastIndex+"]";m.find("input[type=file],input[type=text],textarea").each(function(h,l){h=jQuery(l);h.attr("name",h.attr("name").replace(p,replaceName));h.val(null)});m.find(".show-optional").click(function(){$(m.find(".optional"),
this);return false});N(j);m.find("a.del-file-entry").click(function(){Y(d,e,this,b);return false});f.before(j);d.fileCount==n&&e.addClass("disabled")}return false});else n=1});var E=false;if(T&&"draggable"in document.createElement("span")){i=k.find(".drop-zone");if(i.length>0){i.prepend('<div class="drop-overlay"></div><div class="drop-here" title="###DROP_ZONE_TOOLTIP###">###DROP_ZONE###</div>');i.on("drop",function(a){a.originalEvent.preventDefault();var b=jQuery(".drop-overlay",this);b.toggleClass("loading");
if(!ja(a.originalEvent,this)){aa(b);b.toggleClass("loading")}});i.on("dragover",function(a){a.originalEvent.preventDefault()});i.on("dragenter",function(a){a.originalEvent.preventDefault();a.originalEvent.stopPropagation();if(!E){jQuery(".drop-overlay",this).show();E=true}});i.find(".drop-overlay").on("dragleave",function(a){a.originalEvent.preventDefault();a.originalEvent.stopPropagation();aa(jQuery(this))})}}var x=k.find("a.csrf-protect"),F=k.find("form.csrf-protect"),da="###XHR_PAGETYPE###",ca=
"###XHR_PAGEID###";if(x[0]||F[0]){var ea=F.find(":submit"),M=[];ea.hide();x.hide();x.each(function(a,b){M.push(jQuery(b).attr("data-utoken"))});F.each(function(a,b){M.push(jQuery(b).attr("data-utoken"))});i=new XMLHttpRequest;i.open("HEAD","index.php?id="+ca+"&type="+da+"&tx_fileman_filelist[controller]=Category&tx_fileman_filelist[action]=ajaxGenerateTokens",true);i.setRequestHeader("innologi--utoken",M);i.onload=function(){if(this.status==200){var a=this.getResponseHeader("innologi__stoken"),b=
0;if(a!==null){a=a.split(",");x.each(function(c,d){var e=jQuery(d);e.attr("data-stoken",a[b++]);e.click(function(){ba(e.attr("data-stoken"),e.attr("data-utoken"))})});F.each(function(c,d){var e=jQuery(d);e.attr("data-stoken",a[b++]);e.submit(function(){ba(e.attr("data-stoken"),e.attr("data-utoken"))})})}}ea.show();x.show()};i.send()}});