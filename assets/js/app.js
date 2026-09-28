document.querySelectorAll('form[novalidate]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (!form.reportValidity()) {
            event.preventDefault();
        }
    });
});
