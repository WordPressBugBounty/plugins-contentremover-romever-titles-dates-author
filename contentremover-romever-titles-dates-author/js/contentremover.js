document.addEventListener("DOMContentLoaded", function () {
    if (typeof contentRemoverSettings === 'undefined') {
        return;
    }

    function hideElementWithText(elements, keywords) {
        elements.forEach((element) => {
            const parentElement = element.closest('div, span, p');
            if (parentElement) {
                parentElement.style.display = 'none';
            } else {
                element.style.display = 'none';
            }

            const container = element.closest('.wp-block-group, .meta-info');
            if (container) {
                const siblingElements = container.querySelectorAll('p, span, div');
                siblingElements.forEach((sibling) => {
                    keywords.forEach((keyword) => {
                        if (sibling.textContent.trim().toLowerCase().includes(keyword)) {
                            sibling.style.display = 'none';
                        }
                    });
                });
            }
        });
    }

    if (contentRemoverSettings.hideAuthor) {
        const authorLinks = document.querySelectorAll('a[href*="/author/"]');
        hideElementWithText(authorLinks, ['written by']);
    }

    if (contentRemoverSettings.hideDate) {
        const dateElements = document.querySelectorAll('time, .entry-date, .post-date, .published');
        hideElementWithText(dateElements, ['published on', 'updated on']);
    }
});
