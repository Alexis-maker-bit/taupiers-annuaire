document.addEventListener('DOMContentLoaded', function() {

    // --- GESTION DES SLIDERS UNIFIÉE AVEC SWIPER.JS ---

    // 1. Slider pour les "Produits Recommandés"
    // Ce bloc est déjà correct pour la nouvelle structure HTML générée par PHP.
    const productSliderElement = document.querySelector('.taupe-product-section .product-slider');
    if (productSliderElement) {
        const swiperProducts = new Swiper(productSliderElement, {
            slidesPerView: 2,
            spaceBetween: 15,
            navigation: {
                nextEl: '.taupe-product-section .product-slider-next', // Doit correspondre aux classes dans le HTML Swiper
                prevEl: '.taupe-product-section .product-slider-prev', // Doit correspondre aux classes dans le HTML Swiper
            },
            breakpoints: {
                640: { slidesPerView: 3, spaceBetween: 20 },
                768: { slidesPerView: 4, spaceBetween: 25 },
                1024: { slidesPerView: 5, spaceBetween: 30 }
            }
        });
    }

    // 2. Slider pour les "Taupiers Similaires"
    const relatedTaupierSliderElement = document.querySelector('.related-taupiers-slider .related-taupier-swiper');
    if (relatedTaupierSliderElement) {
        const swiperRelated = new Swiper(relatedTaupierSliderElement, {
            slidesPerView: 1,
            spaceBetween: 15,
            navigation: {
                nextEl: '.related-taupiers-slider .related-next-btn',
                prevEl: '.related-taupiers-slider .related-prev-btn',
            },
            pagination: {
                el: '.related-taupiers-slider .related-taupier-pagination',
                clickable: true,
            },
            breakpoints: {
                600: { slidesPerView: 2, spaceBetween: 20 },
                992: { slidesPerView: 3, spaceBetween: 30 }
            }
        });
    }

    // 3. Slider pour la "Galerie Photos" sur la page du taupier
    const galleryContainer = document.querySelector('.taupier-gallery-slider .gallery-slider-container');
    if (galleryContainer) {
        // Assurez-vous que les classes Swiper sont bien présentes sur le HTML (normalement ajoutées via PHP ou JS lors de la création)
        // Si le conteneur principal n'a pas la classe 'swiper', ajoutez-la.
        if (!galleryContainer.classList.contains('swiper')) {
            galleryContainer.classList.add('swiper');
        }
        const galleryWrapper = galleryContainer.querySelector('.gallery-slider-wrapper');
        if (galleryWrapper && !galleryWrapper.classList.contains('swiper-wrapper')) {
            galleryWrapper.classList.add('swiper-wrapper');
        }
        const gallerySlidesElements = galleryContainer.querySelectorAll('.gallery-slide');
        gallerySlidesElements.forEach(slide => {
            if (!slide.classList.contains('swiper-slide')) {
                slide.classList.add('swiper-slide');
            }
        });

        // Vérifiez que les éléments de navigation et de pagination existent avant d'initialiser
        const galleryNextBtn = galleryContainer.querySelector('.gallery-next-btn');
        const galleryPrevBtn = galleryContainer.querySelector('.gallery-prev-btn');
        const galleryDotsContainer = galleryContainer.querySelector('.gallery-dots');

        if (galleryNextBtn && galleryPrevBtn && galleryDotsContainer) {
            const swiperGallery = new Swiper(galleryContainer, {
                loop: gallerySlidesElements.length > 1, // Activer la boucle seulement si plus d'une slide
                navigation: {
                    nextEl: galleryNextBtn,
                    prevEl: galleryPrevBtn,
                },
                pagination: {
                    el: galleryDotsContainer,
                    clickable: true,
                    renderBullet: function (index, className) {
                        // S'assurer que la classe 'gallery-dot' est bien celle que Swiper utilise ou adapter ici.
                        // Par défaut Swiper utilise 'swiper-pagination-bullet'.
                        // Si vos points ont déjà la classe 'gallery-dot', Swiper les utilisera.
                        return '<button class="' + className + ' gallery-dot" data-index="' + index + '" aria-label="Image ' + (index + 1) + '"></button>';
                    }
                }
            });
        } else {
            console.warn("Swiper Gallery: Éléments de navigation ou de pagination manquants. Le slider ne sera pas pleinement fonctionnel.");
        }
    }

    // --- AUTRES SCRIPTS EXISTANTS ---

    // Gestion de la FAQ (Accordion)
    const faqQuestions = document.querySelectorAll('.faq-question');
    const faqNoResults = document.getElementById('faq-no-results');

    faqQuestions.forEach(function(question) {
        const reponse = question.nextElementSibling;
        if (reponse) { // Vérifier si la réponse existe
            reponse.style.maxHeight = '0';
            reponse.classList.remove('open');
        }
    });

    faqQuestions.forEach(function(question) {
        question.addEventListener('click', function() {
            const reponse = this.nextElementSibling;
            if (!reponse) return; // Si pas de réponse, ne rien faire

            const wasActive = this.classList.contains('active');

            faqQuestions.forEach(function(q) {
                if (q !== question) {
                    q.classList.remove('active');
                    const resp = q.nextElementSibling;
                    if (resp) {
                        resp.classList.remove('open');
                        resp.style.maxHeight = '0';
                    }
                }
            });

            if (!wasActive) {
                this.classList.add('active');
                reponse.classList.add('open');
                reponse.style.maxHeight = reponse.scrollHeight + 'px';
            } else {
                this.classList.remove('active');
                reponse.classList.remove('open');
                reponse.style.maxHeight = '0';
            }
        });
    });

    // Recherche dans la FAQ
    const searchInput = document.getElementById('faq-search');
    const faqItems = document.querySelectorAll('.faq-item');

    if (searchInput && faqItems.length > 0) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            let hasResults = false;

            faqItems.forEach(item => {
                const questionElement = item.querySelector('.faq-question');
                const answerElement = item.querySelector('.faq-reponse div'); // Cible le div à l'intérieur
                const questionText = questionElement ? questionElement.textContent.toLowerCase() : '';
                const answerText = answerElement ? answerElement.textContent.toLowerCase() : '';

                if (questionText.includes(searchTerm) || answerText.includes(searchTerm)) {
                    item.style.display = 'block';
                    hasResults = true;
                } else {
                    item.style.display = 'none';
                }

                if (questionElement && questionElement.classList.contains('active') && (!questionText.includes(searchTerm) && !answerText.includes(searchTerm))) {
                    questionElement.classList.remove('active');
                    const reponse = questionElement.nextElementSibling;
                    if (reponse) {
                        reponse.classList.remove('open');
                        reponse.style.maxHeight = '0';
                    }
                }
            });

            if (faqNoResults) {
                faqNoResults.style.display = hasResults ? 'none' : 'block';
            }
        });
    }

    // Compteur de caractères pour le textarea d'avis
    const reviewTextarea = document.getElementById('review_text');
    const charCounter = document.querySelector('.textarea-counter');
    const MAX_CHARS = 500;

    if (reviewTextarea && charCounter) {
        reviewTextarea.addEventListener('input', function() {
            const count = this.value.length;
            charCounter.textContent = `${count}/${MAX_CHARS} caractères`;
            charCounter.style.color = (count > MAX_CHARS) ? '#e74c3c' : (count > MAX_CHARS * 0.9) ? '#e67e22' : '';
        });
        reviewTextarea.dispatchEvent(new Event('input')); // Initialiser
    }

    // Filtrage des avis
    const filterButtons = document.querySelectorAll('.reviews-filters .filter-btn');
    const reviewsListContainer = document.querySelector('.reviews-list');
    
    if (filterButtons.length > 0 && reviewsListContainer) {
        const reviewItems = Array.from(reviewsListContainer.querySelectorAll('.review-item'));

        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                filterButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                const filter = this.dataset.filter;

                let filteredItems = reviewItems.filter(item => {
                    const rating = parseInt(item.dataset.rating);
                    return filter === 'all' || (filter === 'positive' && rating >= 4);
                });

                if (filter === 'recent') {
                    filteredItems.sort((a, b) => parseInt(b.dataset.date) - parseInt(a.dataset.date));
                } else if (filter === 'positive' || filter === 'all') {
                    // Trier par note (décroissant) puis par date (décroissant)
                    filteredItems.sort((a, b) => {
                        const ratingDiff = parseInt(b.dataset.rating) - parseInt(a.dataset.rating);
                        return ratingDiff !== 0 ? ratingDiff : parseInt(b.dataset.date) - parseInt(a.dataset.date);
                    });
                }

                reviewsListContainer.innerHTML = ''; // Vider
                if (filteredItems.length > 0) {
                    filteredItems.forEach(item => reviewsListContainer.appendChild(item));
                } else {
                    reviewsListContainer.innerHTML = '<p style="text-align: center; color: #666; padding: 1rem;">Aucun avis ne correspond à ce filtre.</p>';
                }
            });
        });
    }


    // Gestion du formulaire d'avis (AJAX)
    const reviewForm = document.getElementById('submit-taupier-review');
    const feedbackSuccess = document.querySelector('.review-submission-feedback');

    if (reviewForm && typeof taupier_ajax !== 'undefined') {
        const formMessage = reviewForm.querySelector('.form-message'); 

        reviewForm.addEventListener('submit', function(e) {
            e.preventDefault();
            let isValid = true;
            const formData = new FormData(this);

            // Validation des champs requis
            this.querySelectorAll('[required]').forEach(field => {
                if (field.type === 'radio') { // Pour les étoiles de notation
                    const ratingGroup = this.querySelector('input[name="rating"]:checked');
                    if (!ratingGroup) {
                        const ratingFieldset = this.querySelector('.rating-field');
                        if (ratingFieldset) ratingFieldset.style.border = '1px solid #e74c3c'; // Ou autre indicateur visuel
                        isValid = false;
                    } else {
                        const ratingFieldset = this.querySelector('.rating-field');
                        if (ratingFieldset) ratingFieldset.style.border = '';
                    }
                } else if (!field.value.trim()) {
                    field.style.borderColor = '#e74c3c';
                    isValid = false;
                } else {
                    field.style.borderColor = '';
                }
            });
            
            if (reviewTextarea && reviewTextarea.value.length > MAX_CHARS) {
                reviewTextarea.style.borderColor = '#e74c3c';
                if (formMessage) formMessage.innerHTML = '<div style="color: #e74c3c; margin-top: 1rem;">Votre avis dépasse la limite de caractères.</div>';
                isValid = false;
            }
            
            if (!isValid) {
                if (formMessage) formMessage.innerHTML = '<div style="color: #e74c3c; margin-top: 1rem;">Veuillez remplir tous les champs obligatoires correctement.</div>';
                return;
            }

            formData.append('action', 'submit_taupier_review');
            formData.append('nonce', taupier_ajax.nonce);

            const submitButton = this.querySelector('.submit-review-button');
            submitButton.disabled = true;
            submitButton.textContent = 'Envoi en cours...';
            if (formMessage) formMessage.innerHTML = '';

            fetch(taupier_ajax.ajax_url, {
                method: 'POST',
                body: formData,
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    reviewForm.style.display = 'none';
                    if (feedbackSuccess) feedbackSuccess.style.display = 'block';
                    reviewForm.reset();
                     if (charCounter) charCounter.textContent = `0/${MAX_CHARS} caractères`; // Réinitialiser le compteur
                } else {
                    let errorMessage = data.data && data.data.message ? data.data.message : 'Une erreur est survenue.';
                    if (data.data && data.data.errors && Array.isArray(data.data.errors)) {
                        errorMessage += '<ul>';
                        data.data.errors.forEach(error => { errorMessage += `<li>- ${error}</li>`; });
                        errorMessage += '</ul>';
                    }
                    if (formMessage) formMessage.innerHTML = `<div style="color: #e74c3c; margin-top: 1rem;">${errorMessage}</div>`;
                }
            })
            .catch(error => {
                console.error('Erreur AJAX:', error);
                if (formMessage) formMessage.innerHTML = '<div style="color: #e74c3c; margin-top: 1rem;">Une erreur de connexion est survenue. Veuillez réessayer.</div>';
            })
            .finally(() => {
                submitButton.disabled = false;
                submitButton.textContent = 'Envoyer mon avis';
            });
        });
    }

});
