/**
 * CodeDropz Uploader
 * Copyright 2018 Glen Mongaya
 * CodeDrop Drag&Drop Uploader
 * @version 1.3.9.7
 * @author CodeDropz, Glen Don L. Mongaya
 * @license The MIT License (MIT)
 */

// New: Native helper - find elements within a context (replaces $(selector, ctx))
function _find( ctx, selector ) {
	if ( ! ctx ) return [];
	if ( typeof ctx === 'string' ) ctx = document.querySelector( ctx );
	return Array.from( ( ctx || document ).querySelectorAll( selector ) );
}

// New: Native helper - get a single element
function _findOne( ctx, selector ) {
	if ( ! ctx ) return null;
	if ( typeof ctx === 'string' ) ctx = document.querySelector( ctx );
	return ( ctx || document ).querySelector( selector );
}

// New: Native helper - add class to element
function _addClass( el, cls ) {
	if ( el && cls ) el.classList.add( cls );
}

// New: Native helper - remove class from element
function _removeClass( el, cls ) {
	if ( el && cls ) el.classList.remove( cls );
}

// New: Native helper - check if element has class
function _hasClass( el, cls ) {
	return el ? el.classList.contains( cls ) : false;
}

// New: Native helper – safely append HTML after an element
function _insertAfter( referenceEl, html ) {
	if ( ! referenceEl || ! html ) return null;
	var template = document.createElement( 'div' );
	template.innerHTML = html;
	var node = template.firstChild;
	referenceEl.parentNode.insertBefore( node, referenceEl.nextSibling );
	return node;
}

// New: Native helper – safely escape HTML to prevent XSS in file names
function _escapeHtml( str ) {
	var div = document.createElement( 'div' );
	div.appendChild( document.createTextNode( String( str ) ) );
	return div.innerHTML;
}

