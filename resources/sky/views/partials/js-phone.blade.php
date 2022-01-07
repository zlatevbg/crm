var phone_code = document.querySelector('#input-phone_code');
var phone_number = document.querySelector('#input-phone_number');

$(phone_code).multiselect({
    multiple: false,
    close: function() {
        if (parseInt(this.value) > 0) {
            phone_number.disabled = false;
        } else {
            phone_number.disabled = true;
            phone_number.value = '';
        }
    },
});
