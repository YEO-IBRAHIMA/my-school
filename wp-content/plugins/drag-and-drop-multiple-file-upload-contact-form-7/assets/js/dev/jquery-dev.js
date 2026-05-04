/**
 * CodeDropz Uploader
 * Copyright 2018 Glen Mongaya
 * CodeDrop Drag&Drop Uploader
 * @version 1.3.9.7
 * @author CodeDropz, Glen Don L. Mongaya
 * @license The MIT License (MIT)
 */

// New: Native helper – find elements within a context (replaces $(selector, ctx))
function _find( ctx, selector ) {
	if ( ! ctx ) return [];
	if ( typeof ctx === 'string' ) ctx = document.querySelector( ctx );
	return Array.from( ( ctx || document ).querySelectorAll( selector ) );
}

// New: Native helper – get a single element
function _findOne( ctx, selector ) {
	if ( ! ctx ) return null;
	if ( typeof ctx === 'string' ) ctx = document.querySelector( ctx );
	return ( ctx || document ).querySelector( selector );
}

// New: Native helper – add class to element
function _addClass( el, cls ) {
	if ( el && cls ) el.classList.add( cls );
}

// New: Native helper – remove class from element
function _removeClass( el, cls ) {
	if ( el && cls ) el.classList.remove( cls );
}