// CodeDropz Drag and Drop Plugin
window.CodeDropz_Uploader=function(e,a){var r;let t=document.querySelector("form.wpcf7-form");if(t){let n=new FormData;n.append("action","_wpcf7_check_nonce"),n.append("_ajax_nonce",dnd_cf7_uploader.ajax_nonce),fetch(dnd_cf7_uploader.ajax_url,{method:"POST",body:n}).then(e=>e.json()).then(({data:e,success:a})=>a&&(dnd_cf7_uploader.ajax_nonce=e)).catch(console.error)}(r=e instanceof HTMLElement?[e]:e instanceof NodeList||Array.isArray(e)?Array.from(e):"string"==typeof e?Array.from(document.querySelectorAll(e)):[]).forEach(function(e){var r=e,t=Object.assign({handler:r,color:"#000",background:"",server_max_error:"Uploaded file exceeds the maximum upload size of your server.",max_file:r.dataset.max?r.dataset.max:10,max_upload_size:r.dataset.limit?r.dataset.limit:"10485760",supported_type:r.dataset.type?r.dataset.type:"jpg|jpeg|JPG|png|gif|pdf|doc|docx|ppt|pptx|odt|avi|ogg|m4a|mov|mp3|mp4|mpg|wav|wmv|xls",text:"Drag & Drop Files Here",separator:"or",button_text:"Browse Files",on_success:""},a);let n=function(e=20){let a=new Uint8Array(16);crypto.getRandomValues(a),a[6]=15&a[6]|64,a[8]=63&a[8]|128;let r=Array.from(a,e=>e.toString(16).padStart(2,"0")).join("");return r.replace(/^(.{8})(.{4})(.{4})(.{4})(.{12})$/,"$1-$2-$3-$4-$5")};var d=r.dataset.name+"_count_files";localStorage.setItem(d,1);var o=dnd_upload_cf7_unique_id(),s=o?localStorage.getItem("dnd_cf7_token_"+o):null;if(o&&s||(o=n(),s=n(),localStorage.setItem("dnd_wpcf7_session_id",JSON.stringify({value:o,savedAt:Date.now()})),localStorage.setItem("dnd_cf7_token_"+o,s)),"undefined"==typeof dnd_cf7_uploader||!dnd_cf7_uploader.drag_n_drop_upload){console.error("CodeDropz Uploader: dnd_cf7_uploader is not defined.");return}var p='<div class="codedropz-upload-handler"><div class="codedropz-upload-container"><div class="codedropz-upload-inner"><'+dnd_cf7_uploader.drag_n_drop_upload.tag+">"+_escapeHtml(t.text)+"</"+dnd_cf7_uploader.drag_n_drop_upload.tag+"><span>"+_escapeHtml(t.separator)+'</span><div class="codedropz-btn-wrap"><a class="cd-upload-btn" href="#">'+_escapeHtml(t.button_text)+'</a></div></div></div><span class="dnd-upload-counter"><span>0</span> '+dnd_cf7_uploader.dnd_text_counter+" "+parseInt(t.max_file)+"</span></div>",l=document.createElement("div");l.className="codedropz-upload-wrapper",r.parentNode.insertBefore(l,r),l.appendChild(r),t.supported_type=t.supported_type.replace(/[^a-zA-Z0-9| ]/g,"");var i=r.closest("form"),c=r.closest(".codedropz-upload-wrapper"),u=i?_find(i,'input[type="submit"], button[type="submit"]'):[];_insertAfter(r,p);var f=_findOne(c=r.closest(".codedropz-upload-wrapper"),".codedropz-upload-handler");f&&(["drag","dragstart","dragend","dragover","dragenter","dragleave","drop"].forEach(function(e){f.addEventListener(e,function(e){e.preventDefault(),e.stopPropagation()})}),["dragover","dragenter"].forEach(function(e){f.addEventListener(e,function(){_addClass(this,"codedropz-dragover")})}),["dragleave","dragend","drop"].forEach(function(e){f.addEventListener(e,function(){_removeClass(this,"codedropz-dragover")})}),f.addEventListener("drop",function(e){m(e.dataTransfer.files,"drop")}));var g=_findOne(c,"a.cd-upload-btn");g&&g.addEventListener("click",function(e){e.preventDefault(),r.value=null,r.click()}),r.addEventListener("change",function(e){m(this.files,"click")}),/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)&&r.removeAttribute("accept"),r.setAttribute("data-random-id",o);var m=function(e,a){if(e&&!(e.length<1)){var n=new FormData;n.append("action","dnd_codedropz_upload"),n.append("type",a),n.append("security",dnd_cf7_uploader.ajax_nonce),n.append("form_id",r.dataset.id),n.append("upload_name",r.dataset.name),n.append("upload_folder",o),n.append("token",s),_find(c,"span.has-error").forEach(function(e){e.remove()}),Array.from(e).forEach(function(e,a){if(void 0!==n.delete&&n.delete("upload-file"),localStorage.getItem(d)>t.max_file){if(!_findOne(c,"span.has-error-msg")){var o=dnd_cf7_uploader.drag_n_drop_upload.max_file_limit,s=_findOne(c,".codedropz-upload-handler");s&&_insertAfter(s,'<span class="has-error-msg">'+o.replace("%count%",t.max_file)+"</span>")}return}var p=v.createProgressBar(e),l=!1;if(e.size>t.max_upload_size){var f=_findOne(document.getElementById(p),".dnd-upload-details");f&&f.insertAdjacentHTML("beforeend",'<span class="has-error">'+dnd_cf7_uploader.drag_n_drop_upload.large_file+"</span>"),l=!0}var g=RegExp("(.*?).("+t.supported_type+")$");if(!1===l&&!g.test(e.name.toLowerCase())){var m=_findOne(document.getElementById(p),".dnd-upload-details");m&&m.insertAdjacentHTML("beforeend",'<span class="has-error">'+dnd_cf7_uploader.drag_n_drop_upload.inavalid_type+"</span>"),l=!0}if(localStorage.setItem(d,Number(localStorage.getItem(d))+1),!1===l){n.append("upload-file",e);var h=new XMLHttpRequest;h.upload.addEventListener("progress",function(e){if(e.lengthComputable){var a=parseInt(100*(e.loaded/e.total));v.setProgressBar(p,a-1)}},!1),h.addEventListener("load",function(){v.setProgressBar(p,100);try{a=JSON.parse(h.responseText)}catch(e){var a,n=_findOne(document.getElementById(p),".dnd-progress-bar");n&&n.remove();var d=_findOne(document.getElementById(p),".dnd-upload-details");d&&d.insertAdjacentHTML("beforeend",'<span class="has-error">'+t.server_max_error+"</span>"),u.forEach(function(e){_removeClass(e,"disabled"),e.disabled=!1}),_removeClass(document.getElementById(p),"in-progress");return}if(a.success)v.setProgressBar(p,100),"function"==typeof t.on_success&&t.on_success.call(this,r,p,a);else{var o=_findOne(document.getElementById(p),".dnd-progress-bar");o&&o.remove();var s=_findOne(document.getElementById(p),".dnd-upload-details");s&&s.insertAdjacentHTML("beforeend",'<span class="has-error">'+a.data+"</span>"),u.forEach(function(e){_removeClass(e,"disabled"),e.disabled=!1}),_removeClass(document.getElementById(p),"in-progress")}}),h.addEventListener("error",function(){var e=_findOne(document.getElementById(p),".dnd-progress-bar");e&&e.remove();var a=_findOne(document.getElementById(p),".dnd-upload-details");a&&a.insertAdjacentHTML("beforeend",'<span class="has-error">'+t.server_max_error+"</span>"),u.forEach(function(e){_removeClass(e,"disabled"),e.disabled=!1}),_removeClass(document.getElementById(p),"in-progress")});var y=i&&i.getAttribute("method")?i.getAttribute("method").toUpperCase():"POST";h.open(y,t.ajax_url,!0),h.send(n)}})}},v={createProgressBar:function(e){var a=_findOne(c,".codedropz-upload-handler"),r="dnd-file-"+Math.random().toString(36).substr(2,9),t='<div class="dnd-upload-image"><span class="file"></span></div><div class="dnd-upload-details"><span class="name"><span>'+_escapeHtml(e.name)+"</span><em>("+v.bytesToSize(e.size)+')</em></span><a href="#" title="'+dnd_cf7_uploader.drag_n_drop_upload.delete.title+'" class="remove-file" data-storage="'+d+'"><span class="dnd-icon-remove"></span></a><span class="dnd-progress-bar"><span></span></span></div>';return a&&_insertAfter(a,'<div id="'+r+'" class="dnd-upload-status">'+t+"</div>"),r},setProgressBar:function(e,a){var r=document.getElementById(e),t=r?_findOne(r,".dnd-progress-bar"):null;if(t){u&&u.length>0&&v.disableBtn(u);var n=a*t.offsetWidth/100;_addClass(r,"in-progress");var d=t.querySelector("span");d&&(100==a?(d.style.width="100%",d.textContent=a+"% "):(d.style.width=n+"px",d.textContent=a+"% ")),100==a&&(_addClass(r,"complete"),_removeClass(r,"in-progress"))}return!1},bytesToSize:function(e){if(0===e)return"0";var a=e/1024;return a>=1024?(a/1024).toFixed(2)+"MB":a.toFixed(2)+"KB"},disableBtn:function(e){e&&e.length>0&&e.forEach(function(e){_addClass(e,"disabled"),e.disabled=!0})}}}),document.addEventListener("click",function(e){var r=e.target.closest(".dnd-icon-remove");if(r){e.preventDefault();var t=r,n=t.closest(".dnd-upload-status"),d=t.closest(".codedropz-upload-wrapper"),o=t.closest("a")?t.closest("a").getAttribute("data-storage"):null,s=Number(localStorage.getItem(o)),p=dnd_upload_cf7_unique_id();if(_hasClass(n,"in-progress"))return!1;if(n&&_findOne(n,".has-error"))return n.remove(),localStorage.setItem(o,s-1),!1;_addClass(t,"deleting"),t.textContent=dnd_cf7_uploader.drag_n_drop_upload.delete.text+"...";var l=new URLSearchParams({path:n&&(n.querySelector('input[type="hidden"]')||{}).value||"",action:"dnd_codedropz_upload_delete",security:dnd_cf7_uploader.ajax_nonce,upload_folder:p,token:localStorage.getItem("dnd_cf7_token_"+p)||""});fetch(a.ajax_url,{method:"POST",headers:{"Content-Type":"application/x-www-form-urlencoded"},body:l.toString()}).then(function(e){if(!e.ok)throw Error("Network response was not ok");return e.json()}).then(function(e){if(e.success){n&&n.remove(),localStorage.setItem(o,s-1),d&&_find(d,".dnd-upload-status").length<=1&&_find(d,"span.has-error-msg").forEach(function(e){e.remove()});var a=_findOne(d,".dnd-upload-counter span");a&&(a.textContent=Number(localStorage.getItem(o))-1)}else{var r=n?_findOne(n,".dnd-upload-details"):null;r&&r.insertAdjacentHTML("beforeend",'<span class="has-error-msg">'+e.data+"</span>")}}).catch(function(e){console.error("CodeDropz delete error:",e)}),document.querySelectorAll("span.has-error-msg").forEach(function(e){e.remove()})}})};
// END: CodeDropz Uploader function

