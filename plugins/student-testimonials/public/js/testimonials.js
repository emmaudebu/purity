document.addEventListener('DOMContentLoaded', function() {
	var triggers = document.querySelectorAll('.st-lightbox-trigger');
	if (triggers.length === 0) return;

	// Create lightbox HTML
	var lightbox = document.createElement('div');
	lightbox.className = 'st-lightbox';
	lightbox.innerHTML = '<span class="st-lightbox-close">&times;</span><img class="st-lightbox-img" src="">';
	document.body.appendChild(lightbox);

	var lightboxImg = lightbox.querySelector('.st-lightbox-img');
	var closeBtn = lightbox.querySelector('.st-lightbox-close');

	// Open Lightbox
	triggers.forEach(function(trigger) {
		trigger.addEventListener('click', function(e) {
			e.preventDefault();
			var imgUrl = this.getAttribute('href');
			if (imgUrl) {
				lightboxImg.setAttribute('src', imgUrl);
				lightbox.style.display = 'flex';
				// trigger reflow
				void lightbox.offsetWidth;
				lightbox.classList.add('st-show');
			}
		});
	});

	// Close Lightbox
	function closeLightbox() {
		lightbox.classList.remove('st-show');
		setTimeout(function() {
			lightbox.style.display = 'none';
			lightboxImg.setAttribute('src', '');
		}, 300); // match transition duration
	}

	closeBtn.addEventListener('click', closeLightbox);
	
	lightbox.addEventListener('click', function(e) {
		if (e.target === lightbox) {
			closeLightbox();
		}
	});

	document.addEventListener('keydown', function(e) {
		if (e.key === 'Escape' && lightbox.classList.contains('st-show')) {
			closeLightbox();
		}
	});
});
