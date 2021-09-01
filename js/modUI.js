const modUI = {
    baseModal: $('#external-modules-disabled-modal').get(0),

    observeDomChange() {
        const config = {attributes: true, childList: true, subtree: true};
        const callback = function (mutationsList, observer) { //Function executed upon dom mutation
            for (const mutation of mutationsList) {
                if (mutation.type === 'childList') { // only check for appended nodes
                    if (mutation.addedNodes.length > 0) {
                        let list = Array.from(mutation.addedNodes);
                        let EMTableRendered = list.find((el) => {
                            return el.id === 'external-modules-disabled-table';
                        });

                        if (EMTableRendered) { // External modules table is rendered, alter functionality
                            modUI.alterHTML();
                            modUI.appendFinanceText();
                            // observer.disconnect();
                        }
                    }
                }
            }
        };

        // Create an observer instance linked to the callback function
        const observer = new MutationObserver(callback);

        // Start observing the target node for configured mutations
        observer.observe(this.baseModal, config);

    },

    alterHTML() {
        let span = "<span class=\"fas fa-plus-circle\" aria-hidden=\"true\"> Request </span>";
        let button = $("<button>", {"class": "btn btn-success btn-xs request-button"});
        let element = $(button).append(span); //replace button functionality with new
        let redirect = $('#redirect-uri').attr('redirect-uri');

        button.on('click', function (event) {
            if (redirect) {
                window.location.href = redirect;
            } else {
                if ($('.modal-body').find('.alert').length === 0) {
                    $('.modal-body').prepend("<div id='alert-focus' class='alert alert-danger' role='alert'>Error: No redirect URI present, please ensure redcap-survey-redirect is not empty</div>")
                }
                $(this).removeClass('btn-success');
                $(this).addClass('btn-danger');
            }
            event.preventDefault();
        })
        $(this.baseModal).find('.external-modules-action-buttons').html(element);
    },

    appendFinanceText() {
        let tr = $("#external-modules-disabled-table").find("tr");
        for (let item of tr) {
            let em = $(item).attr('data-module');
            if (em && em in data1) { // EM is discoverable on the modal page and it has pricing information
                let custom = undefined;
                let cost = `<div><p class="override">Monthly cost: $${data1[em]['actual_monthly_cost']}</p></div>`;

                if (data1[em]['custom_text_override']) //If the custom text is set, create a new dom element
                    custom = `<div><p class="override">${data1[em]['custom_text_override']}</p></div>`;

                $(item).find('.external-modules-description').after(cost).after(custom);
                $('.override').css('color', 'grey');
            }
        }
    }
}

export {modUI}