// New: Native helper – check if element has class
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
// Converted from jQuery plugin to native JS IIFE. Exposes CodeDropz_Uploader as a function.
(function () {

	/**
	 * Main uploader initializer – replaces $.fn.CodeDropz_Uploader
	 * @param {HTMLElement|NodeList|string} target  – input element(s) to enhance
	 * @param {Object}                      settings – options object
	 */
	window.CodeDropz_Uploader = function ( target, settings ) {

		// Generate & check nonce
        const form = document.querySelector('form.wpcf7-form');
        if( form ) {
            const data = new FormData();
            data.append('action', '_wpcf7_check_nonce');
            data.append('_ajax_nonce', dnd_cf7_uploader.ajax_nonce );
            fetch(dnd_cf7_uploader.ajax_url, { method: 'POST', body: data })
            .then(res => res.json())
            .then(({ data, success }) => success && (dnd_cf7_uploader.ajax_nonce = data))
            .catch(console.error)
		}

		// Resolve target to an array of elements
		var elements;
		if ( target instanceof HTMLElement ) {
			elements = [ target ];
		} else if ( target instanceof NodeList || Array.isArray( target ) ) {
			elements = Array.from( target );
		} else if ( typeof target === 'string' ) {
			elements = Array.from( document.querySelectorAll( target ) );
		} else {
			elements = [];
		}

		// Support multiple elements
		elements.forEach( function ( inputEl ) {

			// Parent input file type
			var input = inputEl; // Added: renamed from jQuery object to native element reference

			// Extends options
			var options = Object.assign({
				handler         : input,
				color           : '#000',
				background      : '',
				server_max_error: 'Uploaded file exceeds the maximum upload size of your server.',
				max_file        : input.dataset.max   ? input.dataset.max   : 10, // default 10
				max_upload_size : input.dataset.limit ? input.dataset.limit : '10485760', // should be a bytes it's (5MB)
				supported_type  : input.dataset.type  ? input.dataset.type  : 'jpg|jpeg|JPG|png|gif|pdf|doc|docx|ppt|pptx|odt|avi|ogg|m4a|mov|mp3|mp4|mpg|wav|wmv|xls',
				text            : 'Drag & Drop Files Here',
				separator       : 'or',
				button_text     : 'Browse Files',
				on_success      : ''
			}, settings );

			// Generate random string
			const generateRandomFolder = function ( length = 20 ) {
				const bytes = new Uint8Array(16);
				crypto.getRandomValues(bytes);
				bytes[6] = (bytes[6] & 0x0f) | 0x40; // version 4
				bytes[8] = (bytes[8] & 0x3f) | 0x80; // variant 10
				const hex = Array.from(bytes, b => b.toString(16).padStart(2, '0')).join('');
				return hex.replace(/^(.{8})(.{4})(.{4})(.{4})(.{12})$/, '$1-$2-$3-$4-$5');
			}

			// Get storage name
			var dataStorageName = input.dataset.name + '_count_files';

			// File Counter
			localStorage.setItem( dataStorageName, 1);

			// Get unique id from local storage.
			var sessionID   = dnd_upload_cf7_unique_id();
			var folderToken = sessionID ? localStorage.getItem( 'dnd_cf7_token_' + sessionID ) : null;

			// Unique upload session_id & token
			if ( ! sessionID || ! folderToken ) {
				sessionID   = generateRandomFolder();
				folderToken = generateRandomFolder(); // Generate folder token if not exists.
				localStorage.setItem( 'dnd_wpcf7_session_id', JSON.stringify({ value: sessionID, savedAt: Date.now() }) );
				localStorage.setItem( 'dnd_cf7_token_' + sessionID, folderToken );
			}

			// Guard – dnd_cf7_uploader must be defined before rendering template
			if ( typeof dnd_cf7_uploader === 'undefined' || ! dnd_cf7_uploader.drag_n_drop_upload ) {
				console.error( 'CodeDropz Uploader: dnd_cf7_uploader is not defined.' );
				return;
			}

			// Template Container
			// used _escapeHtml on text/separator/button_text to prevent XSS
			var cdropz_template = '<div class="codedropz-upload-handler">'
				+ '<div class="codedropz-upload-container">'
					+ '<div class="codedropz-upload-inner">'
						+ '<'+ dnd_cf7_uploader.drag_n_drop_upload.tag +'>'+ _escapeHtml( options.text ) +'</'+ dnd_cf7_uploader.drag_n_drop_upload.tag +'>'
						+ '<span>'+ _escapeHtml( options.separator ) +'</span>'
						+'<div class="codedropz-btn-wrap"><a class="cd-upload-btn" href="#">'+ _escapeHtml( options.button_text ) +'</a></div>'
						+'</div>'
					+ '</div>'
					+ '<span class="dnd-upload-counter"><span>0</span> '+ dnd_cf7_uploader.dnd_text_counter +' '+ parseInt( options.max_file ) +'</span>'
				+ '</div>';

			// Wrap input fields
			var wrapper = document.createElement( 'div' );
			wrapper.className = 'codedropz-upload-wrapper';
			input.parentNode.insertBefore( wrapper, input );
			wrapper.appendChild( input );

			// Remove special character
			options.supported_type = options.supported_type.replace(/[^a-zA-Z0-9| ]/g, "");

			// Element Handler
			// native equivalents for parents('form') and parents('.codedropz-upload-wrapper')
			var form_handler     = input.closest( 'form' ),
				options_handler  = input.closest( '.codedropz-upload-wrapper' ),
				btnOBJ           = form_handler ? _find( form_handler, 'input[type="submit"], button[type="submit"]' ) : [];

			// Append Format
			_insertAfter( input, cdropz_template );

			// re-query options_handler after DOM mutation
			options_handler = input.closest( '.codedropz-upload-wrapper' );

			// preventing the unwanted behaviours
			var uploadHandler = _findOne( options_handler, '.codedropz-upload-handler' );
			if ( uploadHandler ) {
				[ 'drag', 'dragstart', 'dragend', 'dragover', 'dragenter', 'dragleave', 'drop' ].forEach( function ( evtName ) {
					uploadHandler.addEventListener( evtName, function ( e ) {
						e.preventDefault();
						e.stopPropagation();
					});
				});

				// dragover and dragenter - add class
				[ 'dragover', 'dragenter' ].forEach( function ( evtName ) {
					uploadHandler.addEventListener( evtName, function () {
						_addClass( this, 'codedropz-dragover' );
					});
				});

				// dragleave dragend drop - remove class
				[ 'dragleave', 'dragend', 'drop' ].forEach( function ( evtName ) {
					uploadHandler.addEventListener( evtName, function () {
						_removeClass( this, 'codedropz-dragover' );
					});
				});

				// when dropping files
				uploadHandler.addEventListener( 'drop', function ( event ) {
					// Run the uploader
					DND_Setup_Uploader( event.dataTransfer.files, 'drop' );
				});
			}

			// Browse button clicked
			var cdUploadBtn = _findOne( options_handler, 'a.cd-upload-btn' );
			if ( cdUploadBtn ) {
				cdUploadBtn.addEventListener( 'click', function ( e ) {
					// stops the default action of an element from happening
					e.preventDefault();

					// Reset value
					input.value = null;

					// Click input type[file] element
					input.click();
				});
			}

			// Trigger when input type[file] is click/changed
			input.addEventListener( 'change', function ( e ) {
				// Run the uploader
				DND_Setup_Uploader( this.files, 'click' );
			});

            // Remove accept attribute on mobile devices
            if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {
                input.removeAttribute( 'accept' );
            }

			// Add random or unique string
			input.setAttribute( 'data-random-id', sessionID );

			// Setup Uploader
			var DND_Setup_Uploader = function ( files, action ) {

				// make sure we have files
				if ( ! files || files.length < 1 ) {
					return;
				}

				// gathering the form data
				var formData = new FormData();

				// Append file
				//formData.append('supported_type', options.supported_type ); @note : removed due Vulnerability
				formData.append( 'action', 'dnd_codedropz_upload' );
				formData.append( 'type', action );
				formData.append( 'security', dnd_cf7_uploader.ajax_nonce );

				// CF7 - upload field name & cf7 id
				formData.append( 'form_id', input.dataset.id );
				formData.append( 'upload_name', input.dataset.name );
				formData.append( 'upload_folder', sessionID );
				formData.append( 'token', folderToken ); // append folder token.

				// remove has error
				_find( options_handler, 'span.has-error' ).forEach( function ( el ) { el.remove(); });

				// Loop files
				Array.from( files ).forEach( function ( file, i ) {

					// Reset upload file type
					if( typeof formData.delete !== 'undefined' ) {
						formData.delete('upload-file');
					}

					// Limit file upload
					if( localStorage.getItem( dataStorageName ) > options.max_file ) {
						if( ! _findOne( options_handler, 'span.has-error-msg' ) ) {
							var err_msg = dnd_cf7_uploader.drag_n_drop_upload.max_file_limit;
							var uploadHandlerEl = _findOne( options_handler, '.codedropz-upload-handler' );
							if ( uploadHandlerEl ) {
								_insertAfter( uploadHandlerEl, '<span class="has-error-msg">'+ err_msg.replace('%count%', options.max_file ) +'</span>' );
							}
						}
						return;
					}

					// Create progress bar
					var progressBarID = CodeDropz_Object.createProgressBar( file ),
						has_error = false;

					// File size limit - validation
					if( file.size > options.max_upload_size ) {
						var sizeErrEl = _findOne( document.getElementById( progressBarID ), '.dnd-upload-details' );
						if ( sizeErrEl ) sizeErrEl.insertAdjacentHTML( 'beforeend', '<span class="has-error">'+ dnd_cf7_uploader.drag_n_drop_upload.large_file +'</span>' );
						has_error = true;
					}

					// Validate file type
					var regex_type = new RegExp("(.*?)\.("+options.supported_type+")$");
					if ( has_error === false && !( regex_type.test( file.name.toLowerCase() ) ) ) {
						var typeErrEl = _findOne( document.getElementById( progressBarID ), '.dnd-upload-details' );
						if ( typeErrEl ) typeErrEl.insertAdjacentHTML( 'beforeend', '<span class="has-error">'+ dnd_cf7_uploader.drag_n_drop_upload.inavalid_type +'</span>' );
						has_error = true;
					}

					// Increment count
					localStorage.setItem( dataStorageName, ( Number( localStorage.getItem( dataStorageName ) ) + 1 ) );

					// Make sure there's no error
					if( has_error === false ) {

						// Append file
						formData.append('upload-file', file );

						// XMLHttpRequest for progress tracking
						var xhr = new XMLHttpRequest();

						//objects to interact with servers.
						// reference : https://stackoverflow.com/questions/15410265/file-upload-progress-bar-with-jquery
						xhr.upload.addEventListener( 'progress', function ( event ) {
							if ( event.lengthComputable ) {
								var percentComplete = ( event.loaded / event.total );
								var percentage = parseInt( percentComplete * 100 );

								// Progress Loading
								CodeDropz_Object.setProgressBar( progressBarID, percentage - 1 );
							}
						}, false );

						xhr.addEventListener( 'load', function () {
							// Set progress bar to 100%
							CodeDropz_Object.setProgressBar( progressBarID, 100 );

							// guard against non-JSON response
							var response;
							try {
								response = JSON.parse( xhr.responseText );
							} catch ( e ) {
								var errEl = _findOne( document.getElementById( progressBarID ), '.dnd-progress-bar' );
								if ( errEl ) errEl.remove();
								var detailsEl = _findOne( document.getElementById( progressBarID ), '.dnd-upload-details' );
								if ( detailsEl ) detailsEl.insertAdjacentHTML( 'beforeend', '<span class="has-error">'+ options.server_max_error +'</span>' );
								btnOBJ.forEach( function ( btn ) { _removeClass( btn, 'disabled' ); btn.disabled = false; });
								_removeClass( document.getElementById( progressBarID ), 'in-progress' );
								return;
							}

							if ( response.success ) {

                                CodeDropz_Object.setProgressBar( progressBarID, 100 );

								// Callback on success
								if ( typeof options.on_success === 'function' ) {
									options.on_success.call( this, input, progressBarID, response );
								}

							} else {
								var pbEl = _findOne( document.getElementById( progressBarID ), '.dnd-progress-bar' );
								if ( pbEl ) pbEl.remove();
								var dEl = _findOne( document.getElementById( progressBarID ), '.dnd-upload-details' );
								if ( dEl ) dEl.insertAdjacentHTML( 'beforeend', '<span class="has-error">'+ response.data +'</span>' );
								btnOBJ.forEach( function ( btn ) { _removeClass( btn, 'disabled' ); btn.disabled = false; });
                                _removeClass( document.getElementById( progressBarID ), 'in-progress' );
							}
						});

						xhr.addEventListener( 'error', function () {
							var pbEl2 = _findOne( document.getElementById( progressBarID ), '.dnd-progress-bar' );
							if ( pbEl2 ) pbEl2.remove();
							var dEl2 = _findOne( document.getElementById( progressBarID ), '.dnd-upload-details' );
							if ( dEl2 ) dEl2.insertAdjacentHTML( 'beforeend', '<span class="has-error">'+ options.server_max_error +'</span>' );
							btnOBJ.forEach( function ( btn ) { _removeClass( btn, 'disabled' ); btn.disabled = false; });
                            _removeClass( document.getElementById( progressBarID ), 'in-progress' );
						});

						// determine method from form; fallback to POST
						var formMethod = ( form_handler && form_handler.getAttribute( 'method' ) ) ? form_handler.getAttribute( 'method' ).toUpperCase() : 'POST';
						xhr.open( formMethod, options.ajax_url, true );
						xhr.send( formData );
					}
				}); // end forEach files

			}

			// CodeDropz object and functions
			var CodeDropz_Object = {

				// Create progress bar
				createProgressBar : function( file ) {

					// Setup progress bar variable
					var upload_handler = _findOne( options_handler, '.codedropz-upload-handler' ),
						generated_ID   = 'dnd-file-' + Math.random().toString(36).substr(2, 9);

					// Setup progressbar elements
					var fileDetails = '<div class="dnd-upload-image"><span class="file"></span></div>'
					+ '<div class="dnd-upload-details">'
						+ '<span class="name"><span>'+ _escapeHtml( file.name ) +'</span><em>('+ CodeDropz_Object.bytesToSize( file.size ) +')</em></span>'
						+ '<a href="#" title="'+ dnd_cf7_uploader.drag_n_drop_upload.delete.title +'" class="remove-file" data-storage="'+ dataStorageName +'"><span class="dnd-icon-remove"></span></a>'
						+ '<span class="dnd-progress-bar"><span></span></span>'
					+ '</div>';

					// Append Status Bar
					if ( upload_handler ) {
						_insertAfter( upload_handler, '<div id="'+ generated_ID +'" class="dnd-upload-status">'+ fileDetails +'</div>' );
					}

					return generated_ID;
				},

				// Process progressbar ( Animate progress )
				setProgressBar : function( statusbar, percent ) {
					var statusBarEl = document.getElementById( statusbar );
					var statusBar   = statusBarEl ? _findOne( statusBarEl, '.dnd-progress-bar' ) : null;
					if( statusBar ) {
						// Disable submit button
                        if( btnOBJ && btnOBJ.length > 0 ){
					        CodeDropz_Object.disableBtn( btnOBJ );
                        }

						// Compute Progress bar
						var progress_width = ( percent * statusBar.offsetWidth / 100);

                        // Set status bar in-progress
                        _addClass( statusBarEl, 'in-progress' );

						var spanEl = statusBar.querySelector( 'span' );
						if ( spanEl ) {
							if( percent == 100 ) {
								spanEl.style.width = '100%';
								spanEl.textContent = percent + '% ';
							} else {
								spanEl.style.width = progress_width + 'px';
								spanEl.textContent = percent + '% ';
							}
						}

						if( percent == 100 ) {
					        _addClass( statusBarEl, 'complete' );
					        _removeClass( statusBarEl, 'in-progress' );
						}
					}
					return false;
				},

				// Size Conversion
				bytesToSize : function( bytes ) {

					if( bytes === 0 )
						return '0';

					var kBytes   = (bytes / 1024);
					var fileSize = ( kBytes >= 1024 ? ( kBytes / 1024 ).toFixed(2) + 'MB' : kBytes.toFixed(2) + 'KB' );

					return fileSize;
				},

				// Disable button
				disableBtn : function( BtnOJB ) {
					if( BtnOJB && BtnOJB.length > 0 ) {
						BtnOJB.forEach( function ( btn ) {
							_addClass( btn, 'disabled' );
							btn.disabled = true;
						});
					}
				}
			};
		}); // end forEach

		// Remove File
		document.addEventListener( 'click', function ( e ) {
			var removeIcon = e.target.closest( '.dnd-icon-remove' );
			if ( ! removeIcon ) return;

			e.preventDefault();
			var _self             = removeIcon,
				_dnd_status       = _self.closest( '.dnd-upload-status' ),
				_parent_wrap      = _self.closest( '.codedropz-upload-wrapper' ),
				removeStorageData = _self.closest( 'a' ) ? _self.closest( 'a' ).getAttribute( 'data-storage' ) : null,
				storageCount      = Number( localStorage.getItem( removeStorageData ) ),
				sessionId         = dnd_upload_cf7_unique_id();

			// If file upload is in progress don't delete
			if( _hasClass( _dnd_status, 'in-progress' )) {
				return false;
			}

			// Direct remove the file if there's any error.
			if( _dnd_status && _findOne( _dnd_status, '.has-error' ) ) {
				_dnd_status.remove();
				localStorage.setItem( removeStorageData, ( storageCount - 1 ) );
				return false;
			}

			// Change text Status
			_addClass( _self, 'deleting' );
			_self.textContent = dnd_cf7_uploader.drag_n_drop_upload.delete.text + '...';

			// Request ajax image delete
			var delData = new URLSearchParams({
				path          : _dnd_status ? ( _dnd_status.querySelector( 'input[type="hidden"]' ) || {} ).value || '' : '',
				action        : 'dnd_codedropz_upload_delete',
				security      : dnd_cf7_uploader.ajax_nonce,
				upload_folder : sessionId,
				token         : localStorage.getItem( 'dnd_cf7_token_' + sessionId ) || '',
			});

			// replaced $.post with native fetch
			fetch( settings.ajax_url, {
				method  : 'POST',
				headers : { 'Content-Type': 'application/x-www-form-urlencoded' },
				body    : delData.toString()
			})
			.then( function ( res ) {
				if ( ! res.ok ) throw new Error( 'Network response was not ok' );
				return res.json();
			})
			.then( function ( response ) {
				if( response.success ) {

					// Reduce file count and status bar element.
					if ( _dnd_status ) _dnd_status.remove();
					localStorage.setItem( removeStorageData, ( storageCount - 1 ) );

					// Remove error msg
					if( _parent_wrap && _find( _parent_wrap, '.dnd-upload-status' ).length <= 1 ) {
						_find( _parent_wrap, 'span.has-error-msg' ).forEach( function ( el ) { el.remove(); });
					}

					// Update Counter
					var counter = _findOne( _parent_wrap, '.dnd-upload-counter span' );
					if ( counter ) counter.textContent = Number( localStorage.getItem( removeStorageData ) ) - 1;

				} else {
					var detailEl = _dnd_status ? _findOne( _dnd_status, '.dnd-upload-details' ) : null;
					if ( detailEl ) detailEl.insertAdjacentHTML( 'beforeend', '<span class="has-error-msg">'+ response.data +'</span>' );
				}
			})
			.catch( function ( err ) {
				console.error( 'CodeDropz delete error:', err );
			});

			// Clear message
			document.querySelectorAll( 'span.has-error-msg' ).forEach( function ( el ) { el.remove(); });

		}); // end remove-file click

	}; // end CodeDropz_Uploader

}()); // end IIFE
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
