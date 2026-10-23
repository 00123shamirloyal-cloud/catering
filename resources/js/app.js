document.documentElement.classList.add('js');

document.addEventListener('DOMContentLoaded', () => {
    const menuButton = document.querySelector('[data-menu-toggle]');
    const mobileMenu = document.querySelector('[data-mobile-menu]');

    menuButton?.addEventListener('click', () => {
        const isExpanded = menuButton.getAttribute('aria-expanded') === 'true';
        menuButton.setAttribute('aria-expanded', String(!isExpanded));
        mobileMenu?.classList.toggle('hidden', isExpanded);
    });

    const sidebarButton = document.querySelector('[data-sidebar-toggle]');
    const sidebar = document.querySelector('[data-admin-sidebar]');

    sidebarButton?.addEventListener('click', () => {
        const isExpanded = sidebarButton.getAttribute('aria-expanded') === 'true';
        sidebarButton.setAttribute('aria-expanded', String(!isExpanded));
        sidebar?.classList.toggle('-translate-x-full', isExpanded);
    });

    const themeButton = document.querySelector('[data-theme-toggle]');
    const savedTheme = window.localStorage.getItem('catering-admin-theme');

    if (savedTheme === 'dark') {
        document.documentElement.classList.add('dark');
    }

    themeButton?.addEventListener('click', () => {
        const isDark = document.documentElement.classList.toggle('dark');
        window.localStorage.setItem('catering-admin-theme', isDark ? 'dark' : 'light');
    });

    const quotationItems = document.querySelector('[data-quotation-items]');
    const quotationTemplate = document.querySelector('[data-quotation-item-template]');

    if (quotationItems && quotationTemplate) {
        const refreshQuotationIndexes = () => {
            quotationItems.querySelectorAll('[data-quotation-item]').forEach((item, index) => {
                item.querySelectorAll('[data-field]').forEach((field) => {
                    field.name = `items[${index}][${field.dataset.field}]`;
                });
            });
        };

        document.querySelector('[data-add-quotation-item]')?.addEventListener('click', () => {
            quotationItems.append(quotationTemplate.content.cloneNode(true));
            refreshQuotationIndexes();
            quotationItems.lastElementChild?.querySelector('input')?.focus();
        });

        quotationItems.addEventListener('click', (event) => {
            const removeButton = event.target.closest('[data-remove-quotation-item]');

            if (!removeButton) {
                return;
            }

            if (quotationItems.querySelectorAll('[data-quotation-item]').length === 1) {
                return;
            }

            removeButton.closest('[data-quotation-item]')?.remove();
            refreshQuotationIndexes();
        });

        refreshQuotationIndexes();
    }

    document.querySelectorAll('[data-stepper]').forEach((form) => {
        const panels = [...form.querySelectorAll('[data-step-panel]')];
        const progress = form.querySelector('[data-step-progress]');
        let currentStep = 0;

        if (panels.length < 2) {
            return;
        }

        form.noValidate = true;

        const showStep = (step) => {
            currentStep = Math.min(Math.max(step, 0), panels.length - 1);
            panels.forEach((panel, index) => {
                panel.hidden = index !== currentStep;
            });

            if (progress) {
                progress.textContent = `Step ${currentStep + 1} of ${panels.length}`;
            }

            form.querySelectorAll('[data-step-prev]').forEach((button) => {
                button.classList.toggle('hidden', currentStep === 0);
            });
            form.querySelectorAll('[data-step-next]').forEach((button) => {
                button.classList.toggle('hidden', currentStep === panels.length - 1);
            });
            form.querySelectorAll('[data-step-submit]').forEach((button) => {
                button.classList.toggle('hidden', currentStep !== panels.length - 1);
            });
        };

        form.querySelectorAll('[data-step-next]').forEach((button) => {
            button.addEventListener('click', () => {
                const invalidField = [...panels[currentStep].querySelectorAll('input, select, textarea')]
                    .find((field) => !field.checkValidity());

                if (invalidField) {
                    invalidField.reportValidity();
                    return;
                }

                showStep(currentStep + 1);
                panels[currentStep].querySelector('input, select, textarea')?.focus();
            });
        });

        form.querySelectorAll('[data-step-prev]').forEach((button) => {
            button.addEventListener('click', () => showStep(currentStep - 1));
        });

        form.addEventListener('submit', (event) => {
            const invalidField = [...form.querySelectorAll('input, select, textarea')]
                .find((field) => !field.checkValidity());

            if (!invalidField) {
                return;
            }

            event.preventDefault();
            const invalidPanel = invalidField.closest('[data-step-panel]');
            showStep(panels.indexOf(invalidPanel));
            invalidField.reportValidity();
        });

        showStep(0);
    });
});
