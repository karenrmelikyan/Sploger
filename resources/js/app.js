import $ from 'jquery'
window.$ = window.jQuery = $;
// import validate from 'jquery-validation'
import 'bootstrap/js/dist/collapse';
import 'bootstrap/js/dist/tooltip';
import 'bootstrap/js/dist/alert';
import Popover from 'bootstrap/js/dist/popover';
import Modal from 'bootstrap/js/dist/modal';

// common js code
(function () {
    'use strict';

    // XHR Magic
    Object.entries([].slice.call(document.querySelectorAll('.xhr-magic'))
        .reduce(function(rv, /** HTMLElement */ value) {
            (rv[value.dataset['xhr']] = rv[value.dataset['xhr']] || []).push(value);
            return rv;
        }, {})
    ).forEach(([url, /** HTMLElement[] */ elements]) => {
        fetch(url)
            .then(response => response.json())
            .then(data => {
                elements.forEach(element => {
                    if (element.dataset['xhrValue'] !== undefined) {
                        let value = element.dataset['xhrValue'] === '' ? data['value'] : data['value'][element.dataset['xhrValue']];
                        if (typeof value === 'number') {
                            value = (new Intl.NumberFormat('en-US')).format(value);
                        }

                        element.innerHTML = value;
                    }

                    if (element.dataset['xhrClass'] !== undefined) {
                        const cssClasses = element.dataset['xhrClass'] === '' ? data['class'] : data['class'][element.dataset['xhrClass']];
                        for (const className in cssClasses) {
                            if (cssClasses[className] === true && !element.classList.contains(className)) {
                                element.classList.add(className);
                            } else if (cssClasses[className] === false && element.classList.contains(className)) {
                                element.classList.remove(className);
                            }
                        }
                    }
                });
            });
    });
})();

// GRID JAVASCRIPT
(function () {
    'use strict'

    // Initialize all popovers on page
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.forEach(function (popoverTriggerEl) {
        new Popover(popoverTriggerEl);
    });

    // Grid controls
    const controlsTriggerList = [].slice.call(document.querySelectorAll('.grid-controls select'));
    controlsTriggerList.forEach(function (controlsTriggerEl) {
        controlsTriggerEl.addEventListener('change', function () {
            controlsTriggerEl.form.submit();
        });
    });

    // Grid filters
    /** @type {HTMLFormElement} */
    const filtersForm = document.querySelector('.grid-filters-form');
    if (filtersForm !== null) {
        console.log(`Initializing grid filters...`);
        document.querySelector('.grid-filters .btn-filters-apply').addEventListener('click', function () {
            submitFiltersForm();
        });
        document.querySelector('.grid-filters .btn-filters-reset').addEventListener('click', function () {
            filtersForm.submit();
        });
        const filterFormTriggerList = [].slice.call(document.querySelectorAll('.grid-filters input'));
        filterFormTriggerList.forEach(function (filterFormTriggerEl) {
            filterFormTriggerEl.addEventListener('keypress', function(event) {
                if (event.key === 'Enter') {
                    submitFiltersForm();
                }
            });
        });

        function submitFiltersForm() {
            const inputs = document.querySelectorAll('.grid-filters input, .grid-filters select');
            inputs.forEach(function (input) {
                let duplicate = null;
                if (input instanceof HTMLInputElement) {
                    const value = input.value;
                    if (value !== '') {
                        duplicate = input.cloneNode(true);
                        duplicate.setAttribute('type', 'hidden');
                        filtersForm.appendChild(duplicate);
                    }
                } else if (input instanceof HTMLSelectElement) {
                    const value = input.options[input.selectedIndex].value;
                    if (value !== '') {
                        /** @type {HTMLInputElement} */
                        duplicate = document.createElement('input');
                        duplicate.name = input.name;
                        duplicate.value = value;
                        duplicate.setAttribute('type', 'hidden');
                        filtersForm.appendChild(duplicate);
                    }
                }

                filtersForm.submit();
            });
        }
    }

    // Confirm modals
    const modalEl = document.getElementById('confirmModal');
    if (modalEl !== null) {
        /** @type {Modal} */
        const modal = new Modal(modalEl);
        const modalTriggerListener = function (event) {
            event.preventDefault();
            /** @type {HTMLAnchorElement|HTMLFormElement} */
            const target = event.currentTarget;
            modalEl.querySelector('.modal-body').textContent = target.dataset['confirm'];
            modalEl.querySelector('.modal-confirm').addEventListener('click', function () {
                console.log('click');
                if (target instanceof HTMLAnchorElement) {
                    target.removeEventListener('click', modalTriggerListener);
                    target.click();
                } else if (target instanceof HTMLFormElement) {
                    target.removeEventListener('submit', modalTriggerListener);
                    target.submit();
                }
            });
            modal.show();
        };
        const confirmTriggerList = [].slice.call(document.querySelectorAll('.data-grid .needs-confirmation'));
        confirmTriggerList.forEach(function (confirmTriggerEl) {
            if (confirmTriggerEl instanceof HTMLAnchorElement) {
                confirmTriggerEl.addEventListener('click', modalTriggerListener);
            } else if (confirmTriggerEl instanceof HTMLFormElement) {
                confirmTriggerEl.addEventListener('submit', modalTriggerListener);
            }
        });
    }
})();

(function () {
    'use strict'

    const projectSplogsTable = document.querySelector('.project-splogs')
    if (projectSplogsTable !== null) {
        const tableBody = projectSplogsTable.querySelector('tbody');

        projectSplogsTable.addEventListener('click', function(e) {
            for (let target = e.target; target && target !== this; target = target.parentNode) {
                if (target.matches('.add-splog')) {
                    onAddSplogClick();
                }
                if (target.matches('.remove-splog')) {
                    onRemoveSplogClick(target);
                }
            }
        });

        function onAddSplogClick() {
            const id = tableBody.childElementCount + 1;
            /** @type {HTMLTemplateElement} template */
            const template = document.querySelector('#splog-template');
            // noinspection JSValidateTypes
            /** @type {DocumentFragment} cloned */
            const cloned = template.content.cloneNode(true);

            // change clone here
            for (const element of cloned.children) {
                /** @type {HTMLElement} element */
                element.innerHTML = element.innerHTML.replace(/:id/g, id);
            }

            tableBody.appendChild(cloned);
        }

        function onRemoveSplogClick(target) {
            if (target.dataset.splogId) {
                /** @type Modal */
                const modal = new Modal(document.getElementById('modalDeleteRecord'));
                /** @type {HTMLFormElement} */
                const form = modal._element.querySelector('form');
                form.action = form.action.replace(/:projectId/, target.dataset.projectId).replace(/:splogId/, target.dataset.splogId);
                modal.show();
            } else {
                target.closest('tr').remove();
                recalculateIds();
            }
        }

        function recalculateIds() {
            let startingIndex = 1;
            for (/** @type {HTMLElement} */ const rowHeader of tableBody.querySelectorAll('th')) {
                rowHeader.innerText = `${startingIndex++}`;
            }
        }
    }
})();