// Get unique id. (reset after 24hours)
function dnd_upload_cf7_unique_id() {
	const item = localStorage.getItem('dnd_wpcf7_session_id');
	if ( ! item ) {
		return null;
	}

	// Parse item
	var data;
	try {
		data = JSON.parse( item );
	} catch ( e ) {
		localStorage.removeItem( 'dnd_wpcf7_session_id' );
		return null;
	}

	// guard against missing savedAt
	if ( ! data || ! data.savedAt || ! data.value ) {
		localStorage.removeItem( 'dnd_wpcf7_session_id' );
		return null;
	}

	// Expired? then remove value from localstorage.
	if ( Date.now() - data.savedAt > ( 24 * 60 * 60 * 1000 ) ) {
		localStorage.removeItem('dnd_cf7_token_' + data.value ); // delete token
		localStorage.removeItem('dnd_wpcf7_session_id');         // delete session
		return null;
	}

	return data.value;
}

// Replaced jQuery(document).ready with native DOMContentLoaded
document.addEventListener( 'DOMContentLoaded', function () {

	// Custom event handler
    var dnd_upload_cf7_event = function( target, name, data ) {
        var event = new CustomEvent( 'dnd_upload_cf7_' + name, {
			bubbles : true,
			detail  : data
		});

		// target is now a native element; guard with instanceof check
		var el = ( target instanceof HTMLElement ) ? target : ( target && target[0] ? target[0] : null );
		if ( el ) el.dispatchEvent( event );
    }

	// Fires when an Ajax form submission has completed successfully, and mail has been sent.
	document.addEventListener( 'wpcf7mailsent', function( event ) {

		// Get input type file element
		var inputFiles = document.querySelectorAll( '.wpcf7-drag-n-drop-file' );
		var $form = inputFiles.length > 0 ? inputFiles[0].closest( 'form' ) : null;

		// Reset upload list for multiple fields
		if( inputFiles.length > 0 ) {
			Array.from( inputFiles ).forEach( function ( el ) {
				// Reset file counts
				localStorage.setItem( el.getAttribute( 'data-name' ) + '_count_files', 1 );
			});
		} else {
			// Reset file counts
			var singleInput = document.querySelector( '.wpcf7-drag-n-drop-file' );
			if ( singleInput && singleInput.dataset.name ) {
				localStorage.setItem( singleInput.dataset.name + '_count_files', 1 );
			}
		}

		// Remove status / progress bar
		if ( $form ) {
			$form.querySelectorAll( '.dnd-upload-status' ).forEach( function ( el ) { el.remove(); });
			var counter = $form.querySelector( '.dnd-upload-counter span' );
			if ( counter ) counter.textContent = '0';
			$form.querySelectorAll( 'span.has-error-msg' ).forEach( function ( el ) { el.remove(); });
		}

	}, false );

	window.initDragDrop = function () {

		// Get text object options/settings from localize script
		if ( typeof dnd_cf7_uploader === 'undefined' ) {
			console.error( 'CodeDropz Uploader: dnd_cf7_uploader localize script is missing.' );
			return;
		}

		var TextOJB = dnd_cf7_uploader.drag_n_drop_upload;

		// Support Multiple Fileds
		CodeDropz_Uploader( document.querySelectorAll( '.wpcf7-drag-n-drop-file' ), {
			'color'           : '#fff',
			'ajax_url'        : dnd_cf7_uploader.ajax_url,
			'text'            : TextOJB.text,
			'separator'       : TextOJB.or_separator,
			'button_text'     : TextOJB.browse,
			'server_max_error': TextOJB.server_max_error,
			'on_success'      : function( input, progressBar, response ){

				// Progressbar Object
				var $progressDetails = document.getElementById( progressBar );
				var $form            = input.closest( 'form' );
				var $span            = $form ? $form.querySelector( '.wpcf7-acceptance' ) : null;
				var $input           = $span ? $span.querySelector( 'input:checkbox' ) : null;

				// If it's complete remove disabled attribute in button
				if( ( $span && _hasClass( $span, 'optional' ) ) || ( $input && $input.checked ) || ! $span || ( $form && _hasClass( $form, 'wpcf7-acceptance-as-validation' ) ) )  {
					setTimeout( function(){
                        var submitBtns = $form ? $form.querySelectorAll( 'input[type="submit"], button[type="submit"]' ) : [];
                        submitBtns.forEach( function ( btn ) {
                            _removeClass( btn, 'disabled' );
                            btn.removeAttribute( 'disabled' );
                        });
                    }, 1 );
				}

				// Append hidden input field
				if ( $progressDetails ) {
					var detailsDiv = $progressDetails.querySelector( '.dnd-upload-details' );
					if ( detailsDiv ) {
						detailsDiv.insertAdjacentHTML( 'beforeend',
							'<span><input type="hidden" name="'+ input.getAttribute('data-name') +'[]" value="'+ response.data.path +'/'+ response.data.file +'"></span>'
						);
					}
				}

				// Update counter
				var $files_counter = ( Number( localStorage.getItem( input.dataset.name + '_count_files' ) ) - 1 );
				var counterEl = input.closest( '.codedropz-upload-wrapper' );
				if ( counterEl ) {
					var span = counterEl.querySelector( '.dnd-upload-counter span' );
					if ( span ) span.textContent = $files_counter;
				}

				// Js hook/event trigger after successful upload.
				dnd_upload_cf7_event( $progressDetails, 'success', response );
			}
		});

	}

	// Run uploader.
	window.initDragDrop();

	// Usage: Custom js hook after success upload
	document.addEventListener( 'dnd_upload_cf7_success', function( event ) {
		//console.log('success');
	});

});
