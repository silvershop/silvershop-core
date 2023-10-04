/**
 *     Addressbook checkout component
 *     This handles a dropdown or radio buttons containing existing addresses or payment methods,
 *     with one of the options being "create a new ____". When that last option is selected, the
 *     other fields need to be shown, otherwise they need to be hidden.
 */
function onExistingValueChange() {
    let existingValues = document.querySelectorAll('.hasExistingValues');
    if(!existingValues) return;

    existingValues.forEach(function (container, idx) {
        let toggle = document.querySelector('.existingValues select, .existingValues input:checked');

        // visible if the value is not an ID (numeric)
        let toggleState = Number.isNaN(parseInt(toggle.value));
        let toggleFields = container.querySelectorAll(".field:not(.existingValues)");

        // animate the fields - hide or show
        if (toggleFields && toggleFields.length > 0) {
            toggleFields.forEach(field => {
                field.style.display = toggleState ? '' : 'none';
            })
        }

        // clear them out
        toggleFields.forEach(field => {
            field.querySelectorAll('input, select, textarea').forEach(f => {
                f.value = '';
                f.disabled = toggleState ? '' : 'disabled';
            });
        });
    });
}

let selectors = document.querySelectorAll('.existingValues select');
if(selectors) selectors.forEach(selector => selector.addEventListener('change', onExistingValueChange));

let inputs = document.querySelectorAll('.existingValues input[type=radio]')
if(inputs) inputs.forEach(input => input.addEventListener('click', onExistingValueChange));

onExistingValueChange(); // handle initial state
