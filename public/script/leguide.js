(function () {
    'use strict';

    const guide = document.querySelector('.bdo-guide');
    const genres = guide.querySelector('.bdo-guide-genres');
    const summary = genres.querySelector('summary');
    const search = genres.querySelector('.bdo-guide-genre-search');
    const choices = Array.from(genres.querySelectorAll('.bdo-guide-genre-choices label'));
    const checks = choices.map(choice => choice.querySelector('input'));
    const noMatch = genres.querySelector('.bdo-guide-no-genre');
    const limit = genres.querySelector('.bdo-guide-genre-limit');

    function searchKey(text) {
        return text.normalize('NFD').replace(/\p{M}/gu, '').toLocaleLowerCase('fr').trim();
    }

    const choiceKeys = choices.map(choice => searchKey(choice.textContent));

    function filterGenres() {
        const term = searchKey(search.value);
        choices.forEach((choice, index) => {
            choice.hidden = !choiceKeys[index].includes(term);
        });
        noMatch.hidden = choices.some(choice => !choice.hidden);
    }

    function updateLimit() {
        const full = checks.filter(check => check.checked).length >= 10;
        checks.forEach(check => {
            check.disabled = full && !check.checked;
        });
        limit.hidden = !full;
    }

    search.hidden = false;
    search.addEventListener('input', filterGenres);
    genres.addEventListener('change', updateLimit);
    genres.addEventListener('keydown', event => {
        if (event.key === 'Escape' && genres.open) {
            genres.open = false;
            summary.focus();
        }
    });
    document.addEventListener('click', event => {
        if (genres.open && !genres.contains(event.target)) {
            genres.open = false;
        }
    });
    updateLimit();

    guide.querySelectorAll('.bdo-avis-detail[data-avis-url]').forEach(detail => {
        const fullText = detail.querySelector('.bdo-avis-full');
        let loading = false;

        detail.addEventListener('toggle', () => {
            if (!detail.open || loading || detail.classList.contains('is-loaded')) {
                return;
            }
            loading = true;
            fullText.textContent = 'Chargement de l’avis…';
            $.getJSON(detail.dataset.avisUrl)
                .done(data => {
                    fullText.textContent = data.texte;
                    detail.classList.add('is-loaded');
                })
                .fail(request => {
                    fullText.textContent = request.status === 404
                        ? 'Cet avis n’est plus disponible.'
                        : 'Impossible de charger cet avis. Refermez-le puis réessayez.';
                })
                .always(() => {
                    loading = false;
                });
        });
    });

    guide.querySelectorAll('.bdo-guide-review-social').forEach(review => {
        const user = Number(review.dataset.avisAuteur);
        const album = Number(review.dataset.avisTome);
        getSocialInfo(user, album);
    });

    guide.addEventListener('click', event => {
        const button = event.target.closest('[data-action-avis]');
        if (!button) {
            return;
        }
        const review = button.closest('.bdo-guide-review-social');
        const user = Number(review.dataset.avisAuteur);
        const album = Number(review.dataset.avisTome);
        switch (button.dataset.actionAvis) {
            case 'aimer':
                likeComment(user, album);
                break;
            case 'repondre':
                $(review.querySelector('.bdo-guide-reply')).toggle();
                break;
            case 'envoyer':
                addCommentToReview(user, album);
                break;
            case 'privatiser':
                setCommentPrivate(user, album);
                break;
        }
    });
})();
