const main = {
    initForm: function (vue, options = {}) {
        let form = {
            vue: vue,
            url: null,
            method: 'post',
            resetCount: 0,
            success() {
            },
            error() {
            },
            twofaError() {
            },
            definition: {},
            data: {},
            errors: {},
            hiddenFields: {},
            getError: function (path) {
                path = path.split('.');
                let cursor = this.errors;
                for (let i in path) {
                    let isLastIteration = i - 0 === path.length - 1;
                    if (undefined === cursor[path[i]]) {
                        return null;
                    }
                    if (isLastIteration) {
                        return cursor[path[i]];
                    }
                    cursor = cursor[path[i]];
                }
            },
            loading: false,
            isFormHandleValidationErrors: true,
            snackSuccessMessage: null,
        };
        form.reset = function () {
            console.warn('Form reset method is deprecated');// eslint-disable-line no-console
            this.data = {};
            this.errors = {};
            this.resetCount++;
        }.bind(form);
        for (let i in options) {
            form[i] = options[i];
        }
        form.submit = (options = {}) => {
            form.loading = true;
            form.errors = {};
            for (let i in options) {
                if (i === 'data' || i === 'hiddenFields') {
                    for (let ii in options[i]) {
                        form[i][ii] = options[i][ii];
                    }
                } else {
                    form[i] = options[i];
                }
            }
            for (let i in form.hiddenFields) {
                form.data[i] = form.hiddenFields[i];
            }
            const success = function (form, response) {
                form.loading = false;
                if (null !== form.snackSuccessMessage) {
                    form.vue.$snack.success({text: form.snackSuccessMessage});
                }
                form.success(response);
            };
            if (form.method === 'get') {
                form.vue.$http.get(form.url, {params: form.data}).then((response) => {
                    success(form, response);
                }).catch((response) => {
                    let error = response.body.errors !== undefined
                        ? Object.keys(response.body.errors)[0]
                        : 'Ошибка ' + response.status;
                    form.vue.$snack.danger({text: error});
                    form.loading = false;
                    form.error(response);
                });
            } else {
                const error = function (form, response) {
                    let errors = {};
                    if (response.status === 412) {
                        form.twofaError(response);
                        errors = response.body.errors;
                        form.errors = errors;
                    } else if (response.status === 400 && form.isFormHandleValidationErrors) {
                        errors = response.body.errors;
                        form.errors = errors;
                    } else {
                        form.vue.$snack.danger({text: 'Ошибка ' + response.status});
                    }
                    form.loading = false;
                    form.error(response);
                };
                let body = {form: {...form.data}, _token: config.token};
                // for (let item in form.data) {
                //     let name = 'form[' + item + ']';
                //     body[name] = form.data[item];
                // }
                if (form.method === 'delete') {
                    form.vue.$http.delete(form.url, {body: body, emulateJSON: true}).then((response) => {
                        success(form, response);
                    }).catch((response) => {
                        error(form, response);
                    });
                } else {
                    form.vue.$http[form.method](form.url, body, {emulateJSON: true}).then((response) => {
                        success(form, response);
                    }).catch((response) => {
                        error(form, response);
                    });
                }
            }
        };

        return form;
    },
    initListingForm: function (vue, options = {}) {
        let page = 1;
        let itemsPerPage = undefined === options.itemsPerPage ? 15 : options.itemsPerPage;
        let form = {
            vue: vue,
            url: null,
            success() {
            },
            error() {
            },
            filters: {},
            sort: [],
            loading: false,
            hasElements: 0,
            pagination: {total: 0, page: page, itemsPerPage: itemsPerPage, pageCount: 0, componentKey: 0},
            reset() {
                this.pagination.page = page;
                this.pagination.show = 0;
                this.pagination.itemsPerPage = itemsPerPage;
                this.pagination.componentKey += 1;
            },
        };
        for (let i in options) {
            form[i] = options[i];
        }
        form.submit = (options = {}) => {
            form.loading = true;
            let params = {};
            for (let i in options) {
                form[i] = options[i];
            }
            for (let i in form.filters) {
                params['f_' + i] = form.filters[i];
            }
            params['page'] = form.pagination.page;
            params['itemsPerPage'] = form.pagination.itemsPerPage;
            for (let i in form.sort) {
                params['s_' + i] = form.sort[i];
            }
            form.vue.$http.get(form.url, {params}).then((response) => {
                form.loading = false;
                form.pagination.total = response.body.total;
                form.hasElements = form.pagination.total > 0 ? 1 : 0;
                form.pagination.pageCount = Math.ceil(form.pagination.total / form.pagination.itemsPerPage);
                form.success(response);
            }).catch((response) => {
                form.loading = false;
                form.vue.$snack.danger({text: 'Error ' + response.status});
                form.error();
            });
        };
        return form;
    },
    formatMoney: function (value) {
        return value.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    },
};

const plugin = {
    install (Vue) {
        Vue.prototype.$ewll = main;
    }
}

export default plugin
