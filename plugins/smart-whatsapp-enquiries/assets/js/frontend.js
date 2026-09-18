document.addEventListener('DOMContentLoaded', function() {
	var floatingBtn = document.querySelector('.swe-btn-floating');
	var modal = document.querySelector('.swe-modal');
	var toast = document.querySelector('.swe-toast');
	var closeBtns = document.querySelectorAll('.swe-modal-close');
	var toastCloseBtn = document.querySelector('.swe-toast-close');
	var backBtn = document.querySelector('.swe-modal-back');
	var viewSubjects = document.querySelector('.swe-view-subjects');
	var viewMessage = document.querySelector('.swe-view-message');
	var subjectCards = document.querySelectorAll('.swe-subject-card');
	var btnContinue = document.getElementById('swe-btn-continue');
	var msgTextarea = document.getElementById('swe-message-textarea');
	var msgStatic = document.getElementById('swe-message-static');
	var previewTitle = document.getElementById('swe-preview-title');

	var currentNumber = '';
	var currentMessage = '';

	// Parse variables passed from PHP
	var data = typeof sweData !== 'undefined' ? sweData : { global_number: '', allow_editing: true };

	// Toast Logic
	if (toast) {
		setTimeout(function() {
			toast.classList.add('active');
		}, 2000); // Show toast after 2s
		
		if (toastCloseBtn) {
			toastCloseBtn.addEventListener('click', function() {
				toast.classList.remove('active');
			});
		}
	}

	// Modal Toggle Logic
	if (floatingBtn && modal) {
		floatingBtn.addEventListener('click', function() {
			if (toast) toast.classList.remove('active');
			modal.classList.toggle('active');
			if (modal.classList.contains('active')) {
				resetViews();
			}
		});

		closeBtns.forEach(function(btn) {
			btn.addEventListener('click', function() {
				modal.classList.remove('active');
			});
		});

		// Close on ESC
		document.addEventListener('keydown', function(e) {
			if (e.key === 'Escape' && modal.classList.contains('active')) {
				modal.classList.remove('active');
				floatingBtn.focus();
			}
		});
	}

	// Back Button
	if (backBtn) {
		backBtn.addEventListener('click', function() {
			resetViews();
		});
	}

	function resetViews() {
		viewMessage.classList.remove('active');
		viewSubjects.classList.add('active');
	}

	function parseVariables(text, subjectTitle) {
		if (!text) return '';
		var context = data.page_context ? '\n\nI am currently viewing:\n' + data.page_title + '\n' + data.page_url : '';
		
		return text
			.replace(/{business_name}/g, data.business_name || '')
			.replace(/{subject}/g, subjectTitle || '')
			.replace(/{page_title}/g, data.page_title || '')
			.replace(/{page_url}/g, data.page_url || '')
			.replace(/{site_name}/g, data.site_name || '')
			+ context;
	}

	// Subject Card Click Logic
	subjectCards.forEach(function(card) {
		card.addEventListener('click', function() {
			var rawMsg = card.getAttribute('data-msg');
			var num = card.getAttribute('data-num');
			var title = card.getAttribute('data-title');
			
			currentNumber = num ? num : data.global_number;
			currentMessage = parseVariables(rawMsg, title);
			
			if (previewTitle) previewTitle.textContent = title;
			
			if (msgTextarea) {
				msgTextarea.value = currentMessage;
			} else if (msgStatic) {
				msgStatic.textContent = currentMessage;
			}

			viewSubjects.classList.remove('active');
			viewMessage.classList.add('active');
		});
	});

	// Continue to WhatsApp
	if (btnContinue) {
		btnContinue.addEventListener('click', function() {
			if (!currentNumber) {
				alert('WhatsApp number is not configured properly.');
				return;
			}
			
			// Get latest message in case user edited it
			var finalMessage = currentMessage;
			if (msgTextarea) {
				finalMessage = msgTextarea.value;
			}
			
			var cleanNumber = currentNumber.replace(/[^0-9]/g, '');
			var url = 'https://wa.me/' + cleanNumber + '?text=' + encodeURIComponent(finalMessage);
			
			window.open(url, '_blank', 'noopener,noreferrer');
			modal.classList.remove('active');
		});
	}
});
