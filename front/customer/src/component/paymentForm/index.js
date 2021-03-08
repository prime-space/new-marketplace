const paymentForm = {
    initForm(formData) {
        let form = document.createElement("form");
        form.method = formData.method;
        form.action = formData.url;
        for (let fieldName in formData.fields) {
            let el = document.createElement('input');
            el.name = fieldName;
            el.value = formData.fields[fieldName];
            form.appendChild(el);
        }
        form.style.display = 'none';
        document.body.appendChild(form);

        return form;
    },
};

export default paymentForm;
