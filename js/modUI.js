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
            redirect = null;
            if (redirect)
                window.location.href = redirect;
            else
                $('.modal-body').prepend("<div class='alert alert-danger' role='alert'>Error: No redirect URI present, please ensure redcap-survey-redirect is not empty</div>")

            event.preventDefault();
        })
        $(this.baseModal).find('.external-modules-action-buttons').html(element);
    }
}

export {modUI}
