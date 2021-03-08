const snackShow = function (vue, method, message) {
    if (undefined === vue.$snack) {
        console.warn('snack is not realised');// eslint-disable-line no-console
    } else {
        vue.$snack[method]({text: message});
    }
};

let Main = {
    loadingCounter: 0,
};

Main.initForm = function (vue, options = {}) {
    let form = {
        vue: vue,
        url: null,
        method: 'post',
        headers: {},
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
                    //For collection's error
                    if (typeof cursor[path[i]] === 'string') {
                        return cursor[path[i]];
                    } else {
                        return null;
                    }
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
    }.bind(form);
    for (let i in options) {
        form[i] = options[i];
    }
    form.submit = (options = {}) => {
        form.loading = true;
        Main.loadingCounter++;
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
            Main.loadingCounter--;
            if (null !== form.snackSuccessMessage) {
                snackShow(form.vue, 'success', form.snackSuccessMessage);
            }
            form.success(response);
        };
        if (form.method === 'get') {
            form.vue.$http.get(form.url, {params: form.data, headers: form.headers}).then((response) => {
                success(form, response);
            }).catch((response) => {
                if (undefined !== response.body) {
                    let error = response.body.errors !== undefined
                        ? Object.keys(response.body.errors)[0]
                        : 'Ошибка ' + response.status;
                    snackShow(form.vue, 'danger', error);
                    form.loading = false;
                    Main.loadingCounter--;
                    form.error(response);
                } else {
                    throw response;
                }

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
                    let error = 'Error ' + response.status;
                    if (response.status === 400) {
                        for (let i in response.body.errors) {
                            error = response.body.errors[i];
                            break;
                        }
                    } //else {
                        // throw response;
                    // }

                    snackShow(form.vue, 'danger', error);
                }
                form.loading = false;
                Main.loadingCounter--;
                form.error(response);
            };
            let body = {form: {...form.data}, _token: config.token};
            // for (let item in form.data) {
            //     let name = 'form[' + item + ']';
            //     body[name] = form.data[item];
            // }
            if (form.method === 'delete') {
                form.vue.$http.delete(form.url, {body, headers: form.headers, emulateJSON: true}).then((response) => {
                    success(form, response);
                }).catch((response) => {
                    error(form, response);
                });
            } else {
                form.vue.$http[form.method](form.url, body, {
                    emulateJSON: true,
                    headers: form.headers
                }).then((response) => {
                    success(form, response);
                }).catch((response) => {
                    error(form, response);
                });
            }
        }
    };

    return form;
};
Main.initListingForm = function (vue, options = {}) {
    let page = 1;
    let itemsPerPage = 15;
    let form = {
        vue: vue,
        url: null,
        success() {
        },
        error() {
        },
        headers: {},
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
        Main.loadingCounter++;
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
        form.vue.$http.get(form.url, {params, headers: form.headers}).then((response) => {
            form.loading = false;
            Main.loadingCounter--;
            form.pagination.total = response.body.total;
            form.hasElements = form.pagination.total > 0 ? 1 : 0;
            form.pagination.pageCount = Math.ceil(form.pagination.total / form.pagination.itemsPerPage);
            form.success(response);
        }).catch((response) => {
            // throw response;
            form.loading = false;
            Main.loadingCounter--;
            snackShow(form.vue, 'danger', 'Error ' + response.status);
            form.error();
        });
    };
    return form;
};

module.exports = Main;


